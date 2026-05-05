<?php

namespace Tests\Feature;

use App\Filament\Pages\RouteRuleCenter;
use App\Models\MemberGroup;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RouteRuleCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_route_rule_center(): void
    {
        $group = MemberGroup::create([
            'name' => '编辑组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $staff = User::create([
            'username' => 'route-rule-staff',
            'email' => 'route-rule-staff@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
            'is_staff' => true,
        ]);

        $this->actingAs($staff)
            ->get(RouteRuleCenter::getUrl())
            ->assertOk()
            ->assertSee('路由')
            ->assertSee('公开入口')
            ->assertSee('会员中心入口')
            ->assertSee('/search')
            ->assertSee('/member');
    }

    public function test_route_rule_labels_are_used_in_frontend_and_member_center(): void
    {
        $group = MemberGroup::create([
            'name' => '会员组',
            'min_points' => 0,
            'max_points' => 999999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
            ],
        ]);

        $user = User::create([
            'username' => 'route-member',
            'email' => 'route-member@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $settings = SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $businessSettings['route_settings']['public_entries']['pricing']['label'] = '加入会员';
        $businessSettings['route_settings']['public_entries']['login']['label'] = '立即登录';
        $businessSettings['route_settings']['member_entries']['dashboard']['label'] = '控制台';
        $businessSettings['route_settings']['member_entries']['create_post']['label'] = '立即投稿';

        $settings->update([
            'business_settings' => $businessSettings,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('加入会员')
            ->assertSee('立即登录');

        $this->actingAs($user)
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertSee('控制台')
            ->assertSee('立即投稿');
    }
}
