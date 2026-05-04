<?php

namespace Tests\Feature;

use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use App\Models\MemberGroup;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BackofficeOperationsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_payments_table_with_operational_columns(): void
    {
        $staff = $this->createStaffUser();
        $member = $this->createMemberUser();

        $order = Order::create([
            'user_id' => $member->id,
            'order_no' => 'OPS-TABLE-001',
            'order_type' => 'membership',
            'purchasable_type' => null,
            'purchasable_id' => null,
            'title' => '后台支付表测试',
            'currency' => 'CNY',
            'amount' => 199,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'provider_payment_no' => 'PAY-TABLE-001',
            'status' => 'pending',
            'amount' => 199,
            'payload' => ['entry' => 'member-checkout', 'simulated' => true],
            'paid_at' => null,
        ]);

        $this->actingAs($staff)
            ->get(PaymentResource::getUrl())
            ->assertOk()
            ->assertSee('支付台')
            ->assertSee('订单类型')
            ->assertSee('来源')
            ->assertSee('支付渠道')
            ->assertSee('模拟支付');
    }

    public function test_staff_can_open_subscriptions_table_with_order_and_payment_status(): void
    {
        $staff = $this->createStaffUser();
        $member = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '测试订阅套餐',
            'slug' => 'ops-subscription-plan',
            'description' => '测试订阅套餐',
            'price' => 99,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $order = Order::create([
            'user_id' => $member->id,
            'order_no' => 'OPS-SUB-001',
            'order_type' => 'membership',
            'purchasable_type' => MembershipPlan::class,
            'purchasable_id' => $plan->id,
            'title' => '后台订阅表测试',
            'currency' => 'CNY',
            'amount' => 99,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 99,
            'payload' => ['entry' => 'member-checkout'],
            'paid_at' => null,
        ]);

        UserSubscription::create([
            'user_id' => $member->id,
            'plan_id' => $plan->id,
            'last_order_id' => $order->id,
            'status' => 'pending',
            'auto_renew' => false,
            'started_at' => null,
            'expires_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        $this->actingAs($staff)
            ->get(UserSubscriptionResource::getUrl())
            ->assertOk()
            ->assertSee('订阅台')
            ->assertSee('最近订单')
            ->assertSee('订单状态')
            ->assertSee('支付状态')
            ->assertSee('待处理订单');
    }

    public function test_staff_can_open_orders_table_with_payment_and_subscription_actions(): void
    {
        $staff = $this->createStaffUser();
        $member = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '订单表联动套餐',
            'slug' => 'ops-order-plan',
            'description' => '订单表联动套餐',
            'price' => 129,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $order = Order::create([
            'user_id' => $member->id,
            'order_no' => 'OPS-ORDER-TABLE-001',
            'order_type' => 'membership',
            'purchasable_type' => MembershipPlan::class,
            'purchasable_id' => $plan->id,
            'title' => '后台订单台测试',
            'currency' => 'CNY',
            'amount' => 129,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'shop-front'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 129,
            'payload' => ['entry' => 'member-checkout'],
            'paid_at' => null,
        ]);

        UserSubscription::create([
            'user_id' => $member->id,
            'plan_id' => $plan->id,
            'last_order_id' => $order->id,
            'status' => 'pending',
            'auto_renew' => false,
            'started_at' => null,
            'expires_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        $this->actingAs($staff)
            ->get(OrderResource::getUrl())
            ->assertOk()
            ->assertSee('订单台')
            ->assertSee('支付渠道')
            ->assertSee('支付状态')
            ->assertSee('来源')
            ->assertSee('订阅')
            ->assertSee('批量关闭待处理订单');
    }

    public function test_staff_can_open_order_edit_page_with_operational_overview(): void
    {
        $staff = $this->createStaffUser();
        $member = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '订单详情套餐',
            'slug' => 'ops-order-detail-plan',
            'description' => '订单详情套餐',
            'price' => 168,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $order = Order::create([
            'user_id' => $member->id,
            'order_no' => 'OPS-ORDER-DETAIL-001',
            'order_type' => 'membership',
            'purchasable_type' => MembershipPlan::class,
            'purchasable_id' => $plan->id,
            'title' => '订单处理页测试',
            'currency' => 'CNY',
            'amount' => 168,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'member-center'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 168,
            'payload' => ['entry' => 'member-checkout'],
            'paid_at' => null,
        ]);

        UserSubscription::create([
            'user_id' => $member->id,
            'plan_id' => $plan->id,
            'last_order_id' => $order->id,
            'status' => 'pending',
            'auto_renew' => false,
            'started_at' => null,
            'expires_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        $this->actingAs($staff)
            ->get(OrderResource::getUrl('edit', ['record' => $order]))
            ->assertOk()
            ->assertSee('订单链路概览')
            ->assertSee('处理建议')
            ->assertSee('最新支付')
            ->assertSee('记录处理备注')
            ->assertSee('查看支付')
            ->assertSee('查看订阅');
    }

    public function test_staff_can_open_payment_edit_page_with_operational_overview(): void
    {
        $staff = $this->createStaffUser();
        $member = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '支付详情套餐',
            'slug' => 'ops-payment-detail-plan',
            'description' => '支付详情套餐',
            'price' => 88,
            'billing_period' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $order = Order::create([
            'user_id' => $member->id,
            'order_no' => 'OPS-PAYMENT-DETAIL-001',
            'order_type' => 'membership',
            'purchasable_type' => MembershipPlan::class,
            'purchasable_id' => $plan->id,
            'title' => '支付处理页测试',
            'currency' => 'CNY',
            'amount' => 88,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'shop-front'],
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'provider_payment_no' => 'PAY-DETAIL-001',
            'status' => 'pending',
            'amount' => 88,
            'payload' => [
                'entry' => 'member-checkout',
                'history' => [[
                    'at' => '2026-04-28 10:00:00',
                    'event' => '支付创建',
                    'source' => 'member-checkout',
                    'status' => 'pending',
                ]],
                'webhook_receipts' => [[
                    'at' => '2026-04-28 10:02:00',
                    'provider' => 'manual',
                    'status' => 'received',
                    'note' => '收到支付回调诊断数据',
                ]],
            ],
            'paid_at' => null,
        ]);

        UserSubscription::create([
            'user_id' => $member->id,
            'plan_id' => $plan->id,
            'last_order_id' => $order->id,
            'status' => 'pending',
            'auto_renew' => false,
            'started_at' => null,
            'expires_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        $this->actingAs($staff)
            ->get(PaymentResource::getUrl('edit', ['record' => $payment]))
            ->assertOk()
            ->assertSee('支付链路概览')
            ->assertSee('业务来源')
            ->assertSee('渠道流水号')
            ->assertSee('记录处理备注')
            ->assertSee('支付最近变化')
            ->assertSee('最近 webhook 回写')
            ->assertSee('查看订单')
            ->assertSee('查看订阅');
    }

    public function test_staff_can_open_subscription_edit_page_with_operational_overview(): void
    {
        $staff = $this->createStaffUser();
        $member = $this->createMemberUser();

        $plan = MembershipPlan::create([
            'name' => '订阅详情套餐',
            'slug' => 'ops-subscription-detail-plan',
            'description' => '订阅详情套餐',
            'price' => 118,
            'billing_period' => 'monthly',
            'duration_days' => 45,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['会员中心' => '可用'],
        ]);

        $order = Order::create([
            'user_id' => $member->id,
            'order_no' => 'OPS-SUBSCRIPTION-DETAIL-001',
            'order_type' => 'membership',
            'purchasable_type' => MembershipPlan::class,
            'purchasable_id' => $plan->id,
            'title' => '订阅处理页测试',
            'currency' => 'CNY',
            'amount' => 118,
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => ['source' => 'member-center'],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'paid',
            'amount' => 118,
            'payload' => ['entry' => 'member-checkout'],
            'paid_at' => now(),
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $member->id,
            'plan_id' => $plan->id,
            'last_order_id' => $order->id,
            'status' => 'pending',
            'auto_renew' => false,
            'started_at' => null,
            'expires_at' => null,
            'meta' => [
                'source' => 'test',
                'history' => [[
                    'at' => '2026-04-28 11:00:00',
                    'event' => '创建待生效订阅',
                    'source' => 'member-center',
                    'status' => 'pending',
                ]],
            ],
        ]);

        $this->actingAs($staff)
            ->get(UserSubscriptionResource::getUrl('edit', ['record' => $subscription]))
            ->assertOk()
            ->assertSee('订阅链路概览')
            ->assertSee('支付状态')
            ->assertSee('有效期')
            ->assertSee('风险提示')
            ->assertSee('记录处理备注')
            ->assertSee('订阅最近变化')
            ->assertSee('查看订单')
            ->assertSee('立即生效');
    }

    protected function createStaffUser(): User
    {
        $group = MemberGroup::create([
            'name' => '后台员工组-'.str()->random(6),
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        return User::create([
            'username' => 'backoffice-staff-'.str()->random(6),
            'email' => str()->random(8).'@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
            'is_staff' => true,
        ]);
    }

    protected function createMemberUser(): User
    {
        $group = MemberGroup::create([
            'name' => '普通会员组-'.str()->random(6),
            'min_points' => 0,
            'max_points' => 999999,
            'permissions' => [
                'site.access' => true,
                'member.center' => true,
            ],
        ]);

        return User::create([
            'username' => 'ops-member-'.str()->random(6),
            'email' => str()->random(8).'@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
        ]);
    }
}
