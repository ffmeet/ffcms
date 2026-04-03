<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberCommentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_own_comments_index(): void
    {
        [$user, $post] = $this->createBaseData();

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '这是我的评论',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('member.comments.index'))
            ->assertOk()
            ->assertSee('我的评论')
            ->assertSee('这是我的评论')
            ->assertSee($post->title);
    }

    public function test_member_comments_page_shows_reply_target(): void
    {
        [$user, $post] = $this->createBaseData();
        $otherUser = User::factory()->create();

        $parent = Comment::create([
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
            'content' => '主评论',
            'status' => 'approved',
        ]);

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'content' => '我的回复',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('member.comments.index'))
            ->assertOk()
            ->assertSee('回复')
            ->assertSee($otherUser->username);
    }

    public function test_member_comments_index_supports_status_filter_and_keyword_search(): void
    {
        [$user, $post] = $this->createBaseData();

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '这是待审核评论',
            'status' => 'pending',
        ]);

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '这是已通过评论',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('member.comments.index', ['status' => 'approved', 'q' => '通过']))
            ->assertOk()
            ->assertSee('这是已通过评论')
            ->assertDontSee('这是待审核评论');
    }

    public function test_member_comments_index_supports_status_sort(): void
    {
        [$user, $post] = $this->createBaseData();

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '待审核评论',
            'status' => 'pending',
        ]);

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '已通过评论',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get(route('member.comments.index', ['sort' => 'status']));

        $response->assertOk();
        $response->assertSeeInOrder(['已通过评论', '待审核评论']);
    }

    private function createBaseData(): array
    {
        $group = MemberGroup::create([
            'name' => '普通会员',
            'min_points' => 0,
            'max_points' => 999,
        ]);

        $user = User::create([
            'username' => 'member-commenter',
            'email' => 'member-commenter@example.com',
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

        $post = Post::create([
            'title' => '评论文章',
            'slug' => 'comment-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        return [$user, $post];
    }
}
