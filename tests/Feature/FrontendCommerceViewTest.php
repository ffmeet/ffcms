<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MemberGroup;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendCommerceViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_prompt_on_available_event_page(): void
    {
        $event = $this->createEvent([
            'slug' => 'guest-login-event',
            'capacity' => 20,
        ]);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('登录后报名')
            ->assertDontSee('当前活动名额已满');
    }

    public function test_event_page_shows_capacity_full_notice_without_registration_button(): void
    {
        $event = $this->createEvent([
            'slug' => 'capacity-full-event',
            'capacity' => 1,
        ]);

        $otherUser = $this->createMemberUser();

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
            'order_id' => null,
            'status' => 'approved',
            'payment_status' => 'not_required',
            'notes' => null,
            'payload' => ['seeded' => true],
        ]);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('当前活动名额已满，暂时不能继续提交报名。')
            ->assertDontSee('登录后报名')
            ->assertDontSee('action="'.route('events.register', $event->slug).'"', false);
    }

    public function test_event_page_shows_group_upgrade_notice_for_ineligible_member(): void
    {
        $basicGroup = MemberGroup::create([
            'name' => '普通会员组',
            'min_points' => 0,
            'max_points' => 999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
                'events.access' => true,
            ],
        ]);

        $premiumGroup = MemberGroup::create([
            'name' => '高级会员组',
            'min_points' => 1000,
            'max_points' => 999999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
                'events.access' => true,
            ],
        ]);

        $user = User::create([
            'username' => 'view-basic-member',
            'email' => 'view-basic-member@example.com',
            'password_hash' => 'secret123',
            'group_id' => $basicGroup->id,
            'status' => 'active',
        ]);

        $event = $this->createEvent([
            'slug' => 'premium-only-event',
            'required_member_group_id' => $premiumGroup->id,
        ]);

        $this->actingAs($user)
            ->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('这场活动仅对 '.$premiumGroup->name.' 开放，当前会员级别还不能报名。')
            ->assertSee('升级会员后报名')
            ->assertDontSee('提交报名');
    }

    public function test_member_can_open_pending_order_payment_page(): void
    {
        $user = $this->createMemberUser();

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'ORD-VIEW-001',
            'order_type' => 'membership',
            'purchasable_type' => null,
            'purchasable_id' => null,
            'title' => '前台支付页测试订单',
            'currency' => 'CNY',
            'amount' => 199,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 199,
            'payload' => ['source' => 'test'],
            'paid_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('member.orders.pay', $order))
            ->assertOk()
            ->assertSee('订单支付')
            ->assertSee('前台支付页测试订单')
            ->assertSee('模拟支付成功')
            ->assertSee('模拟支付失败')
            ->assertSee('关闭支付并返回订单');
    }

    public function test_paid_order_redirects_back_to_orders_index_from_payment_page(): void
    {
        $user = $this->createMemberUser();

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'ORD-VIEW-002',
            'order_type' => 'product',
            'purchasable_type' => null,
            'purchasable_id' => null,
            'title' => '已支付订单',
            'currency' => 'CNY',
            'amount' => 99,
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => ['source' => 'test'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'paid',
            'amount' => 99,
            'payload' => ['source' => 'test'],
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('member.orders.pay', $order))
            ->assertRedirect(route('member.orders.index'));
    }

    public function test_xiaofang_theme_event_page_keeps_capacity_notice_consistent(): void
    {
        $this->activateTheme('xiaofang');

        $event = $this->createEvent([
            'slug' => 'xiaofang-capacity-full-event',
            'capacity' => 1,
        ]);

        $otherUser = $this->createMemberUser();

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
            'order_id' => null,
            'status' => 'approved',
            'payment_status' => 'not_required',
            'notes' => null,
            'payload' => ['seeded' => true],
        ]);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('当前活动名额已满，暂时不能继续提交报名。')
            ->assertSee('Event Info')
            ->assertDontSee('action="'.route('events.register', $event->slug).'"', false);
    }

    public function test_xiaofang_theme_member_order_payment_page_uses_xiaofang_shell(): void
    {
        $this->activateTheme('xiaofang');

        $user = $this->createMemberUser();

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'ORD-XF-001',
            'order_type' => 'event',
            'purchasable_type' => null,
            'purchasable_id' => null,
            'title' => '小芳主题支付订单',
            'currency' => 'CNY',
            'amount' => 299,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 299,
            'payload' => ['source' => 'test'],
            'paid_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('member.orders.pay', $order))
            ->assertOk()
            ->assertSee('订单支付')
            ->assertSee('小芳主题支付订单')
            ->assertSee('bg-white', false)
            ->assertSee('模拟支付成功');
    }

    public function test_xiaofang_theme_member_activity_pages_use_xiaofang_shell(): void
    {
        $this->activateTheme('xiaofang');

        $user = $this->createMemberUser();

        $this->actingAs($user)
            ->get(route('member.activity.center'))
            ->assertOk()
            ->assertSee('活动中心')
            ->assertSee('会员中心')
            ->assertSee('bg-[#111111]', false);

        $this->actingAs($user)
            ->get(route('member.activities.index'))
            ->assertOk()
            ->assertSee('我的活动')
            ->assertSee('会员中心')
            ->assertSee('bg-[#111111]', false);
    }

    public function test_xiaofang_theme_member_orders_and_subscriptions_pages_use_xiaofang_shell(): void
    {
        $this->activateTheme('xiaofang');

        $user = $this->createMemberUser();

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'ORD-XF-LIST-001',
            'order_type' => 'membership',
            'purchasable_type' => null,
            'purchasable_id' => null,
            'title' => '小芳主题订单列表',
            'currency' => 'CNY',
            'amount' => 129,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 129,
            'payload' => ['source' => 'test'],
            'paid_at' => null,
        ]);

        $plan = MembershipPlan::create([
            'name' => '小芳会员计划',
            'slug' => 'xiaofang-membership',
            'description' => '主题会员测试计划',
            'price' => 129,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['theme-preview'],
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'last_order_id' => $order->id,
            'status' => 'pending',
            'auto_renew' => false,
            'started_at' => null,
            'expires_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        $this->actingAs($user)
            ->get(route('member.orders.index'))
            ->assertOk()
            ->assertSee('我的订单')
            ->assertSee('会员中心')
            ->assertDontSee('ecms-settings-overview', false)
            ->assertSee('Orders');

        $this->actingAs($user)
            ->get(route('member.subscriptions.index'))
            ->assertOk()
            ->assertSee('我的订阅')
            ->assertSee('会员中心')
            ->assertDontSee('ecms-settings-overview', false)
            ->assertSee('Subscriptions');
    }

    public function test_member_dashboard_shows_operational_attention_cards(): void
    {
        $group = MemberGroup::create([
            'name' => '运营同学',
            'min_points' => 0,
            'max_points' => 9999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
                'shop.access' => true,
                'events.access' => true,
            ],
        ]);

        $user = User::create([
            'username' => 'ops-member',
            'email' => 'ops-member@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
            'is_staff' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'OPS-ORDER-001',
            'order_type' => 'membership',
            'purchasable_type' => null,
            'purchasable_id' => null,
            'title' => '待支付运营订单',
            'currency' => 'CNY',
            'amount' => 199,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 199,
            'payload' => ['source' => 'test'],
            'paid_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertSee('还有待支付订单')
            ->assertSee('后台快捷处理')
            ->assertSee('支付中心')
            ->assertSee('支付记录');
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => '前台活动页测试',
            'slug' => 'frontend-event',
            'status' => 'registration-open',
            'location' => '上海',
            'is_paid' => false,
            'price' => 0,
            'required_member_group_id' => null,
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(2),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(5),
            'capacity' => 20,
            'summary' => '活动摘要',
            'content' => '活动正文',
            'payload' => ['demo' => true],
        ], $overrides));
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

    private function createMemberUser(): User
    {
        $group = MemberGroup::create([
            'name' => '前台视图会员-'.str()->random(6),
            'min_points' => 0,
            'max_points' => 9999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
                'shop.access' => true,
                'events.access' => true,
            ],
        ]);

        return User::create([
            'username' => 'frontend-member-'.str()->random(6),
            'email' => str()->random(8).'@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);
    }
}
