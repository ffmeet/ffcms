<?php

namespace Tests\Feature;

use App\Models\MemberGroup;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\MemberResetPasswordNotification;
use App\Support\SiteTheme;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class FrontendAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_themed_login_and_register_pages(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('登录站点')
            ->assertSee('登录并进入会员中心')
            ->assertSee('忘记密码？');

        $this->get('/register')
            ->assertOk()
            ->assertSee('创建会员账号')
            ->assertSee('注册并进入会员中心');
    }

    public function test_guest_can_request_password_reset_link(): void
    {
        Notification::fake();

        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'forgot-member',
            'email' => 'forgot-member@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHas('status');

        Notification::assertSentTo($user, MemberResetPasswordNotification::class);
    }

    public function test_guest_can_reset_password_with_valid_token(): void
    {
        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'reset-member',
            'email' => 'reset-member@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'renewed123',
            'password_confirmation' => 'renewed123',
        ])->assertRedirect('/login');

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'renewed123',
        ])->assertRedirect('/member');
    }

    public function test_frontend_user_can_register_and_enter_member_dashboard(): void
    {
        $response = $this->post('/register', [
            'username' => 'frontend-user',
            'email' => 'frontend@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/member');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'frontend-user',
            'email' => 'frontend@example.com',
            'status' => 'active',
        ]);
    }

    public function test_frontend_user_can_login_with_username(): void
    {
        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        User::create([
            'username' => 'memberlogin',
            'email' => 'memberlogin@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'memberlogin',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/member');
        $this->assertAuthenticated();
    }

    public function test_guest_is_redirected_from_member_dashboard(): void
    {
        $this->get('/member')
            ->assertRedirect('/login');
    }

    public function test_frontend_user_can_view_member_dashboard_page(): void
    {
        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'member-dashboard',
            'email' => 'member-dashboard@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/member')
            ->assertOk()
            ->assertSee('会员中心');
    }

    public function test_logout_redirects_back_to_home_page(): void
    {
        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'logout-member',
            'email' => 'logout-member@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');
    }

    public function test_seeded_admin_can_login_and_open_member_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => '789789',
        ]);

        $response->assertRedirect('/member');

        $this->get('/member')
            ->assertOk()
            ->assertSee('会员中心');
    }

    public function test_member_group_dotted_permissions_are_honored(): void
    {
        $group = MemberGroup::create([
            'name' => '管理员',
            'min_points' => 0,
            'max_points' => 999999,
            'permissions' => [
                'member.center' => true,
                'admin.access' => true,
            ],
        ]);

        $this->assertTrue($group->hasPermission('member.center'));
        $this->assertTrue($group->hasPermission('admin.access'));
    }

    public function test_admin_can_enter_theme_preview_mode(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/preview/theme/xiaofang')
            ->assertRedirect('/');

        $this->assertSame('xiaofang', session(SiteTheme::PREVIEW_SESSION_KEY));

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('当前正在预览主题')
            ->assertSee('当前正在预览主题');
    }

    public function test_non_admin_cannot_enter_theme_preview_mode(): void
    {
        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'member-preview',
            'email' => 'member-preview@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/preview/theme/xiaofang')
            ->assertRedirect('/');
    }

    public function test_xiaofang_theme_member_dashboard_uses_xiaofang_shell(): void
    {
        $this->activateTheme('xiaofang');

        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'xiaofang-dashboard',
            'email' => 'xiaofang-dashboard@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/member')
            ->assertOk()
            ->assertSee('会员中心')
            ->assertSee('bg-[#111111]', false);
    }

    public function test_xiaofang_theme_member_profile_page_uses_xiaofang_shell(): void
    {
        $this->activateTheme('xiaofang');

        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'xiaofang-profile',
            'email' => 'xiaofang-profile@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('member.profile.edit'))
            ->assertOk()
            ->assertSee('修改资料')
            ->assertSee('会员中心')
            ->assertSee('bg-[#111111]', false);
    }

    public function test_xiaofang_theme_member_post_and_comment_pages_use_xiaofang_shell(): void
    {
        $this->activateTheme('xiaofang');

        $group = MemberGroup::create([
            'name' => '默认会员',
            'min_points' => 0,
            'max_points' => 100,
        ]);

        $user = User::create([
            'username' => 'xiaofang-member-pages',
            'email' => 'xiaofang-member-pages@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('member.posts.index'))
            ->assertOk()
            ->assertSee('我的稿件')
            ->assertSee('会员中心')
            ->assertSee('bg-[#111111]', false);

        $this->actingAs($user)
            ->get(route('member.comments.index'))
            ->assertOk()
            ->assertSee('我的评论')
            ->assertSee('会员中心')
            ->assertSee('bg-[#111111]', false);
    }

    private function activateTheme(string $theme): void
    {
        $settings = SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $businessSettings['active_theme'] = $theme;

        $settings->update([
            'business_settings' => $businessSettings,
        ]);
    }
}
