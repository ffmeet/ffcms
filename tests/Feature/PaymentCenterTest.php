<?php

namespace Tests\Feature;

use App\Filament\Pages\PaymentCenter;
use App\Models\MemberGroup;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_payment_center_and_see_channel_readiness(): void
    {
        $group = MemberGroup::create([
            'name' => '编辑组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $staff = User::create([
            'username' => 'payment-staff',
            'email' => 'payment-staff@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
            'is_staff' => true,
        ]);

        $settings = SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $businessSettings['payment_mode'] = 'sandbox';
        $businessSettings['wechat_app_id'] = 'wx-test-app';
        $businessSettings['wechat_mch_id'] = '1900000109';
        $businessSettings['wechat_api_v3_key'] = 'wechat-key';

        $settings->update([
            'business_settings' => $businessSettings,
        ]);

        $this->actingAs($staff)
            ->get(PaymentCenter::getUrl())
            ->assertOk()
            ->assertSee('支付中心')
            ->assertSee('微信支付')
            ->assertSee('可结算')
            ->assertSee(route('payments.webhook', 'wechat'))
            ->assertSee('支付宝')
            ->assertSee('待补齐');
    }

    public function test_payment_webhook_endpoint_accepts_supported_provider(): void
    {
        $group = MemberGroup::create([
            'name' => '普通组',
            'min_points' => 0,
            'max_points' => 999999,
        ]);

        $member = User::create([
            'username' => 'webhook-member',
            'email' => 'webhook-member@example.com',
            'password_hash' => Hash::make('password'),
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        $order = Order::create([
            'user_id' => $member->id,
            'order_no' => 'TEST-20260426',
            'order_type' => 'membership',
            'purchasable_type' => null,
            'purchasable_id' => null,
            'title' => 'Webhook 测试订单',
            'currency' => 'CNY',
            'amount' => 99,
            'status' => 'pending',
            'paid_at' => null,
            'meta' => ['source' => 'test'],
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'wechat',
            'provider_payment_no' => null,
            'status' => 'pending',
            'amount' => 99,
            'payload' => ['entry' => 'member-checkout'],
            'paid_at' => null,
        ]);

        $this->postJson(route('payments.webhook', 'wechat'), [
            'order_no' => 'TEST-20260426',
            'status' => 'paid',
            'provider_payment_no' => 'WX-WEBHOOK-001',
        ])->assertOk()
            ->assertJson([
                'ok' => true,
                'provider' => 'wechat',
            ]);

        $payment->refresh();

        $this->assertSame('WX-WEBHOOK-001', $payment->provider_payment_no);
        $this->assertSame('paid', data_get($payment->payload, 'last_webhook_status'));
        $this->assertSame('收到支付回调诊断数据', data_get($payment->payload, 'webhook_receipts.0.note'));
        $this->assertSame('收到支付回调', data_get($payment->payload, 'history.0.event'));
    }
}
