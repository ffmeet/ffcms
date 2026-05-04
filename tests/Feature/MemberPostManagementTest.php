<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\PostDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        $attachment = $this->createImageAttachment($user, 'new-cover.png');

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
            'custom_fields' => ['summary' => '旧摘要', 'cover_image_url' => 'https://cdn.example.com/old-cover.jpg'],
        ]);

        $this->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '新标题',
                'summary' => '新摘要',
                'content' => '更新后的正文',
                'category_id' => $category->id,
                'model_id' => $model->id,
                'tags' => '更新, 稿件',
                'cover_attachment_id' => $attachment->id,
                'status' => 'pending',
            ])
            ->assertRedirect(route('member.posts.index'));

        $post->refresh();

        $this->assertSame('新标题', $post->title);
        $this->assertSame('pending', $post->status);
        $this->assertSame('更新后的正文', $post->detail?->content);
        $this->assertSame('新摘要', $post->summary);
        $this->assertNotNull($post->cover_image_url);
        $this->assertSame($attachment->id, $post->cover_attachment_id);
        $this->assertNotNull($attachment->fresh()->media_file_id);
        $this->assertTrue($post->coverMediaFiles()->exists());
    }

    public function test_member_can_update_post_without_passing_model_id_when_category_is_bound(): void
    {
        [$user, $category, $model] = $this->createBaseData();
        $attachment = $this->createImageAttachment($user, 'bound-cover.png');

        $post = Post::create([
            'title' => '旧稿件',
            'slug' => 'old-member-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要', 'cover_image_url' => 'https://cdn.example.com/old-bound-cover.jpg'],
        ]);

        $this->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '栏目绑定模型更新',
                'summary' => '沿用栏目自动绑定模型',
                'content' => '新的正文内容',
                'category_id' => $category->id,
                'tags' => '会员, 更新',
                'cover_attachment_id' => $attachment->id,
                'status' => 'draft',
            ])
            ->assertRedirect(route('member.posts.index'));

        $post->refresh();

        $this->assertSame($model->id, $post->model_id);
        $this->assertSame('栏目绑定模型更新', $post->title);
        $this->assertSame('draft', $post->status);
        $this->assertSame('沿用栏目自动绑定模型', $post->summary);
        $this->assertNotNull($post->cover_image_url);
        $this->assertSame($attachment->id, $post->cover_attachment_id);
        $this->assertNotNull($attachment->fresh()->media_file_id);
        $this->assertTrue($post->coverMediaFiles()->exists());
    }

    public function test_edit_form_only_lists_current_members_own_cover_attachments(): void
    {
        [$user, $category, $model] = $this->createBaseData();
        $otherUser = User::factory()->create();

        $post = Post::create([
            'title' => '编辑选图稿件',
            'slug' => 'edit-cover-library-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $ownAttachment = $this->createImageAttachment($user, 'my-edit-cover.png');
        $otherAttachment = $this->createImageAttachment($otherUser, 'others-edit-cover.png');

        $response = $this->actingAs($user)->get(route('member.posts.edit', $post));

        $response->assertOk();
        $response->assertSee($ownAttachment->filename);
        $response->assertDontSee($otherAttachment->filename);
    }

    public function test_edit_form_shows_current_cover_preview(): void
    {
        [$user, $category, $model] = $this->createBaseData();

        $post = Post::create([
            'title' => '预览当前封面',
            'slug' => 'preview-current-cover',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要', 'cover_image_url' => 'https://cdn.example.com/current-preview-cover.jpg'],
        ]);

        $response = $this->actingAs($user)->get(route('member.posts.edit', $post));

        $response->assertOk();
        $response->assertSee('当前封面预览');
        $response->assertSee('https://cdn.example.com/current-preview-cover.jpg');
        $response->assertDontSee('封面图 URL');
        $response->assertSee('从媒体库选择');
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

    public function test_member_can_replace_cover_image_by_uploading_new_file_on_edit(): void
    {
        Storage::fake('public');

        [$user, $category, $model] = $this->createBaseData();

        $post = Post::create([
            'title' => '替换封面稿件',
            'slug' => 'replace-cover-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要', 'cover_image_url' => 'https://cdn.example.com/old-cover.jpg'],
        ]);

        $this->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '替换封面稿件',
                'summary' => '新摘要',
                'content' => '新的正文',
                'category_id' => $category->id,
                'tags' => '替换, 封面',
                'cover_upload' => UploadedFile::fake()->image('replacement-cover.png', 1280, 720),
                'status' => 'draft',
            ])
            ->assertRedirect(route('member.posts.index'));

        $post->refresh();

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

    public function test_member_can_switch_cover_to_own_existing_attachment_on_edit(): void
    {
        [$user, $category, $model] = $this->createBaseData();

        $post = Post::create([
            'title' => '切换已有封面',
            'slug' => 'switch-existing-cover',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要', 'cover_image_url' => 'https://cdn.example.com/old-cover.jpg'],
        ]);

        $attachment = $this->createImageAttachment($user, 'existing-cover.png');

        $this->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '切换已有封面',
                'summary' => '新摘要',
                'content' => '新的正文',
                'category_id' => $category->id,
                'cover_attachment_id' => $attachment->id,
                'status' => 'draft',
            ])
            ->assertRedirect(route('member.posts.index'));

        $post->refresh()->load('detail');

        $this->assertNotNull($post->cover_image_url);
        $this->assertSame($attachment->id, $post->cover_attachment_id);
        $this->assertNotNull($attachment->fresh()->media_file_id);
        $this->assertTrue($post->coverMediaFiles()->exists());
    }

    public function test_member_cannot_switch_cover_to_other_users_attachment_on_edit(): void
    {
        [$user, $category, $model] = $this->createBaseData();
        $otherUser = User::factory()->create();

        $post = Post::create([
            'title' => '越权切换封面',
            'slug' => 'forbidden-cover-switch',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要', 'cover_image_url' => 'https://cdn.example.com/old-cover.jpg'],
        ]);

        $otherAttachment = $this->createImageAttachment($otherUser, 'forbidden-cover.png');

        $response = $this->from(route('member.posts.edit', $post))
            ->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '越权切换封面',
                'summary' => '新摘要',
                'content' => '新的正文',
                'category_id' => $category->id,
                'cover_attachment_id' => $otherAttachment->id,
                'status' => 'draft',
            ]);

        $response->assertRedirect(route('member.posts.edit', $post));
        $response->assertSessionHasErrors(['cover_attachment_id']);

        $post->refresh()->load('detail');

        $this->assertSame('https://cdn.example.com/old-cover.jpg', $post->cover_image_url);
        $this->assertNull($post->cover_attachment_id);
    }

    public function test_member_can_update_post_body_attachments_on_edit(): void
    {
        [$user, $category, $model] = $this->createBaseData();

        $post = Post::create([
            'title' => '更新正文附件',
            'slug' => 'update-body-attachments',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要', 'attachment_ids' => []],
        ]);

        $attachment = $this->createImageAttachment($user, 'member-manual.pdf');
        $attachment->update(['mime_type' => 'application/pdf']);

        $this->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '更新正文附件',
                'summary' => '新摘要',
                'content' => '新的正文',
                'category_id' => $category->id,
                'attachment_ids' => [$attachment->id],
                'status' => 'draft',
            ])
            ->assertRedirect(route('member.posts.index'));

        $post->refresh()->load('detail');

        $this->assertSame([$attachment->id], $post->attachment_ids);
    }

    public function test_member_cannot_update_post_with_other_users_body_attachments(): void
    {
        [$user, $category, $model] = $this->createBaseData();
        $otherUser = User::factory()->create();

        $post = Post::create([
            'title' => '越权更新正文附件',
            'slug' => 'forbidden-body-attachments',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        PostDetail::create([
            'post_id' => $post->id,
            'content' => '旧正文',
            'custom_fields' => ['summary' => '旧摘要', 'attachment_ids' => []],
        ]);

        $otherAttachment = $this->createImageAttachment($otherUser, 'other-private-manual.pdf');
        $otherAttachment->update(['mime_type' => 'application/pdf']);

        $response = $this->from(route('member.posts.edit', $post))
            ->actingAs($user)
            ->put(route('member.posts.update', $post), [
                'title' => '越权更新正文附件',
                'summary' => '新摘要',
                'content' => '新的正文',
                'category_id' => $category->id,
                'attachment_ids' => [$otherAttachment->id],
                'status' => 'draft',
            ]);

        $response->assertRedirect(route('member.posts.edit', $post));
        $response->assertSessionHasErrors(['attachment_ids']);

        $post->refresh()->load('detail');
        $this->assertSame([], $post->attachment_ids);
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
            'model_id' => null,
        ]);

        $model = ContentModel::create([
            'name' => '新闻文章',
            'table_name' => 'posts_news',
        ]);

        $category->update([
            'model_id' => $model->id,
        ]);

        return [$user, $category, $model];
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
