<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

        return view('site.member.posts.index', [
            'posts' => $posts,
            'user' => $request->user(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        return view('site.member.posts.create', [
            'categories' => Category::query()->with('contentModel')->orderBy('sort_order')->orderBy('id')->get(),
            'user' => $request->user(),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Request $request, Post $post): View
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        return view('site.member.posts.edit', [
            'post' => $post->load('detail', 'tags', 'contentModel', 'category.contentModel'),
            'categories' => Category::query()->with('contentModel')->orderBy('sort_order')->orderBy('id')->get(),
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->postRules($request));

        $modelId = Post::ensureCategoryMatchesModel($validated['category_id']);

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
            ],
        ]);

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

        $validated = $request->validate($this->postRules($request));

        $modelId = Post::ensureCategoryMatchesModel($validated['category_id'], $post->model_id);

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
                ],
            ],
        );

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
        $modelId = Post::resolveModelIdFromCategory($request->input('category_id'));
        $isFlashModel = Post::isFlashModelId($modelId);

        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => [$isFlashModel ? 'required' : 'nullable', 'string', 'max:1000'],
            'content' => [$isFlashModel ? 'nullable' : 'required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,pending'],
        ];
    }
}
