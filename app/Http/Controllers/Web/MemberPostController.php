<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Post;
use App\Support\UploadDiagnostics;
use App\Models\Tag;
use App\Support\SiteTheme;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Slimani\MediaManager\Models\File as MediaFile;

class MemberPostController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:draft,pending,published'],
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:latest,oldest,title'],
        ]);

        $posts = Post::query()
            ->with(['category', 'contentModel', 'detail'])
            ->where('user_id', $request->user()->id)
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->when(
                filled($filters['q'] ?? null),
                fn ($query) => $query->where(function ($subQuery) use ($filters): void {
                    $subQuery
                        ->where('title', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%');
                }),
            )
            ->when(
                ($filters['sort'] ?? 'latest') === 'oldest',
                fn ($query) => $query->oldest(),
                fn ($query) => $query->latest(),
            )
            ->when(
                ($filters['sort'] ?? null) === 'title',
                fn ($query) => $query->reorder('title'),
            )
            ->paginate(10)
            ->withQueryString();

        return view(SiteTheme::view('member.posts-index', 'themes.default.member.posts-index'), [
            'posts' => $posts,
            'user' => $request->user(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        return view(SiteTheme::view('member.posts-create', 'themes.default.member.posts-create'), [
            'categories' => Category::query()->with('contentModel')->orderBy('sort_order')->orderBy('id')->get(),
            'coverAttachments' => $this->loadMemberCoverAttachments($request->user()->id),
            'memberAttachments' => $this->loadMemberAttachments($request->user()->id),
            'user' => $request->user(),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Request $request, Post $post): View
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        return view(SiteTheme::view('member.posts-edit', 'themes.default.member.posts-edit'), [
            'post' => $post->load('detail', 'tags', 'contentModel', 'category.contentModel'),
            'categories' => Category::query()->with('contentModel')->orderBy('sort_order')->orderBy('id')->get(),
            'coverAttachments' => $this->loadMemberCoverAttachments($request->user()->id),
            'memberAttachments' => $this->loadMemberAttachments($request->user()->id),
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($failedUploadResponse = $this->interceptFailedCoverUpload($request)) {
            return $failedUploadResponse;
        }

        $validated = $request->validate($this->postRules($request), $this->postMessages());
        $attachmentIds = $this->resolveOwnedAttachmentIds($validated['attachment_ids'] ?? [], $request->user()->id);
        $coverSelection = $this->resolveCoverSelection(
            $request,
            $request->user()->id,
        );

        $modelId = Post::ensureCategoryMatchesModel($validated['category_id'], $validated['model_id'] ?? null);

        $data = Post::normalizePublishingDataForRecord([
            'title' => $validated['title'],
            'slug' => '',
            'category_id' => $validated['category_id'],
            'model_id' => $modelId,
            'user_id' => $request->user()->id,
            'status' => $validated['status'],
            'published_at' => null,
            'content' => $validated['content'],
            'seo_title' => $validated['seo_title'] ?? '',
            'summary' => $validated['summary'] ?? '',
            'cover_image_url' => $coverSelection['cover_image_url'] ?? '',
        ]);

        $post = Post::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'],
            'model_id' => $data['model_id'],
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'published_at' => $data['published_at'],
        ]);

        if ($post->isFlashModel()) {
            $post->update([
                'slug' => $post::generateFlashSlugForCategory($post->category_id, $post->id),
            ]);
        }

        $post->detail()->create([
            'content' => $data['content'],
            'custom_fields' => [
                'seo_title' => $data['seo_title'],
                'summary' => $data['summary'],
                'cover_image_url' => $data['cover_image_url'],
                'cover_attachment_id' => $coverSelection['cover_attachment_id'],
                'attachment_ids' => $attachmentIds,
            ],
        ]);

        $this->syncPostCoverMedia($post, $coverSelection['cover_media_file_id'] ?? null);

        $tagIds = collect(explode(',', (string) ($validated['tags'] ?? '')))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->take(8)
            ->map(function (string $tag): int {
                $slug = Str::slug($tag);
                $slug = filled($slug) ? $slug : 'tag-'.substr(md5($tag), 0, 12);

                return Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $tag, 'count' => 0],
                )->id;
            })
            ->all();

        if ($tagIds !== []) {
            $post->tags()->sync($tagIds);

            Tag::query()
                ->whereIn('id', $tagIds)
                ->get()
                ->each(fn (Tag $tag) => $tag->update(['count' => $tag->posts()->count()]));
        }

        $post->statistics()->firstOrCreate(
            ['post_id' => $post->id],
            ['views' => 0, 'likes' => 0, 'comments_count' => 0],
        );

        return redirect()
            ->route('member.dashboard')
            ->with('status', $validated['status'] === 'pending'
                ? '稿件已提交审核。'
                : '草稿已保存到会员中心。');
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        if ($failedUploadResponse = $this->interceptFailedCoverUpload($request)) {
            return $failedUploadResponse;
        }

        $validated = $request->validate($this->postRules($request), $this->postMessages());
        $attachmentIds = $this->resolveOwnedAttachmentIds($validated['attachment_ids'] ?? [], $request->user()->id);
        $coverSelection = $this->resolveCoverSelection(
            $request,
            $request->user()->id,
            $post->custom_cover_image_url,
            $post->cover_attachment_id,
        );

        $modelId = Post::ensureCategoryMatchesModel(
            $validated['category_id'],
            $validated['model_id'] ?? $post->model_id,
        );

        $data = Post::normalizePublishingDataForRecord([
            'title' => $validated['title'],
            'slug' => $post->slug,
            'category_id' => $validated['category_id'],
            'model_id' => $modelId,
            'user_id' => $request->user()->id,
            'status' => $validated['status'],
            'published_at' => null,
            'content' => $validated['content'],
            'seo_title' => $validated['seo_title'] ?? '',
            'summary' => $validated['summary'] ?? '',
            'cover_image_url' => $coverSelection['cover_image_url'] ?? '',
        ], $post->id);

        $post->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'],
            'model_id' => $data['model_id'],
            'status' => $data['status'],
            'published_at' => null,
        ]);

        if ($post->isFlashModel()) {
            $post->update([
                'slug' => $post::generateFlashSlugForCategory($post->category_id, $post->id),
            ]);
        }

        $post->detail()->updateOrCreate(
            ['post_id' => $post->id],
            [
                'content' => $data['content'],
                'custom_fields' => [
                    'seo_title' => $data['seo_title'],
                    'summary' => $data['summary'],
                    'cover_image_url' => $data['cover_image_url'],
                    'cover_attachment_id' => $coverSelection['cover_attachment_id'],
                    'attachment_ids' => $attachmentIds,
                ],
            ],
        );

        $this->syncPostCoverMedia($post, $coverSelection['cover_media_file_id'] ?? null);

        $tagIds = collect(explode(',', (string) ($validated['tags'] ?? '')))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->take(8)
            ->map(function (string $tag): int {
                $slug = Str::slug($tag);
                $slug = filled($slug) ? $slug : 'tag-'.substr(md5($tag), 0, 12);

                return Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $tag, 'count' => 0],
                )->id;
            })
            ->all();

        $post->tags()->sync($tagIds);

        Tag::query()->get()->each(fn (Tag $tag) => $tag->update(['count' => $tag->posts()->count()]));

        return redirect()
            ->route('member.posts.index')
            ->with('status', $validated['status'] === 'pending'
                ? '稿件更新成功，已重新进入审核队列。'
                : '稿件草稿已更新。');
    }

    protected function postRules(Request $request): array
    {
        $modelId = Post::resolveModelIdFromCategory(
            $request->input('category_id'),
            $request->input('model_id'),
        );
        $isFlashModel = Post::isFlashModelId($modelId);

        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => [$isFlashModel ? 'required' : 'nullable', 'string', 'max:1000'],
            'content' => [$isFlashModel ? 'nullable' : 'required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'model_id' => ['nullable', 'exists:content_models,id'],
            'tags' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'cover_image_url' => ['prohibited'],
            'cover_attachment_id' => ['nullable', 'integer'],
            'attachment_ids' => ['nullable', 'array'],
            'attachment_ids.*' => ['integer'],
            'cover_upload' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', 'in:draft,pending'],
        ];
    }

    protected function postMessages(): array
    {
        return [
            'cover_upload.image' => '封面必须是图片文件。',
            'cover_upload.max' => '封面图片不能超过 5MB。',
            'cover_upload.uploaded' => '封面上传失败，请重试；如果图片较大，请先压缩到 5MB 以内。',
        ];
    }

    protected function interceptFailedCoverUpload(Request $request): ?RedirectResponse
    {
        $rawUpload = $_FILES['cover_upload'] ?? null;

        if (! is_array($rawUpload)) {
            return null;
        }

        $uploadName = (string) ($rawUpload['name'] ?? '');
        $uploadError = (int) ($rawUpload['error'] ?? UPLOAD_ERR_OK);

        if ($uploadName === '' || $uploadError === UPLOAD_ERR_OK) {
            return null;
        }

        UploadDiagnostics::log('member.cover.failed.before_validation', array_merge(
            UploadDiagnostics::baseContext($request),
            [
                'error_code' => $uploadError,
                'error_message' => $this->coverUploadErrorMessage($uploadError),
                'original_name' => $uploadName,
                'reported_size' => $rawUpload['size'] ?? null,
                'tmp_name' => $rawUpload['tmp_name'] ?? null,
            ]
        ), 'warning');

        return back()
            ->withInput()
            ->withErrors([
                'cover_upload' => $this->coverUploadErrorMessage($uploadError),
            ]);
    }

    protected function coverUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => '封面上传失败：当前服务端上传上限仍然拦截了这张图片。',
            UPLOAD_ERR_FORM_SIZE => '封面上传失败：图片超出了表单允许的大小。',
            UPLOAD_ERR_PARTIAL => '封面上传失败：文件只上传了一部分，请重新上传。',
            UPLOAD_ERR_NO_FILE => '封面上传失败：这次请求里没有带上图片文件。',
            UPLOAD_ERR_NO_TMP_DIR => '封面上传失败：服务器临时目录不可用。',
            UPLOAD_ERR_CANT_WRITE => '封面上传失败：服务器写入临时文件时出错。',
            UPLOAD_ERR_EXTENSION => '封面上传失败：服务器扩展拦截了这次上传。',
            default => '封面上传失败：服务器在接收图片时出现未知错误。',
        };
    }

    protected function resolveCoverSelection(
        Request $request,
        int $userId,
        ?string $fallback = null,
        ?int $fallbackAttachmentId = null,
    ): array {
        if ($request->hasFile('cover_upload')) {
            $attachment = $this->storeCoverUpload($request->file('cover_upload'), $userId);

            return [
                'cover_image_url' => $attachment?->url,
                'cover_attachment_id' => $attachment?->id,
                'cover_media_file_id' => $attachment?->media_file_id,
            ];
        }

        if (filled($request->input('cover_attachment_id'))) {
            $attachment = $this->resolveOwnedCoverAttachment((int) $request->input('cover_attachment_id'), $userId);

            return [
                'cover_image_url' => $attachment->url,
                'cover_attachment_id' => $attachment->id,
                'cover_media_file_id' => $attachment->media_file_id,
            ];
        }

        return [
            'cover_image_url' => $fallback,
            'cover_attachment_id' => $fallbackAttachmentId,
            'cover_media_file_id' => $this->resolveFallbackCoverMediaFileId($fallbackAttachmentId, $userId),
        ];
    }

    protected function storeCoverUpload(?UploadedFile $file, int $userId): ?Attachment
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $storedPath = $file->store('attachments/member-post-covers', 'public');

        $attachment = Attachment::query()->create([
            'user_id' => $userId,
            'filename' => basename($storedPath),
            'filepath' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        $mediaFile = $this->ensureAttachmentMediaFile($attachment, $userId);

        if ($mediaFile) {
            $attachment->update(['media_file_id' => $mediaFile->id]);
        }

        UploadDiagnostics::log('member.cover.stored', UploadDiagnostics::uploadedFileContext($file, [
            'user_id' => $userId,
            'stored_path' => $storedPath,
            'attachment_id' => $attachment->id,
            'media_file_id' => $mediaFile?->id,
        ]));

        return $attachment;
    }

    protected function resolveOwnedCoverAttachment(int $attachmentId, int $userId): Attachment
    {
        $attachment = Attachment::query()
            ->whereKey($attachmentId)
            ->where('user_id', $userId)
            ->where('mime_type', 'like', 'image/%')
            ->first();

        if (! $attachment) {
            throw ValidationException::withMessages([
                'cover_attachment_id' => '只能选择自己上传到媒体库的图片。',
            ]);
        }

        if (! $attachment->media_file_id) {
            $mediaFile = $this->ensureAttachmentMediaFile($attachment, $userId);

            if ($mediaFile) {
                $attachment->forceFill(['media_file_id' => $mediaFile->id])->save();
            }
        }

        return $attachment;
    }

    protected function loadMemberCoverAttachments(int $userId): Collection
    {
        return Attachment::query()
            ->where('user_id', $userId)
            ->where('mime_type', 'like', 'image/%')
            ->latest()
            ->get();
    }

    protected function loadMemberAttachments(int $userId): Collection
    {
        return Attachment::query()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    protected function resolveOwnedAttachmentIds(array $attachmentIds, int $userId): array
    {
        $normalizedIds = collect($attachmentIds)
            ->map(fn ($attachmentId): int => (int) $attachmentId)
            ->filter(fn (int $attachmentId): bool => $attachmentId > 0)
            ->unique()
            ->values();

        if ($normalizedIds->isEmpty()) {
            return [];
        }

        $ownedIds = Attachment::query()
            ->where('user_id', $userId)
            ->whereIn('id', $normalizedIds)
            ->pluck('id')
            ->map(fn ($attachmentId): int => (int) $attachmentId);

        if ($ownedIds->count() !== $normalizedIds->count()) {
            throw ValidationException::withMessages([
                'attachment_ids' => '正文附件只能选择自己上传到媒体库的文件。',
            ]);
        }

        return $normalizedIds->all();
    }

    protected function syncPostCoverMedia(Post $post, ?int $mediaFileId): void
    {
        if (blank($mediaFileId)) {
            return;
        }

        $post->coverMediaFiles()->sync([
            $mediaFileId => ['collection' => 'cover', 'sort_order' => 0],
        ]);
    }

    protected function resolveFallbackCoverMediaFileId(?int $fallbackAttachmentId, int $userId): ?int
    {
        if (blank($fallbackAttachmentId)) {
            return null;
        }

        $attachment = Attachment::query()
            ->whereKey($fallbackAttachmentId)
            ->where('user_id', $userId)
            ->first();

        if (! $attachment) {
            return null;
        }

        if ($attachment->media_file_id) {
            return (int) $attachment->media_file_id;
        }

        return $this->ensureAttachmentMediaFile($attachment, $userId)?->id;
    }

    protected function ensureAttachmentMediaFile(Attachment $attachment, int $userId): ?MediaFile
    {
        if ($attachment->media_file_id) {
            return MediaFile::query()->find($attachment->media_file_id);
        }

        if (blank($attachment->filepath) || ! Storage::disk('public')->exists($attachment->filepath)) {
            UploadDiagnostics::log('member.cover.media_sync_missing_source', [
                'user_id' => $userId,
                'attachment_id' => $attachment->id,
                'filepath' => $attachment->filepath,
            ], 'warning');

            return null;
        }

        $mediaFile = MediaFile::create([
            'uploaded_by_user_id' => $userId,
            'name' => pathinfo($attachment->filename, PATHINFO_FILENAME),
            'caption' => $attachment->filename,
            'alt_text' => $attachment->filename,
        ]);

        $media = $mediaFile
            ->addMediaFromDisk($attachment->filepath, 'public')
            ->preservingOriginal()
            ->toMediaCollection('default', 'public');

        $mediaFile->update([
            'size' => $media->size,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
            'width' => $media->getCustomProperty('width'),
            'height' => $media->getCustomProperty('height'),
        ]);

        UploadDiagnostics::log('member.cover.media_synced', [
            'user_id' => $userId,
            'attachment_id' => $attachment->id,
            'media_file_id' => $mediaFile->id,
            'filepath' => $attachment->filepath,
        ]);

        return $mediaFile;
    }
}
