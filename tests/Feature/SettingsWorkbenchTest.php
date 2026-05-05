<?php

namespace Tests\Feature;

use App\Models\MemberGroup;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsWorkbenchTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_see_settings_workbench_summaries(): void
    {
        $group = MemberGroup::create([
            'name' => '编辑组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $staff = User::create([
            'username' => 'settings-staff',
            'email' => 'settings-staff@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
            'is_staff' => true,
        ]);

        $settings = SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $businessSettings['active_theme'] = 'editorial';
        $businessSettings['wechat_enabled'] = true;
        $businessSettings['alipay_enabled'] = false;
        $businessSettings['paypal_enabled'] = true;
        $businessSettings['stripe_enabled'] = false;

        $settings->update([
            'site_name' => '测试站点',
            'site_tagline' => '后台设置工作台',
            'seo_title' => '后台设置 SEO',
            'primary_navigation' => [
                ['label' => '首页', 'url' => '/'],
                ['label' => '栏目', 'url' => '/search'],
            ],
            'footer_navigation' => [
                ['label' => '商店', 'url' => '/shop'],
            ],
            'social_links' => [
                ['label' => 'GitHub', 'url' => 'https://github.com/example/repo'],
            ],
            'business_settings' => $businessSettings,
        ]);

        $this->actingAs($staff)
            ->get('/admin/settings-center')
            ->assertOk()
            ->assertSee('站点品牌')
            ->assertSee('导航菜单')
            ->assertSee('主题')
            ->assertSee('Editorial / 编辑刊物风')
            ->assertSee('支付')
            ->assertSee('微信 / PayPal')
            ->assertSee('路由')
            ->assertSee('上传诊断');
    }
}
