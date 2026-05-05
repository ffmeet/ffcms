<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tilto\Commentable\Models\Comment as BaseComment;

class Comment extends BaseComment
{
    use HasFactory;

    public const MAX_THREAD_DEPTH = 6;

    protected $fillable = [
        'author_id',
        'author_type',
        'commentable_id',
        'commentable_type',
        'user_id',
        'post_id',
        'parent_id',
        'content',
        'body',
        'status',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $comment): void {
            $comment->status = $comment->status ?: 'pending';
            $comment->body = $comment->body ?: $comment->content;
            $comment->content = $comment->content ?: $comment->body;

            if ($comment->user_id && ! $comment->author_id) {
                $comment->author_id = $comment->user_id;
                $comment->author_type = User::class;
            }

            if ($comment->author_id && ! $comment->user_id && $comment->author_type === User::class) {
                $comment->user_id = $comment->author_id;
            }

            if ($comment->post_id && ! $comment->commentable_id) {
                $comment->commentable_id = $comment->post_id;
                $comment->commentable_type = Post::class;
            }

            if ($comment->commentable_id && ! $comment->post_id && $comment->commentable_type === Post::class) {
                $comment->post_id = $comment->commentable_id;
            }
        });

        static::saved(function (self $comment): void {
            $comment->post?->syncCommentStatistics();
        });

        static::deleted(function (self $comment): void {
            $comment->post?->syncCommentStatistics();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function depth(): int
    {
        $depth = 0;
        $current = $this->parent;

        while ($current) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }
}
