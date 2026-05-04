<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_upload_avatar_and_generate_three_sizes(): void
    {
        Storage::fake('public');

        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->put(route('member.profile.update'), [
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.png', 720, 720),
            ])
            ->assertRedirect(route('member.profile.edit'));

        $user->refresh();

        $this->assertNotNull($user->avatar_original_path);
        $this->assertNotNull($user->avatar_large_path);
        $this->assertNotNull($user->avatar_medium_path);
        $this->assertNotNull($user->avatar_small_path);

        Storage::disk('public')->assertExists($user->avatar_original_path);
        Storage::disk('public')->assertExists($user->avatar_large_path);
        Storage::disk('public')->assertExists($user->avatar_medium_path);
        Storage::disk('public')->assertExists($user->avatar_small_path);
    }

    public function test_member_profile_page_uses_medium_and_small_avatar_urls(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithAvatar();

        $response = $this->actingAs($user)->get(route('member.profile.edit'));

        $response->assertOk();
        $response->assertSee($user->avatarUrl('medium'));
        $response->assertSee($user->avatarUrl('small'));
    }

    public function test_member_can_change_email_after_confirming_current_password(): void
    {
        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->put(route('member.profile.update'), [
                'username' => $user->username,
                'email' => 'updated-member@example.com',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('member.profile.edit'));

        $this->assertSame('updated-member@example.com', $user->fresh()->email);
    }

    public function test_member_cannot_change_email_without_current_password(): void
    {
        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->from(route('member.profile.edit'))
            ->put(route('member.profile.update'), [
                'username' => $user->username,
                'email' => 'updated-member@example.com',
                'current_password' => '',
            ])
            ->assertRedirect(route('member.profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertSame('avatar-member@example.com', $user->fresh()->email);
    }

    public function test_member_can_generate_public_nickname_from_name_order(): void
    {
        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->put(route('member.profile.update'), [
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => 'Hua',
                'last_name' => 'Liu',
                'nickname_strategy' => 'last_first',
                'nickname' => '',
            ])
            ->assertRedirect(route('member.profile.edit'));

        $user->refresh();

        $this->assertSame('Liu Hua', $user->nickname);
        $this->assertSame('Liu Hua', $user->public_display_name);
    }

    public function test_member_can_choose_username_as_public_nickname(): void
    {
        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->put(route('member.profile.update'), [
                'username' => $user->username,
                'email' => $user->email,
                'nickname_strategy' => 'username',
                'nickname' => $user->username,
            ])
            ->assertRedirect(route('member.profile.edit'));

        $user->refresh();

        $this->assertSame('avatar-member', $user->nickname);
        $this->assertSame('avatar-member', $user->public_display_name);
    }

    public function test_member_can_update_public_bio_for_frontend_cards(): void
    {
        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->put(route('member.profile.update'), [
                'username' => $user->username,
                'email' => $user->email,
                'bio' => '长期关注设计写作、城市更新和人物报道。',
            ])
            ->assertRedirect(route('member.profile.edit'));

        $user->refresh();

        $this->assertSame('长期关注设计写作、城市更新和人物报道。', $user->bio);
        $this->assertSame('长期关注设计写作、城市更新和人物报道。', $user->author_bio);
    }

    public function test_post_comments_use_small_avatar_url(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithAvatar();
        [$category, $model] = $this->createContentBase();

        $post = Post::create([
            'title' => '头像评论文章',
            'slug' => 'avatar-comment-post',
            'category_id' => $category->id,
            'model_id' => $model->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $post->detail()->create([
            'content' => '<p>这里是正文。</p>',
            'custom_fields' => [
                'summary' => '评论头像测试摘要',
            ],
        ]);

        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => '这是一条带头像的评论',
            'status' => 'approved',
        ]);

        $this->get("/posts/{$post->slug}")
            ->assertOk()
            ->assertSee($user->avatarUrl('small'))
            ->assertSee('这是一条带头像的评论');
    }

    private function createMemberUser(): User
    {
        $group = MemberGroup::create([
            'name' => '头像会员',
            'min_points' => 0,
            'max_points' => 999,
        ]);

        return User::create([
            'username' => 'avatar-member',
            'email' => 'avatar-member@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
        ]);
    }

    private function createUserWithAvatar(): User
    {
        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->put(route('member.profile.update'), [
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.png', 720, 720),
            ]);

        $user->refresh();

        return $user;
    }

    private function createContentBase(): array
    {
        $category = Category::create([
            'name' => '头像栏目',
            'slug' => 'avatar-category',
        ]);

        $model = ContentModel::create([
            'name' => '头像模型',
            'table_name' => 'posts_avatar',
        ]);

        return [$category, $model];
    }
}
