<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MemberGroup;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_purchase_free_product_and_get_paid_order(): void
    {
        $user = $this->createMemberUser();

        $product = Product::create([
            'title' => '零元商品',
            'slug' => 'free-product',
            'status' => 'published',
            'delivery_type' => 'download',
            'currency' => 'CNY',
            'price' => 0,
            'stock' => 10,
            'summary' => '免费商品',
            'content' => '免费商品内容',
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->post("/shop/{$product->slug}/purchase")
            ->assertRedirect(route('member.orders.index'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'order_type' => 'product',
            'purchasable_type' => Product::class,
            'purchasable_id' => $product->id,
            'status' => 'paid',
            'title' => '零元商品',
        ]);
    }

    public function test_member_can_complete_paid_product_order_and_stock_is_decremented(): void
    {
        $user = $this->createMemberUser();

        $product = Product::create([
            'title' => '付费商品',
            'slug' => 'paid-product',
            'status' => 'published',
            'delivery_type' => 'download',
            'currency' => 'CNY',
            'price' => 99,
            'stock' => 5,
            'summary' => '付费商品',
            'content' => '付费商品内容',
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->post("/shop/{$product->slug}/purchase")
            ->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_type', 'product')
            ->where('purchasable_id', $product->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->post(route('member.orders.simulate-payment', $order), [
                'provider' => 'manual',
                'action' => 'paid',
            ])
            ->assertRedirect(route('member.orders.index'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'paid',
            'provider' => 'manual',
        ]);

        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_ready_online_provider_is_used_when_creating_pending_payment(): void
    {
        $user = $this->createMemberUser();

        $settings = \App\Models\SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $businessSettings['wechat_app_id'] = 'wx-ready-app';
        $businessSettings['wechat_mch_id'] = '1900000109';
        $businessSettings['wechat_api_v3_key'] = 'wechat-key';
        $businessSettings['alipay_enabled'] = false;
        $businessSettings['paypal_enabled'] = false;
        $businessSettings['stripe_enabled'] = false;

        $settings->update([
            'business_settings' => $businessSettings,
        ]);

        $product = Product::create([
            'title' => '渠道校验商品',
            'slug' => 'provider-check-product',
            'status' => 'published',
            'delivery_type' => 'download',
            'currency' => 'CNY',
            'price' => 39,
            'stock' => 10,
            'summary' => '渠道校验商品',
            'content' => '渠道校验商品内容',
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->post("/shop/{$product->slug}/purchase")
            ->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('purchasable_id', $product->id)
            ->firstOrFail();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => 'wechat',
            'status' => 'pending',
        ]);
    }

    public function test_member_can_subscribe_free_plan_and_get_active_subscription(): void
    {
        $user = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '免费会员',
            'slug' => 'free-plan',
            'description' => '免费层',
            'price' => 0,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $this->actingAs($user)
            ->post("/pricing/{$plan->slug}/subscribe")
            ->assertRedirect(route('member.subscriptions.index'));

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_member_can_complete_paid_plan_payment_and_activate_subscription(): void
    {
        $user = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '专业会员',
            'slug' => 'pro-plan',
            'description' => '专业层',
            'price' => 199,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $this->actingAs($user)
            ->post("/pricing/{$plan->slug}/subscribe")
            ->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_type', 'membership')
            ->where('purchasable_id', $plan->id)
            ->firstOrFail();

        $subscription = UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->firstOrFail();

        $this->assertSame('pending', $subscription->status);

        $this->actingAs($user)
            ->post(route('member.orders.simulate-payment', $order), [
                'provider' => 'manual',
                'action' => 'paid',
            ])
            ->assertRedirect(route('member.orders.index'));

        $subscription->refresh();

        $this->assertSame('active', $subscription->status);
        $this->assertNotNull($subscription->started_at);
        $this->assertNotNull($subscription->expires_at);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'paid',
        ]);
    }

    public function test_failed_paid_plan_payment_keeps_order_and_subscription_pending(): void
    {
        $user = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '标准会员',
            'slug' => 'standard-plan',
            'description' => '标准层',
            'price' => 99,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $this->actingAs($user)
            ->post("/pricing/{$plan->slug}/subscribe")
            ->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_type', 'membership')
            ->where('purchasable_id', $plan->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->post(route('member.orders.simulate-payment', $order), [
                'provider' => 'manual',
                'action' => 'failed',
            ])
            ->assertRedirect(route('member.orders.pay', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);
    }

    public function test_closed_paid_plan_payment_closes_order_and_cancels_subscription(): void
    {
        $user = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '年度会员',
            'slug' => 'annual-plan',
            'description' => '年度层',
            'price' => 299,
            'billing_period' => 'yearly',
            'duration_days' => 365,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $this->actingAs($user)
            ->post("/pricing/{$plan->slug}/subscribe")
            ->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_type', 'membership')
            ->where('purchasable_id', $plan->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->post(route('member.orders.simulate-payment', $order), [
                'provider' => 'manual',
                'action' => 'closed',
            ])
            ->assertRedirect(route('member.orders.index'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_member_cannot_create_duplicate_active_subscription_for_same_plan(): void
    {
        $user = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '成长会员',
            'slug' => 'growth-plan',
            'description' => '成长层',
            'price' => 0,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'last_order_id' => null,
            'status' => 'active',
            'auto_renew' => false,
            'started_at' => now(),
            'expires_at' => now()->addDays(30),
            'meta' => ['source' => 'test'],
        ]);

        $this->actingAs($user)
            ->post("/pricing/{$plan->slug}/subscribe")
            ->assertRedirect(route('member.subscriptions.index'));

        $this->assertSame(1, UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->count());
    }

    public function test_member_can_register_paid_event_and_receive_pending_order(): void
    {
        $user = $this->createMemberUser();

        $event = Event::create([
            'title' => '付费活动',
            'slug' => 'paid-event',
            'status' => 'registration-open',
            'location' => '上海',
            'is_paid' => true,
            'price' => 199,
            'required_member_group_id' => null,
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(3),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(5),
            'capacity' => 50,
            'summary' => '活动摘要',
            'content' => '活动正文',
            'payload' => ['demo' => true],
        ]);

        $response = $this->actingAs($user)
            ->post("/events/{$event->slug}/register");

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_type', 'event')
            ->where('purchasable_id', $event->id)
            ->first();

        $this->assertNotNull($order);

        $response->assertRedirect(route('member.orders.pay', $order));

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    public function test_member_can_complete_paid_event_payment_and_registration_is_approved(): void
    {
        $user = $this->createMemberUser();

        $event = Event::create([
            'title' => '闭门分享会',
            'slug' => 'paid-event-approved',
            'status' => 'registration-open',
            'location' => '杭州',
            'is_paid' => true,
            'price' => 299,
            'required_member_group_id' => null,
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(10)->addHours(2),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(7),
            'capacity' => 40,
            'summary' => '活动摘要',
            'content' => '活动正文',
            'payload' => ['demo' => true],
        ]);

        $this->actingAs($user)
            ->post("/events/{$event->slug}/register")
            ->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_type', 'event')
            ->where('purchasable_id', $event->id)
            ->firstOrFail();

        $registration = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->assertSame('pending', $registration->status);
        $this->assertSame('pending', $registration->payment_status);

        $this->actingAs($user)
            ->post(route('member.orders.simulate-payment', $order), [
                'provider' => 'manual',
                'action' => 'paid',
            ])
            ->assertRedirect(route('member.orders.index'));

        $registration->refresh();

        $this->assertSame('approved', $registration->status);
        $this->assertSame('paid', $registration->payment_status);
    }

    public function test_closed_paid_event_payment_cancels_registration(): void
    {
        $user = $this->createMemberUser();

        $event = Event::create([
            'title' => '活动关闭测试',
            'slug' => 'paid-event-closed',
            'status' => 'registration-open',
            'location' => '北京',
            'is_paid' => true,
            'price' => 199,
            'required_member_group_id' => null,
            'starts_at' => now()->addDays(12),
            'ends_at' => now()->addDays(12)->addHours(2),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(8),
            'capacity' => 80,
            'summary' => '活动摘要',
            'content' => '活动正文',
            'payload' => ['demo' => true],
        ]);

        $this->actingAs($user)
            ->post("/events/{$event->slug}/register")
            ->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('order_type', 'event')
            ->where('purchasable_id', $event->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->post(route('member.orders.simulate-payment', $order), [
                'provider' => 'manual',
                'action' => 'closed',
            ])
            ->assertRedirect(route('member.orders.index'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => 'cancelled',
            'payment_status' => 'closed',
        ]);
    }

    public function test_member_cannot_register_event_when_capacity_is_full(): void
    {
        $user = $this->createMemberUser();
        $otherUser = $this->createMemberUser();

        $event = Event::create([
            'title' => '满员活动',
            'slug' => 'sold-out-event',
            'status' => 'registration-open',
            'location' => '上海',
            'is_paid' => false,
            'price' => 0,
            'required_member_group_id' => null,
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(2),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(2),
            'capacity' => 1,
            'summary' => '活动摘要',
            'content' => '活动正文',
            'payload' => ['demo' => true],
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
            'order_id' => null,
            'status' => 'approved',
            'payment_status' => 'not_required',
            'notes' => null,
            'payload' => ['seeded' => true],
        ]);

        $this->actingAs($user)
            ->post("/events/{$event->slug}/register")
            ->assertRedirect(route('events.show', $event->slug));

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_member_cannot_register_event_when_required_group_is_not_met(): void
    {
        $basicGroup = MemberGroup::create([
            'name' => '普通会员',
            'min_points' => 0,
            'max_points' => 999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
                'events.access' => true,
            ],
        ]);

        $premiumGroup = MemberGroup::create([
            'name' => '高级会员',
            'min_points' => 1000,
            'max_points' => 999999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
                'events.access' => true,
            ],
        ]);

        $user = User::create([
            'username' => 'basic-member',
            'email' => 'basic-member@example.com',
            'password_hash' => 'secret123',
            'group_id' => $basicGroup->id,
            'status' => 'active',
        ]);

        $event = Event::create([
            'title' => '高级会员活动',
            'slug' => 'premium-event',
            'status' => 'registration-open',
            'location' => '深圳',
            'is_paid' => false,
            'price' => 0,
            'required_member_group_id' => $premiumGroup->id,
            'starts_at' => now()->addDays(6),
            'ends_at' => now()->addDays(6)->addHours(3),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(4),
            'capacity' => 20,
            'summary' => '活动摘要',
            'content' => '活动正文',
            'payload' => ['demo' => true],
        ]);

        $this->actingAs($user)
            ->post("/events/{$event->slug}/register")
            ->assertRedirect(route('events.show', $event->slug));

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    protected function createMemberUser(): User
    {
        $group = MemberGroup::create([
            'name' => '默认会员-'.str()->random(6),
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
            'username' => 'member-'.str()->random(8),
            'email' => str()->random(8).'@example.com',
            'password_hash' => 'secret123',
            'group_id' => $group->id,
            'status' => 'active',
        ]);
    }
}
