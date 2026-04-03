<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('model_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('content_models')
                ->nullOnDelete();
        });

        $newsModelId = DB::table('content_models')->where('table_name', 'posts_news')->value('id');
        $flashModelId = DB::table('content_models')->where('table_name', 'posts_flash')->value('id');

        if ($newsModelId) {
            DB::table('categories')
                ->whereNull('model_id')
                ->update(['model_id' => $newsModelId]);
        }

        if ($flashModelId && DB::table('posts')->where('model_id', $flashModelId)->exists()) {
            $flashCategory = DB::table('categories')->where('slug', 'kuaixun')->first();

            if (! $flashCategory) {
                $flashCategoryId = DB::table('categories')->insertGetId([
                    'parent_id' => null,
                    'model_id' => $flashModelId,
                    'name' => '快讯',
                    'slug' => 'kuaixun',
                    'description' => '快讯栏目，统一承接快讯模型内容。',
                    'sort_order' => 0,
                    'level' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $flashCategoryId = $flashCategory->id;

                DB::table('categories')
                    ->where('id', $flashCategoryId)
                    ->update([
                        'model_id' => $flashModelId,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('posts')
                ->where('model_id', $flashModelId)
                ->update([
                    'category_id' => $flashCategoryId,
                    'updated_at' => now(),
                ]);

            $flashCategorySlug = DB::table('categories')->where('id', $flashCategoryId)->value('slug') ?? 'kuaixun';

            DB::table('posts')
                ->where('model_id', $flashModelId)
                ->orderBy('id')
                ->get(['id'])
                ->each(function (object $post) use ($flashCategorySlug): void {
                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update([
                            'slug' => "{$flashCategorySlug}-{$post->id}",
                            'updated_at' => now(),
                        ]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('model_id');
        });
    }
};
