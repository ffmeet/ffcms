<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            if (! Schema::hasColumn('comments', 'author_type')) {
                $table->string('author_type')->nullable()->after('parent_id');
            }

            if (! Schema::hasColumn('comments', 'author_id')) {
                $table->unsignedBigInteger('author_id')->nullable()->after('author_type');
            }

            if (! Schema::hasColumn('comments', 'commentable_type')) {
                $table->string('commentable_type')->nullable()->after('author_id');
            }

            if (! Schema::hasColumn('comments', 'commentable_id')) {
                $table->unsignedBigInteger('commentable_id')->nullable()->after('commentable_type');
            }

            if (! Schema::hasColumn('comments', 'body')) {
                $table->text('body')->nullable()->after('content');
            }
        });

        if (! collect(Schema::getIndexes('comments'))->has('comments_commentable_type_commentable_id_index')) {
            Schema::table('comments', function (Blueprint $table): void {
                $table->index(['commentable_type', 'commentable_id']);
            });
        }

        DB::table('comments')
            ->orderBy('id')
            ->get()
            ->each(function (object $comment): void {
                DB::table('comments')
                    ->where('id', $comment->id)
                    ->update([
                        'author_type' => $comment->author_type ?: ($comment->user_id ? User::class : null),
                        'author_id' => $comment->author_id ?: $comment->user_id,
                        'commentable_type' => $comment->commentable_type ?: ($comment->post_id ? Post::class : null),
                        'commentable_id' => $comment->commentable_id ?: $comment->post_id,
                        'body' => $comment->body ?: $comment->content,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            foreach (['comments_commentable_type_commentable_id_index'] as $index) {
                if (collect(Schema::getIndexes('comments'))->has($index)) {
                    $table->dropIndex($index);
                }
            }

            foreach (['author_type', 'author_id', 'commentable_type', 'commentable_id', 'body'] as $column) {
                if (Schema::hasColumn('comments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
