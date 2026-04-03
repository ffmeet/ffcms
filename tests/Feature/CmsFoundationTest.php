<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Comment;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\PostStatistic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CmsFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_core_tables_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('member_groups'));
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasTable('content_models'));
        $this->assertTrue(Schema::hasTable('posts'));
        $this->assertTrue(Schema::hasTable('post_details'));
        $this->assertTrue(Schema::hasTable('post_tags'));
        $this->assertTrue(Schema::hasTable('tags'));
        $this->assertTrue(Schema::hasTable('comments'));
        $this->assertTrue(Schema::hasTable('attachments'));
        $this->assertTrue(Schema::hasTable('post_statistics'));
    }

    public function test_user_model_uses_cms_membership_fields(): void
    {
        $group = MemberGroup::create([
            'name' => '测试会员组',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'cms-admin',
            'email' => 'cms@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->assertSame('password_hash', $user->getAuthPasswordName());
        $this->assertSame($group->id, $user->memberGroup?->id);
        $this->assertNotSame('password', $user->getAuthPassword());
    }

    public function test_approved_comments_sync_post_statistics(): void
    {
        $group = MemberGroup::create([
            'name' => '统计组',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'stats-user',
            'email' => 'stats@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => '测试栏目',
            'slug' => 'test-category',
            'sort_order' => 0,
            'level' => 0,
        ]);

        $contentModel = ContentModel::create([
            'name' => '测试模型',
            'table_name' => 'posts_test',
        ]);

        $post = Post::create([
            'title' => '测试文章',
            'slug' => 'test-post',
            'category_id' => $category->id,
            'model_id' => $contentModel->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        PostStatistic::create([
            'post_id' => $post->id,
            'views' => 0,
            'likes' => 0,
            'comments_count' => 0,
        ]);

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '待审核评论',
            'status' => 'pending',
        ]);

        $this->assertSame(0, $post->statistics()->value('comments_count'));

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '已通过评论',
            'status' => 'approved',
        ]);

        $this->assertSame(1, $post->statistics()->value('comments_count'));
    }

    public function test_post_detail_can_store_attachment_ids_in_custom_fields(): void
    {
        $group = MemberGroup::create([
            'name' => '附件组',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'attachment-user',
            'email' => 'attachment@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => '附件栏目',
            'slug' => 'attachment-category',
            'sort_order' => 0,
            'level' => 0,
        ]);

        $contentModel = ContentModel::create([
            'name' => '附件模型',
            'table_name' => 'posts_attachment',
        ]);

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'filename' => 'demo.jpg',
            'filepath' => 'uploads/demo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        $post = Post::create([
            'title' => '附件文章',
            'slug' => 'attachment-post',
            'category_id' => $category->id,
            'model_id' => $contentModel->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $post->detail()->create([
            'content' => '正文',
            'custom_fields' => [
                'attachment_ids' => [$attachment->id],
            ],
        ]);

        $this->assertSame([$attachment->id], $post->detail->custom_fields['attachment_ids']);
    }
}
