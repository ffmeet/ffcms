<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPostSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_view_submission_form(): void
    {
        $user = $this->createMember();
        Category::create(['name' => '新闻中心', 'slug' => 'news']);
        ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);

        $response = $this->actingAs($user)->get(route('member.posts.create'));

        $response->assertOk();
        $response->assertSee('发布新稿件');
    }

    public function test_authenticated_member_can_submit_pending_post(): void
    {
        $user = $this->createMember();
        $category = Category::create(['name' => '新闻中心', 'slug' => 'news']);
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);

        $response = $this->actingAs($user)->post(route('member.posts.store'), [
            'title' => '会员投稿测试',
            'summary' => '一条从会员中心发出的测试稿件',
            'content' => '这里是会员投稿正文。',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'tags' => '会员投稿, 测试',
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        $post = Post::query()->first();

        $this->assertNotNull($post);
        $this->assertSame($user->id, $post->user_id);
        $this->assertSame('pending', $post->status);
        $this->assertCount(2, $post->tags);
        $this->assertSame('一条从会员中心发出的测试稿件', $post->summary);
    }

    private function createMember(): User
    {
        $group = MemberGroup::create([
            'name' => '普通会员',
            'min_points' => 0,
            'max_points' => 999,
        ]);

        return User::create([
            'username' => 'member01',
            'email' => 'member01@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);
    }
}
