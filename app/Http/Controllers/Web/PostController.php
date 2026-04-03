<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
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
                'comments.user',
                'comments.children.user',
                'statistics',
                'coverMediaFiles.media',
                'attachmentMediaFiles.media',
            ])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $commentThreads = $post->comments
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->sortBy('created_at')
            ->values()
            ->map(function ($comment) {
                $comment->setRelation(
                    'children',
                    $comment->children
                        ->where('status', 'approved')
                        ->sortBy('created_at')
                        ->values(),
                );

                return $comment;
            });

        $relatedPosts = Post::query()
            ->with(['category', 'contentModel', 'detail', 'coverMediaFiles.media'])
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->id)
            ->published()
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('site.post.show', [
            'post' => $post,
            'attachments' => $post->attachmentMediaFiles,
            'commentThreads' => $commentThreads,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
