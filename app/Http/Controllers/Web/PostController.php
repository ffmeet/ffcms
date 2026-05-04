<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Support\SiteTheme;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function __invoke(string $slug): View
    {
        $post = Post::query()
            ->with([
                'category',
                'contentModel',
                'user',
                'detail',
                'tags',
                'statistics',
                'coverMediaFiles.media',
                'attachmentMediaFiles.media',
            ])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
        $post->loadResolvedAttachments();

        $activeReplyId = (int) old('parent_id', request()->integer('reply'));
        $focusedCommentId = request()->integer('focus');

        $commentThreads = $this->buildCommentThreads(
            Comment::query()
                ->with(['user', 'parent.user'])
                ->where('post_id', $post->id)
                ->where('status', 'approved')
                ->orderBy('created_at')
                ->get(),
            $activeReplyId,
            $focusedCommentId,
        );

        $relatedPosts = Post::query()
            ->with(['category', 'contentModel', 'detail', 'coverMediaFiles.media'])
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->id)
            ->published()
            ->latest('published_at')
            ->limit(4)
            ->get();

        $authorMorePosts = Post::query()
            ->with(['category', 'detail', 'statistics', 'coverMediaFiles.media'])
            ->where('user_id', $post->user_id)
            ->whereKeyNot($post->id)
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view(SiteTheme::view('pages.post-show', 'themes.default.pages.post-show'), [
            'post' => $post,
            'attachments' => $this->normalizeAttachments($post),
            'commentThreads' => $commentThreads,
            'maxReplyDepth' => Comment::MAX_THREAD_DEPTH,
            'relatedPosts' => $relatedPosts,
            'authorMorePosts' => $authorMorePosts,
        ]);
    }

    protected function normalizeAttachments(Post $post): Collection
    {
        $mediaAttachments = $post->attachmentMediaFiles->map(function ($attachment): array {
            return [
                'name' => (string) $attachment->name,
                'extension' => (string) ($attachment->extension ?? pathinfo((string) $attachment->name, PATHINFO_EXTENSION)),
                'url' => (string) $attachment->getUrl(),
                'source' => 'media-manager',
            ];
        });

        $memberAttachments = $post->resolveAttachments()->map(function ($attachment): array {
            return [
                'name' => (string) $attachment->filename,
                'extension' => (string) pathinfo((string) $attachment->filename, PATHINFO_EXTENSION),
                'url' => (string) $attachment->url,
                'source' => 'member-library',
            ];
        });

        return $mediaAttachments
            ->concat($memberAttachments)
            ->unique(fn (array $attachment): string => $attachment['url'].'|'.$attachment['name'])
            ->values();
    }

    /**
     * @param  Collection<int, Comment>  $comments
     * @return Collection<int, Comment>
     */
    protected function buildCommentThreads(Collection $comments, int $activeReplyId, int $focusedCommentId): Collection
    {
        $grouped = $comments->groupBy('parent_id');

        $buildTree = function ($parentId, int $depth) use (&$buildTree, $grouped, $activeReplyId, $focusedCommentId): Collection {
            return ($grouped->get($parentId) ?? collect())
                ->values()
                ->map(function (Comment $comment) use (&$buildTree, $depth, $activeReplyId, $focusedCommentId) {
                    $children = $buildTree($comment->id, $depth + 1);

                    $comment->setRelation('children', $children);
                    $comment->depth_level = $depth;
                    $comment->reply_count = $children->sum(fn (Comment $child): int => 1 + (int) ($child->reply_count ?? 0));
                    $comment->has_active_path = $comment->id === $activeReplyId
                        || $comment->id === $focusedCommentId
                        || $children->contains(fn (Comment $child): bool => (bool) ($child->has_active_path ?? false));

                    return $comment;
                });
        };

        return $buildTree(null, 0);
    }
}
