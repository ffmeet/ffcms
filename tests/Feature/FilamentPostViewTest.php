<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentPostViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_post_view_page(): void
    {
        $group = MemberGroup::create([
            'name' => '管理员组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $user = User::create([
            'username' => 'admin-view',
            'email' => 'admin-view@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => '查看栏目',
            'slug' => 'view-category',
            'sort_order' => 1,
            'level' => 0,
        ]);

        $contentModel = ContentModel::create([
            'name' => '查看模型',
            'table_name' => 'posts_view',
        ]);

        $post = Post::create([
            'title' => 'Filament 查看文章',
            'slug' => 'filament-view-post',
            'category_id' => $category->id,
            'model_id' => $contentModel->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $post->detail()->create([
            'content' => '<p>文章查看页正文</p>',
            'custom_fields' => [
                'summary' => '文章查看页摘要',
                'seo_title' => '文章查看页 SEO 标题',
            ],
        ]);

        $this->actingAs($user)
            ->get("/admin/posts/{$post->id}")
            ->assertOk()
            ->assertSee('Filament 查看文章')
            ->assertSee('文章查看页摘要');
    }
}
