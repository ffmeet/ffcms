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

    public function test_guest_can_open_admin_login_page(): void
    {
        $this->get('/admin/login')
            ->assertOk();
    }

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

    public function test_non_admin_session_is_forbidden_from_admin_panel(): void
    {
        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'plain-member',
            'email' => 'plain-member@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_open_post_edit_page_even_when_legacy_content_is_scalar(): void
    {
        $group = MemberGroup::create([
            'name' => '管理员编辑组',
            'min_points' => 0,
            'max_points' => 999999,
            'permissions' => ['admin.access'],
        ]);

        $user = User::create([
            'username' => 'admin-edit',
            'email' => 'admin-edit@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => '编辑栏目',
            'slug' => 'edit-category',
            'sort_order' => 1,
            'level' => 0,
        ]);

        $contentModel = ContentModel::create([
            'name' => '编辑模型',
            'table_name' => 'posts_edit',
        ]);

        $post = Post::create([
            'title' => '旧内容文章',
            'slug' => 'legacy-content-post',
            'category_id' => $category->id,
            'model_id' => $contentModel->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $post->detail()->create([
            'content' => '11',
            'custom_fields' => [
                'summary' => '旧摘要',
                'seo_title' => '旧 SEO',
            ],
        ]);

        $this->actingAs($user)
            ->get("/admin/posts/{$post->id}/edit")
            ->assertOk()
            ->assertDontSee('Internal Server Error')
            ->assertDontSee('Trying to access array offset on int');
    }
}
