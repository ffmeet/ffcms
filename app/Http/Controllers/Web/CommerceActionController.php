<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\UserSubscription;
use App\Support\OrderNumber;
use App\Support\PaymentProviderRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommerceActionController extends Controller
{
    public function purchaseProduct(Request $request, string $slug): RedirectResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $user = $request->user();
        $isFree = (float) $product->price <= 0;

        if (! is_null($product->stock) && $product->stock < 1) {
            return redirect()->route('shop.show', $product->slug)
                ->with('status', '当前商品暂时不可购买，请稍后再试。');
        }

        $orderId = null;

        DB::transaction(function () use ($user, $product, $isFree, &$orderId): void {
            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_no' => OrderNumber::make('PRD'),
                'order_type' => 'product',
                'purchasable_type' => Product::class,
                'purchasable_id' => $product->id,
                'title' => $product->title,
                'currency' => $product->currency ?? 'CNY',
                'amount' => $product->price,
                'status' => $isFree ? 'paid' : 'pending',
                'paid_at' => $isFree ? now() : null,
                'meta' => [
                    'delivery_type' => $product->delivery_type,
                    'source' => 'shop-front',
                ],
            ]);

            $this->createPendingPayment($order, [
                'entry' => 'shop',
                'delivery_type' => $product->delivery_type,
            ]);

            $orderId = $order->id;
        });

        return $isFree
            ? redirect()->route('member.orders.index')->with('status', '商品订单已自动确认。')
            : redirect()->route('member.orders.pay', $orderId)->with('status', '商品订单与待支付记录已创建，请继续完成支付。');
    }

    public function subscribe(Request $request, string $slug): RedirectResponse
    {
        $plan = MembershipPlan::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user = $request->user();
        $isFree = (float) $plan->price <= 0;

        $existing = UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if ($existing) {
            return redirect()->route('member.subscriptions.index')
                ->with('status', '你已经有这个套餐的有效订阅记录了。');
        }

        $orderId = null;

        DB::transaction(function () use ($user, $plan, $isFree, &$orderId): void {
            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_no' => OrderNumber::make('SUB'),
                'order_type' => 'membership',
                'purchasable_type' => MembershipPlan::class,
                'purchasable_id' => $plan->id,
                'title' => $plan->name,
                'currency' => 'CNY',
                'amount' => $plan->price,
                'status' => $isFree ? 'paid' : 'pending',
                'paid_at' => $isFree ? now() : null,
                'meta' => [
                    'billing_period' => $plan->billing_period,
                    'duration_days' => $plan->duration_days,
                    'source' => 'pricing-front',
                ],
            ]);

            $this->createPendingPayment($order, [
                'entry' => 'pricing',
                'billing_period' => $plan->billing_period,
            ]);

            UserSubscription::query()->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'last_order_id' => $order->id,
                'status' => $isFree ? 'active' : 'pending',
                'auto_renew' => false,
                'started_at' => $isFree ? now() : null,
                'expires_at' => $isFree ? now()->addDays((int) $plan->duration_days) : null,
                'meta' => [
                    'source' => 'manual-checkout',
                ],
            ]);

            $orderId = $order->id;
        });

        return $isFree
            ? redirect()->route('member.subscriptions.index')->with('status', '订阅已立即生效。')
            : redirect()->route('member.orders.pay', $orderId)->with('status', '订阅申请与待支付记录已创建，请继续完成支付。');
    }

    public function registerEvent(Request $request, string $slug): RedirectResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $user = $request->user();

        if ($event->status !== 'registration-open') {
            return redirect()->route('events.show', $event->slug)
                ->with('status', '当前活动暂未开放报名。');
        }

        if (! $user->hasMemberPermission('events.access')) {
            return redirect()->route('events.show', $event->slug)
                ->with('status', '当前账号暂未开通活动权限。');
        }

        if ($event->required_member_group_id && ! $user->canAccessMemberGroup($event->memberGroup)) {
            return redirect()->route('events.show', $event->slug)
                ->with('status', '当前活动仅对指定会员级别开放。');
        }

        if ($event->registration_closes_at && $event->registration_closes_at->isPast()) {
            return redirect()->route('events.show', $event->slug)
                ->with('status', '当前活动报名已截止。');
        }

        if ($event->capacity && $event->registrations()->whereIn('status', ['pending', 'approved'])->count() >= $event->capacity) {
            return redirect()->route('events.show', $event->slug)
                ->with('status', '当前活动名额已满。');
        }

        $existing = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return redirect()->route('member.activities.index')
                ->with('status', '你已经有这场活动的报名记录了。');
        }

        $requiresPayment = $event->is_paid && (float) $event->price > 0;

        $orderId = null;

        DB::transaction(function () use ($user, $event, $requiresPayment, &$orderId): void {
            $order = null;

            if ($requiresPayment) {
                $order = Order::query()->create([
                    'user_id' => $user->id,
                    'order_no' => OrderNumber::make('EVT'),
                    'order_type' => 'event',
                    'purchasable_type' => Event::class,
                    'purchasable_id' => $event->id,
                    'title' => $event->title,
                    'currency' => 'CNY',
                    'amount' => $event->price,
                    'status' => 'pending',
                    'meta' => [
                        'starts_at' => optional($event->starts_at)?->toDateTimeString(),
                        'location' => $event->location,
                        'source' => 'events-front',
                    ],
                ]);

                $this->createPendingPayment($order, [
                    'entry' => 'events',
                    'starts_at' => optional($event->starts_at)?->toDateTimeString(),
                ]);

                $orderId = $order->id;
            }

            EventRegistration::query()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'status' => $requiresPayment ? 'pending' : 'approved',
                'payment_status' => $requiresPayment ? 'pending' : 'not_required',
                'notes' => null,
                'payload' => [
                    'is_paid' => $event->is_paid,
                ],
            ]);
        });

        return $requiresPayment
            ? redirect()->route('member.orders.pay', $orderId)->with('status', '活动报名与待支付记录已创建，请继续完成支付。')
            : redirect()->route('member.activities.index')->with('status', '活动报名已确认。');
    }

    protected function createPendingPayment(Order $order, array $payload = []): ?Payment
    {
        if ((float) $order->amount <= 0) {
            return null;
        }

        return $order->payments()->create([
            'provider' => $this->resolvePaymentProvider(),
            'status' => 'pending',
            'amount' => $order->amount,
            'payload' => array_merge([
                'order_no' => $order->order_no,
                'order_type' => $order->order_type,
            ], $payload),
        ]);
    }

    protected function resolvePaymentProvider(): string
    {
        return PaymentProviderRegistry::defaultProvider(
            SiteSetting::current()->business_settings ?? []
        );
    }
}
