<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuickSearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(Filament::auth()->check(), 403);

        $query = trim((string) $request->string('q'));

        if ($query === '') {
            return response()->json([
                'results' => [],
            ]);
        }

        $results = [
            ...$this->searchPosts($query),
            ...$this->searchCategories($query),
            ...$this->searchTags($query),
            ...$this->searchUsers($query),
            ...$this->searchComments($query),
        ];

        return response()->json([
            'results' => array_values($results),
        ]);
    }

    protected function searchPosts(string $query): array
    {
        return Post::query()
            ->with(['category', 'user'])
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Post $post): array => [
                'group' => '文章',
                'title' => $post->title,
                'meta' => collect([
                    $post->category?->name,
                    $post->user?->username,
                ])->filter()->implode(' · '),
                'url' => PostResource::getUrl('edit', ['record' => $post]),
            ])
            ->all();
    }

    protected function searchCategories(string $query): array
    {
        return Category::query()
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Category $category): array => [
                'group' => '栏目',
                'title' => $category->name,
                'meta' => $category->slug,
                'url' => CategoryResource::getUrl('edit', ['record' => $category]),
            ])
            ->all();
    }

    protected function searchTags(string $query): array
    {
        return Tag::query()
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Tag $tag): array => [
                'group' => '标签',
                'title' => $tag->name,
                'meta' => $tag->slug,
                'url' => TagResource::getUrl('edit', ['record' => $tag]),
            ])
            ->all();
    }

    protected function searchUsers(string $query): array
    {
        return User::query()
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('username')
            ->limit(5)
            ->get()
            ->map(fn (User $user): array => [
                'group' => '会员',
                'title' => $user->username,
                'meta' => $user->email,
                'url' => UserResource::getUrl('edit', ['record' => $user]),
            ])
            ->all();
    }

    protected function searchComments(string $query): array
    {
        return Comment::query()
            ->with(['user', 'post'])
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('body', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Comment $comment): array => [
                'group' => '评论',
                'title' => Str::limit(trim((string) ($comment->body ?: $comment->content)), 36),
                'meta' => collect([
                    $comment->user?->username,
                    $comment->post?->title,
                ])->filter()->implode(' · '),
                'url' => CommentResource::getUrl('edit', ['record' => $comment]),
            ])
            ->all();
    }
}
