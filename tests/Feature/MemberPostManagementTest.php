<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\PostDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_own_posts_index(): void
    {
        [$user, $category, $model] = $this->createBaseData();

        Post::create([
            'title' => '我的第一篇稿件',
            'slug' => 'my-first-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('member.posts.index'))
            ->assertOk()
            ->assertSee('我的稿件')
            ->assertSee('我的第一篇稿件');
    }

    public function test_member_can_update_own_post(): void
    {
        [$user, $category, $model] = $this->createBaseData();

        $post = Post::create([
            'title' => '旧标题',
            'slug' => 'old-slug',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要'],
        ]);

        $this->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '新标题',
                'summary' => '新摘要',
                'content' => '更新后的正文',
                'category_id' => $category->id,
                'model_id' => $model->id,
                'tags' => '更新, 稿件',
                'status' => 'pending',
            ])
            ->assertRedirect(route('member.posts.index'));

        $post->refresh();

        $this->assertSame('新标题', $post->title);
        $this->assertSame('pending', $post->status);
        $this->assertSame('更新后的正文', $post->detail?->content);
        $this->assertSame('新摘要', $post->summary);
    }

    public function test_member_cannot_edit_others_post(): void
    {
        [$user, $category, $model] = $this->createBaseData();
        $otherUser = User::factory()->create();

        $post = Post::create([
            'title' => '别人的稿件',
            'slug' => 'others-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $otherUser->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('member.posts.edit', $post))
            ->assertForbidden();
    }

    public function test_member_posts_index_supports_status_filter_and_keyword_search(): void
    {
        [$user, $category, $model] = $this->createBaseData();

        Post::create([
            'title' => '待审核新闻稿',
            'slug' => 'pending-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        Post::create([
            'title' => '草稿随笔',
            'slug' => 'draft-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('member.posts.index', ['status' => 'pending', 'q' => '新闻']))
            ->assertOk()
            ->assertSee('待审核新闻稿')
            ->assertDontSee('草稿随笔');
    }

    public function test_member_posts_index_supports_title_sort(): void
    {
        [$user, $category, $model] = $this->createBaseData();

        Post::create([
            'title' => 'B 稿件',
            'slug' => 'b-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        Post::create([
            'title' => 'A 稿件',
            'slug' => 'a-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('member.posts.index', ['sort' => 'title']));

        $response->assertOk();
        $response->assertSeeInOrder(['A 稿件', 'B 稿件']);
    }

    private function createBaseData(): array
    {
        $group = MemberGroup::create([
            'name' => '普通会员',
            'min_points' => 0,
            'max_points' => 999,
        ]);

        $user = User::create([
            'username' => 'member-manager',
            'email' => 'member-manager@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => '新闻中心',
            'slug' => 'news',
        ]);

        $model = ContentModel::create([
            'name' => '新闻文章',
            'table_name' => 'posts_news',
        ]);

        return [$user, $category, $model];
    }
}
