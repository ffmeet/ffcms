<?php

namespace Tests\Feature;

use App\Models\MemberGroup;
use App\Models\Attachment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalize_publishing_data_generates_slug_and_publish_time(): void
    {
        $data = Post::normalizePublishingData([
            'title' => 'Empire CMS Publishing Workflow',
            'slug' => '',
            'status' => 'published',
            'published_at' => null,
            'content' => '<p>Publishing summary content for frontend rendering.</p>',
            'seo_title' => '',
            'summary' => '',
        ]);

        $this->assertSame('empire-cms-publishing-workflow', $data['slug']);
        $this->assertSame('Empire CMS Publishing Workflow', $data['seo_title']);
        $this->assertSame('Publishing summary content for frontend rendering.', $data['summary']);
        $this->assertNotNull($data['published_at']);
    }

    public function test_normalize_publishing_data_clears_publish_time_for_non_published_states(): void
    {
        $data = Post::normalizePublishingData([
            'title' => '待审核文章',
            'slug' => 'pending-post',
            'status' => 'pending',
            'published_at' => now(),
        ]);

        $this->assertSame('pending-post', $data['slug']);
        $this->assertNull($data['published_at']);
    }

    public function test_generate_unique_slug_appends_suffix_for_duplicate_posts(): void
    {
        $group = MemberGroup::create([
            'name' => '内容组',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'slugger',
            'email' => 'slugger@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        Post::create([
            'title' => '第一篇文章',
            'slug' => 'duplicate-post',
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $data = Post::normalizePublishingDataForRecord([
            'title' => 'Duplicate Post',
            'slug' => 'duplicate-post',
            'status' => 'draft',
        ]);

        $this->assertSame('duplicate-post-2', $data['slug']);
    }

    public function test_attachment_model_exposes_public_url_and_readable_size(): void
    {
        $group = MemberGroup::create([
            'name' => '上传组',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'uploader',
            'email' => 'uploader@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $attachment = $user->attachments()->create([
            'filename' => 'demo.pdf',
            'filepath' => 'attachments/demo.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
        ]);

        $this->assertStringContainsString('/storage/attachments/demo.pdf', $attachment->url);
        $this->assertSame('2.0 KB', $attachment->readable_size);
        $this->assertFalse($attachment->is_image);
    }

    public function test_attachment_model_detects_images(): void
    {
        $group = MemberGroup::create([
            'name' => '图片组',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'image-user',
            'email' => 'image@example.com',
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'filename' => 'cover.jpg',
            'filepath' => 'attachments/cover.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        $this->assertTrue($attachment->is_image);
    }
}
