<?php

namespace App\Models;

use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;
use Slimani\MediaManager\Form\RichEditor\MediaManagerRichContentPlugin;
use Slimani\MediaManager\Concerns\InteractsWithMediaFiles;
use Slimani\MediaManager\Models\File;
use Tilto\Commentable\Contracts\Commentable as CommentableContract;
use Tilto\Commentable\Traits\HasComments;

class Post extends Model implements CommentableContract, HasRichContent
{
    public const FLASH_MODEL_TABLE_NAMES = [
        'posts_flash',
        'posts_kx',
    ];

    use HasComments;
    use HasFactory;
    use InteractsWithRichContent;
    use InteractsWithMediaFiles;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'model_id',
        'user_id',
        'status',
        'published_at',
        'is_headline',
        'is_featured',
        'is_recommended',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_headline' => 'boolean',
            'is_featured' => 'boolean',
            'is_recommended' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at');
    }

    public function scopeNonFlash(Builder $query): Builder
    {
        return $query->whereHas('contentModel', function (Builder $builder): void {
            $builder->whereNotIn('table_name', self::FLASH_MODEL_TABLE_NAMES);
        });
    }

    public function scopeHeadline(Builder $query): Builder
    {
        return $query->where('is_headline', true);
    }

    public function scopeFeaturedPlacement(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecommendedPlacement(Builder $query): Builder
    {
        return $query->where('is_recommended', true);
    }

    protected function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->plugins([
                MediaManagerRichContentPlugin::make()
                    ->acceptedFileTypes(['image/*', 'video/*', 'application/pdf']),
            ]);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function contentModel(): BelongsTo
    {
        return $this->belongsTo(ContentModel::class, 'model_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(PostDetail::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    public function coverMediaFiles(): MorphToMany
    {
        return $this->mediaFiles('cover');
    }

    public function attachmentMediaFiles(): MorphToMany
    {
        return $this->mediaFiles('attachments');
    }

    public function statistics(): HasOne
    {
        return $this->hasOne(PostStatistic::class);
    }

    public static function normalizePublishingData(array $data): array
    {
        return static::normalizePublishingDataForRecord($data);
    }

    public static function normalizePublishingDataForRecord(array $data, ?int $ignorePostId = null): array
    {
        $title = (string) ($data['title'] ?? '');
        $slugSource = (string) ($data['slug'] ?? '');

        $data['slug'] = static::generateUniqueSlug(
            filled($slugSource) ? $slugSource : $title,
            $ignorePostId,
        );
        $seoTitle = trim((string) ($data['seo_title'] ?? ''));
        $summary = trim((string) ($data['summary'] ?? ''));

        if (($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        if (($data['status'] ?? null) !== 'published') {
            $data['published_at'] = null;
        }

        $data['is_headline'] = (bool) ($data['is_headline'] ?? false);
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['is_recommended'] = (bool) ($data['is_recommended'] ?? false);

        if (static::isFlashModelId($data['model_id'] ?? null)) {
            $data['is_headline'] = false;
            $data['is_featured'] = false;
            $data['is_recommended'] = false;
        }

        if ($data['is_headline']) {
            $data['is_featured'] = false;
            $data['is_recommended'] = false;
        }

        if ($data['is_featured']) {
            $data['is_recommended'] = false;
        }

        if ($data['is_recommended']) {
            $data['is_featured'] = false;
        }

        $data['seo_title'] = filled($seoTitle) ? $seoTitle : $title;

        if (blank($summary) && filled($data['content'] ?? null)) {
            $summary = Str::limit(trim(strip_tags((string) $data['content'])), 140, '...');
        }

        $data['summary'] = $summary;

        return $data;
    }

    public static function syncEditorialPlacementsForRecord(self $record): void
    {
        if (! $record->is_headline) {
            return;
        }

        static::query()
            ->whereKeyNot($record->getKey())
            ->where('is_headline', true)
            ->update(['is_headline' => false]);
    }

    public static function resolveModelIdFromCategory(?int $categoryId, mixed $fallback = null): ?int
    {
        if (blank($categoryId)) {
            return filled($fallback) ? (int) $fallback : null;
        }

        $categoryModelId = Category::query()->whereKey($categoryId)->value('model_id');

        return filled($categoryModelId) ? (int) $categoryModelId : (filled($fallback) ? (int) $fallback : null);
    }

    public static function ensureCategoryMatchesModel(?int $categoryId, mixed $modelId = null): int
    {
        $category = Category::query()->with('contentModel')->find($categoryId);

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => '所选栏目不存在。',
            ]);
        }

        if (blank($category->model_id)) {
            if (filled($modelId)) {
                return (int) $modelId;
            }

            throw ValidationException::withMessages([
                'category_id' => '所选栏目尚未绑定内容模型，请先到栏目管理里完成绑定。',
            ]);
        }

        if (filled($modelId) && (int) $category->model_id !== (int) $modelId) {
            throw ValidationException::withMessages([
                'category_id' => '所选栏目与当前内容模型不一致，请重新选择栏目。',
            ]);
        }

        return (int) $category->model_id;
    }

    public static function generateUniqueSlug(string $source, ?int $ignorePostId = null): string
    {
        $baseSlug = Str::slug($source);
        $baseSlug = filled($baseSlug) ? $baseSlug : Str::random(8);
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()
            ->when($ignorePostId, fn (Builder $query) => $query->whereKeyNot($ignorePostId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public static function generateFlashSlugForCategory(?int $categoryId, int $postId): string
    {
        $categorySlug = Category::query()->whereKey($categoryId)->value('slug');
        $baseSlug = filled($categorySlug) ? "{$categorySlug}-{$postId}" : "post-{$postId}";

        return static::generateUniqueSlug($baseSlug, $postId);
    }

    public function getSeoTitleAttribute(): string
    {
        return (string) Arr::get($this->detail?->custom_fields, 'seo_title', $this->title);
    }

    public function getSummaryAttribute(): string
    {
        return (string) Arr::get($this->detail?->custom_fields, 'summary', '');
    }

    public function getAuthorNameAttribute(): string
    {
        return trim((string) Arr::get($this->detail?->custom_fields, 'author_name', ''));
    }

    public function getDisplayAuthorAttribute(): string
    {
        return filled($this->author_name)
            ? $this->author_name
            : (string) ($this->user?->public_display_name ?? $this->user?->username ?? '匿名');
    }

    public function getPublicUrlAttribute(): string
    {
        return route('posts.show', $this->slug);
    }

    public function isFlashModel(): bool
    {
        $tableName = $this->relationLoaded('contentModel')
            ? $this->contentModel?->table_name
            : $this->contentModel()->value('table_name');

        return static::isFlashModelTableName($tableName);
    }

    public static function isFlashModelId(mixed $modelId): bool
    {
        if (blank($modelId)) {
            return false;
        }

        $tableName = ContentModel::query()->whereKey($modelId)->value('table_name');

        return static::isFlashModelTableName($tableName);
    }

    public static function isFlashModelTableName(?string $tableName): bool
    {
        return in_array($tableName, self::FLASH_MODEL_TABLE_NAMES, true);
    }

    public function getCoverMediaAttribute(): ?File
    {
        if ($this->relationLoaded('coverMediaFiles')) {
            return $this->coverMediaFiles->first();
        }

        return $this->coverMediaFiles()->first();
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->coverMedia) {
            return $this->coverMedia->getUrl('preview');
        }

        if ($this->custom_cover_image_url) {
            return $this->custom_cover_image_url;
        }

        return $this->cover_attachment?->url;
    }

    public function getContentAttribute(): ?string
    {
        if ($this->relationLoaded('detail')) {
            return $this->detail?->content;
        }

        return $this->detail()->value('content');
    }

    public function renderContentForFrontend(): string
    {
        $content = $this->content;

        if (blank($content)) {
            return '<p>当前文章还没有正文内容。</p>';
        }

        if (is_array($content) && Arr::has($content, 'type')) {
            try {
                return $this->renderRichContent('content');
            } catch (Throwable) {
                return '<p>当前正文暂时无法按富文本格式渲染。</p>';
            }
        }

        $trimmed = trim((string) $content);

        if ($trimmed === '') {
            return '<p>当前文章还没有正文内容。</p>';
        }

        if (str_starts_with($trimmed, '<')) {
            return $trimmed;
        }

        $decoded = json_decode($trimmed, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && Arr::has($decoded, 'type')) {
            try {
                return $this->renderRichContent('content');
            } catch (Throwable) {
                return '<p>当前正文暂时无法按富文本格式渲染。</p>';
            }
        }

        return nl2br(e($trimmed));
    }

    public function getCoverAttachmentIdAttribute(): ?int
    {
        $coverId = Arr::get($this->detail?->custom_fields, 'cover_attachment_id');

        return filled($coverId) ? (int) $coverId : null;
    }

    public function getAttachmentIdsAttribute(): array
    {
        return array_values(Arr::get($this->detail?->custom_fields, 'attachment_ids', []));
    }

    public function getCustomCoverImageUrlAttribute(): ?string
    {
        $coverImageUrl = Arr::get($this->detail?->custom_fields, 'cover_image_url');

        return filled($coverImageUrl) ? (string) $coverImageUrl : null;
    }

    public function getCoverAttachmentAttribute(): ?Attachment
    {
        $attachments = $this->relationLoaded('resolvedAttachments')
            ? $this->getRelation('resolvedAttachments')
            : collect();

        if (blank($this->cover_attachment_id)) {
            return null;
        }

        return $attachments->firstWhere('id', $this->cover_attachment_id);
    }

    public function resolveAttachments(): Collection
    {
        if (blank($this->attachment_ids)) {
            return collect();
        }

        return Attachment::query()
            ->whereIn('id', $this->attachment_ids)
            ->orderByDesc('id')
            ->get();
    }

    public function loadResolvedAttachments(): static
    {
        $this->setRelation('resolvedAttachments', $this->resolveAttachments());

        return $this;
    }

    public function syncCommentStatistics(): void
    {
        $approvedCommentsCount = $this->comments()
            ->where('status', 'approved')
            ->count();

        $this->statistics()->updateOrCreate(
            ['post_id' => $this->id],
            ['comments_count' => $approvedCommentsCount],
        );
    }
}
