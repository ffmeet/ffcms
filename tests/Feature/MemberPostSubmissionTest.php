<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberPostSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_view_submission_form(): void
    {
        $user = $this->createMember();
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);

        $response = $this->actingAs($user)->get(route('member.posts.create'));

        $response->assertOk();
        $response->assertSee('发布新稿件');
    }

    public function test_submission_form_only_lists_current_members_own_cover_attachments(): void
    {
        $user = $this->createMember('owner');
        $otherUser = $this->createMember('other');
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);

        $ownAttachment = $this->createImageAttachment($user, 'my-cover.png');
        $otherAttachment = $this->createImageAttachment($otherUser, 'others-cover.png');

        $response = $this->actingAs($user)->get(route('member.posts.create'));

        $response->assertOk();
        $response->assertSee($ownAttachment->filename);
        $response->assertDontSee($otherAttachment->filename);
    }

    public function test_submission_form_shows_cover_preview_panel(): void
    {
        $user = $this->createMember('preview');
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);

        $response = $this->actingAs($user)->get(route('member.posts.create'));

        $response->assertOk();
        $response->assertSee('当前封面预览');
        $response->assertSee('当前还没有选定封面');
        $response->assertDontSee('封面图 URL');
        $response->assertSee('从媒体库选择');
    }

    public function test_authenticated_member_can_submit_pending_post_using_category_bound_model(): void
    {
        $user = $this->createMember();
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        $category = Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);

        $response = $this->actingAs($user)->post(route('member.posts.store'), [
            'title' => '会员投稿测试',
            'summary' => '一条从会员中心发出的测试稿件',
            'content' => '这里是会员投稿正文。',
            'category_id' => $category->id,
            'tags' => '会员投稿, 测试',
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        $post = Post::query()->first();

        $this->assertNotNull($post);
        $this->assertSame($user->id, $post->user_id);
        $this->assertSame('pending', $post->status);
        $this->assertSame($model->id, $post->model_id);
        $this->assertCount(2, $post->tags);
        $this->assertSame('一条从会员中心发出的测试稿件', $post->summary);
        $this->assertNull($post->cover_image_url);
    }

    public function test_authenticated_member_can_submit_flash_post_without_content(): void
    {
        $user = $this->createMember();
        $model = ContentModel::create(['name' => '快讯模型', 'table_name' => 'posts_flash']);
        $category = Category::create(['name' => '快讯栏目', 'slug' => 'flash-news', 'model_id' => $model->id]);

        $response = $this->actingAs($user)->post(route('member.posts.store'), [
            'title' => '快讯投稿测试',
            'summary' => '这是一条快讯摘要',
            'content' => '',
            'category_id' => $category->id,
            'tags' => '快讯, 会员投稿',
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        $post = Post::query()->first();

        $this->assertNotNull($post);
        $this->assertSame($model->id, $post->model_id);
        $this->assertSame('pending', $post->status);
        $this->assertStringStartsWith('flash-news-', $post->slug);
        $this->assertSame('这是一条快讯摘要', $post->summary);
        $this->assertSame('', (string) $post->content);
        $this->assertNull($post->cover_image_url);
    }

    public function test_authenticated_member_can_upload_cover_image_during_submission(): void
    {
        Storage::fake('public');

        $user = $this->createMember();
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        $category = Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);

        $response = $this->actingAs($user)->post(route('member.posts.store'), [
            'title' => '上传封面稿件',
            'summary' => '带上传封面的稿件',
            'content' => '这里是正文。',
            'category_id' => $category->id,
            'tags' => '上传, 封面',
            'cover_upload' => UploadedFile::fake()->image('member-cover.png', 1200, 800),
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        $post = Post::query()->firstOrFail();

        $this->assertNotNull($post->cover_image_url);
        $this->assertTrue($post->coverMediaFiles()->exists());
        $this->assertDatabaseHas('attachments', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('media_files', [
            'uploaded_by_user_id' => $user->id,
            'mime_type' => 'image/png',
        ]);
    }

    public function test_authenticated_member_can_select_own_attachment_as_cover_during_submission(): void
    {
        $user = $this->createMember('picker');
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        $category = Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);
        $attachment = $this->createImageAttachment($user, 'selected-cover.png');

        $response = $this->actingAs($user)->post(route('member.posts.store'), [
            'title' => '从媒体库选封面',
            'summary' => '选择自己的媒体库图片',
            'content' => '这里是正文。',
            'category_id' => $category->id,
            'cover_attachment_id' => $attachment->id,
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        $post = Post::query()->firstOrFail();

        $this->assertNotNull($post->cover_image_url);
        $this->assertSame($attachment->id, $post->cover_attachment_id);
        $this->assertNotNull($attachment->fresh()->media_file_id);
        $this->assertTrue($post->coverMediaFiles()->exists());
    }

    public function test_authenticated_member_cannot_select_other_users_attachment_as_cover_during_submission(): void
    {
        $user = $this->createMember('allowed');
        $otherUser = $this->createMember('blocked');
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        $category = Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);
        $otherAttachment = $this->createImageAttachment($otherUser, 'others-private-cover.png');

        $response = $this->from(route('member.posts.create'))
            ->actingAs($user)
            ->post(route('member.posts.store'), [
                'title' => '越权选图测试',
                'summary' => '不应该成功',
                'content' => '这里是正文。',
                'category_id' => $category->id,
                'cover_attachment_id' => $otherAttachment->id,
                'status' => 'pending',
            ]);

        $response->assertRedirect(route('member.posts.create'));
        $response->assertSessionHasErrors(['cover_attachment_id']);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_authenticated_member_can_select_own_attachments_for_post_body_during_submission(): void
    {
        $user = $this->createMember('attachments');
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        $category = Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);
        $attachment = $this->createImageAttachment($user, 'member-appendix.pdf');
        $attachment->update(['mime_type' => 'application/pdf']);

        $response = $this->actingAs($user)->post(route('member.posts.store'), [
            'title' => '带正文附件的稿件',
            'summary' => '选择自己的附件',
            'content' => '这里是正文。',
            'category_id' => $category->id,
            'attachment_ids' => [$attachment->id],
            'status' => 'pending',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        $post = Post::query()->firstOrFail();

        $this->assertSame([$attachment->id], $post->attachment_ids);
    }

    public function test_authenticated_member_cannot_select_other_users_attachments_for_post_body_during_submission(): void
    {
        $user = $this->createMember('body-own');
        $otherUser = $this->createMember('body-other');
        $model = ContentModel::create(['name' => '新闻文章', 'table_name' => 'posts_news']);
        $category = Category::create(['name' => '新闻中心', 'slug' => 'news', 'model_id' => $model->id]);
        $otherAttachment = $this->createImageAttachment($otherUser, 'others-private-file.pdf');
        $otherAttachment->update(['mime_type' => 'application/pdf']);

        $response = $this->from(route('member.posts.create'))
            ->actingAs($user)
            ->post(route('member.posts.store'), [
                'title' => '越权选正文附件',
                'summary' => '不应该成功',
                'content' => '这里是正文。',
                'category_id' => $category->id,
                'attachment_ids' => [$otherAttachment->id],
                'status' => 'pending',
            ]);

        $response->assertRedirect(route('member.posts.create'));
        $response->assertSessionHasErrors(['attachment_ids']);
        $this->assertDatabaseCount('posts', 0);
    }

    private function createMember(?string $suffix = null): User
    {
        $suffix ??= (string) str()->lower(str()->random(6));

        $group = MemberGroup::create([
            'name' => '普通会员-'.$suffix,
            'min_points' => 0,
            'max_points' => 999,
        ]);

        return User::create([
            'username' => 'member-'.$suffix,
            'email' => "member-{$suffix}@example.com",
            'password_hash' => 'password',
            'group_id' => $group->id,
            'status' => 'active',
        ]);
    }

    private function createImageAttachment(User $user, string $filename): Attachment
    {
        Storage::disk('public')->put(
            'attachments/member-post-covers/'.$filename,
            File::get(base_path('storage/app/public/3/avatar-admin.png'))
        );

        return Attachment::create([
            'user_id' => $user->id,
            'filename' => $filename,
            'filepath' => 'attachments/member-post-covers/'.$filename,
            'mime_type' => 'image/png',
            'size' => 1024,
        ]);
    }
}
