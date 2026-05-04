<?php

namespace Tests\Feature;

use App\Models\MemberGroup;
use App\Models\User;
use App\Filament\Pages\NavigationMenuCenter;
use App\Filament\Pages\SiteBrandCenter;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_access_backend_without_admin_permission(): void
    {
        $memberGroup = MemberGroup::create([
            'name' => '普通会员',
            'min_points' => 0,
            'max_points' => 999,
        ]);

        $staff = User::create([
            'username' => 'staff-user',
            'email' => 'staff@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $memberGroup->id,
            'status' => 'active',
            'is_staff' => true,
        ]);

        $this->assertTrue($staff->is_staff_account);
        $this->assertFalse($staff->is_admin_account);
        $this->assertTrue($staff->hasMemberPermission('member.center'));
        $this->assertFalse($staff->hasMemberPermission('admin.access'));
        $this->assertTrue($staff->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($staff)
            ->get('/admin')
            ->assertOk()
            ->assertSee('资料')
            ->assertDontSee('员工个人资料');

        $this->actingAs($staff)
            ->get('/admin/settings-center')
            ->assertOk()
            ->assertSee('设置中心');

        $this->actingAs($staff)
            ->get(SiteBrandCenter::getUrl())
            ->assertOk()
            ->assertSee('站点品牌');

        $this->actingAs($staff)
            ->get(NavigationMenuCenter::getUrl())
            ->assertOk()
            ->assertSee('导航菜单');

        $this->actingAs($staff)
            ->get('/admin/theme-workbench')
            ->assertOk()
            ->assertSee('主题工作台');

        $this->actingAs($staff)
            ->get('/admin/payment-center')
            ->assertOk()
            ->assertSee('支付中心');

        $this->actingAs($staff)
            ->get('/admin/route-rule-center')
            ->assertOk()
            ->assertSee('路由');
    }

    public function test_staff_can_update_own_staff_profile_modal_form(): void
    {
        $memberGroup = MemberGroup::create([
            'name' => '普通会员',
            'min_points' => 0,
            'max_points' => 999,
        ]);

        $staff = User::create([
            'username' => 'staff-editor',
            'email' => 'editor@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $memberGroup->id,
            'status' => 'active',
            'is_staff' => true,
        ]);

        $this->actingAs($staff)
            ->from('/admin')
            ->put(route('admin.staff-profile.update'), [
                'display_name' => '编辑部同事',
                'email' => 'editor-updated@example.com',
                'nickname' => '编辑小刘',
                'bio' => '负责后台流程、内容节奏和专题发布。',
                'password' => 'new-secret',
                'password_confirmation' => 'new-secret',
            ])
            ->assertRedirect('/admin');

        $staff->refresh();

        $this->assertSame('编辑部同事', $staff->display_name);
        $this->assertSame('editor-updated@example.com', $staff->email);
        $this->assertSame('编辑小刘', $staff->nickname);
        $this->assertSame('负责后台流程、内容节奏和专题发布。', $staff->bio);
        $this->assertTrue(Hash::check('new-secret', $staff->password_hash));
    }

    public function test_normal_member_cannot_update_staff_profile_modal_form(): void
    {
        $memberGroup = MemberGroup::create([
            'name' => '普通会员',
            'min_points' => 0,
            'max_points' => 999,
        ]);

        $member = User::create([
            'username' => 'plain-member',
            'email' => 'plain@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $memberGroup->id,
            'status' => 'active',
            'is_staff' => false,
        ]);

        $this->actingAs($member)
            ->put(route('admin.staff-profile.update'), [
                'display_name' => '不会成功',
                'email' => 'forbidden@example.com',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin')
            ->assertForbidden();
    }
}
