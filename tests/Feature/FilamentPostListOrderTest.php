<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Category;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentPostListOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_post_list_orders_latest_published_posts_first(): void
    {
        [$admin, $category, $model] = $this->createAdminListingContext();

        $oldPublished = Post::create([
            'title' => '较早发布',
            'slug' => 'older-published',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $admin->id,
            'status' => 'published',
            'published_at' => now()->subDays(3),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $newPublished = Post::create([
            'title' => '最新发布',
            'slug' => 'newest-published',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $admin->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        $newPending = Post::create([
            'title' => '刚进待审核',
            'slug' => 'fresh-pending',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $admin->id,
            'status' => 'pending',
            'published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $component = Livewire::actingAs($admin)
            ->test(ListPosts::class, ['view' => null]);

        $posts = $component->instance()->posts->items();

        $this->assertSame($newPending->id, $posts[0]->id);
        $this->assertSame($newPublished->id, $posts[1]->id);
        $this->assertSame($oldPublished->id, $posts[2]->id);
    }

    public function test_draft_view_orders_latest_created_drafts_first(): void
    {
        [$admin, $category, $model] = $this->createAdminListingContext();

        $olderDraft = Post::create([
            'title' => '旧草稿',
            'slug' => 'older-draft',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $admin->id,
            'status' => 'draft',
            'published_at' => null,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $newerDraft = Post::create([
            'title' => '新草稿',
            'slug' => 'newer-draft',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $admin->id,
            'status' => 'draft',
            'published_at' => null,
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        $component = Livewire::actingAs($admin)
            ->test(ListPosts::class, ['view' => 'draft']);

        $posts = $component->instance()->posts->items();

        $this->assertSame($newerDraft->id, $posts[0]->id);
        $this->assertSame($olderDraft->id, $posts[1]->id);
    }

    protected function createAdminListingContext(): array
    {
        $group = MemberGroup::create([
            'name' => '文章列表管理员组',
            'min_points' => 0,
            'max_points' => 999999,
            'permissions' => [
                'admin.access' => true,
                'member.center' => true,
            ],
        ]);

        $admin = User::create([
            'username' => 'post-list-admin',
            'email' => 'post-list-admin@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $model = ContentModel::create([
            'name' => '新闻模型',
            'table_name' => 'posts_news',
        ]);

        $category = Category::create([
            'name' => '新闻栏目',
            'slug' => 'news-category',
            'model_id' => $model->id,
            'sort_order' => 1,
            'level' => 0,
        ]);

        return [$admin, $category, $model];
    }

    public function test_bulk_approve_updates_selected_posts_to_published(): void
    {
        [$admin, $category, $model] = $this->createAdminListingContext();

        $pendingA = Post::create([
            'title' => '待审核 A',
            'slug' => 'pending-a',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $admin->id,
            'status' => 'pending',
            'published_at' => null,
        ]);

        $pendingB = Post::create([
            'title' => '待审核 B',
            'slug' => 'pending-b',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $admin->id,
            'status' => 'pending',
            'published_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(ListPosts::class)
            ->set('selectedPostIds', [$pendingA->id, $pendingB->id])
            ->call('approveSelected')
            ->assertSet('selectedPostIds', []);

        $this->assertDatabaseHas('posts', [
            'id' => $pendingA->id,
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $pendingB->id,
            'status' => 'published',
        ]);

        $this->assertNotNull($pendingA->fresh()->published_at);
        $this->assertNotNull($pendingB->fresh()->published_at);
    }
}
