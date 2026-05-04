<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $parentComment = null;

        if (filled($data['parent_id'] ?? null)) {
            $parentComment = Comment::query()
                ->with('parent.parent.parent.parent.parent.parent')
                ->whereKey($data['parent_id'])
                ->where('post_id', $post->id)
                ->firstOrFail();

            if ($parentComment->depth() >= Comment::MAX_THREAD_DEPTH - 1) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'body' => '当前评论链路已达到最大回复层级，请直接回复这一层或新开讨论。',
                    ]);
            }
        }

        $comment = Comment::create([
            'author_id' => $request->user()->id,
            'author_type' => $request->user()::class,
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
            'parent_id' => $parentComment?->id,
            'body' => $data['body'],
            'status' => 'pending',
        ]);

        $focusCommentId = $parentComment?->id ?? $comment->id;

        return redirect()
            ->route('posts.show', [
                'slug' => $post->slug,
                'reply' => $parentComment?->id,
                'focus' => $focusCommentId,
            ])
            ->withFragment('comment-'.$focusCommentId)
            ->with('status', $parentComment
                ? '回复已提交，审核通过后会显示在讨论串中。'
                : '评论已提交，审核通过后会显示在文章页。');
    }
}
