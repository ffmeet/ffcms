<?php

namespace Tests\Feature;

use App\Models\MemberGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAuthTest extends TestCase
{
    use RefreshDatabase;

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
}
