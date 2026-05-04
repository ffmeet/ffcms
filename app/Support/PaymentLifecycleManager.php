<?php

namespace App\Support;

use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

class PaymentLifecycleManager
{
    public static function markPaid(Payment $payment, array $payload = []): void
    {
        DB::transaction(function () use ($payment, $payload): void {
            $payment->refresh();
            $order = $payment->order()->first();
            $source = (string) ($payload['source'] ?? 'system');
            $entry = OperationHistory::makeEntry('支付标记成功', $source, 'paid', [
                'provider' => $payment->provider,
                'order_no' => $order?->order_no,
                'entry' => data_get($payment->payload ?? [], 'entry'),
            ]);

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payload' => OperationHistory::append(array_merge($payment->payload ?? [], $payload), 'history', $entry),
            ]);

            if (! $order) {
                return;
            }

            $wasPaid = $order->status === 'paid';

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'meta' => OperationHistory::append($order->meta ?? [], 'history', OperationHistory::makeEntry('订单已完成', $source, 'paid', [
                    'provider' => $payment->provider,
                    'order_no' => $order->order_no,
                ])),
            ]);

            if ($order->order_type === 'membership') {
                UserSubscription::query()
                    ->where('last_order_id', $order->id)
                    ->whereIn('status', ['pending', 'inactive', 'cancelled'])
                    ->get()
                    ->each(function (UserSubscription $subscription) use ($source): void {
                        $durationDays = (int) ($subscription->plan?->duration_days ?? 30);

                        $subscription->update([
                            'status' => 'active',
                            'started_at' => $subscription->started_at ?? now(),
                            'expires_at' => now()->addDays($durationDays),
                            'meta' => OperationHistory::append($subscription->meta ?? [], 'history', OperationHistory::makeEntry('订阅自动生效', $source, 'active', [
                                'order_no' => $subscription->lastOrder?->order_no,
                            ])),
                        ]);
                    });
            }

            if ($order->order_type === 'event') {
                EventRegistration::query()
                    ->where('order_id', $order->id)
                    ->whereIn('status', ['pending', 'cancelled'])
                    ->get()
                    ->each(function (EventRegistration $registration): void {
                        $registration->update([
                            'status' => 'approved',
                            'payment_status' => 'paid',
                        ]);
                    });
            }

            if ($order->order_type === 'product' && ! $wasPaid) {
                $product = $order->purchasable;

                if ($product instanceof Product && ! is_null($product->stock) && $product->stock > 0) {
                    $product->decrement('stock');
                }
            }
        });
    }

    public static function markFailed(Payment $payment, array $payload = []): void
    {
        DB::transaction(function () use ($payment, $payload): void {
            $source = (string) ($payload['source'] ?? 'system');

            $payment->update([
                'status' => 'failed',
                'payload' => OperationHistory::append(array_merge($payment->payload ?? [], $payload), 'history', OperationHistory::makeEntry('支付标记失败', $source, 'failed', [
                    'provider' => $payment->provider,
                    'order_no' => $payment->order?->order_no,
                    'entry' => data_get($payment->payload ?? [], 'entry'),
                ])),
            ]);
        });
    }

    public static function markClosed(Payment $payment, array $payload = []): void
    {
        DB::transaction(function () use ($payment, $payload): void {
            $payment->refresh();
            $order = $payment->order()->first();
            $source = (string) ($payload['source'] ?? 'system');

            $payment->update([
                'status' => 'closed',
                'payload' => OperationHistory::append(array_merge($payment->payload ?? [], $payload), 'history', OperationHistory::makeEntry('支付已关闭', $source, 'closed', [
                    'provider' => $payment->provider,
                    'order_no' => $order?->order_no,
                    'entry' => data_get($payment->payload ?? [], 'entry'),
                ])),
            ]);

            if (! $order || $order->status === 'paid') {
                return;
            }

            $order->update([
                'status' => 'closed',
                'meta' => OperationHistory::append($order->meta ?? [], 'history', OperationHistory::makeEntry('订单已关闭', $source, 'closed', [
                    'provider' => $payment->provider,
                    'order_no' => $order->order_no,
                ])),
            ]);

            if ($order->order_type === 'membership') {
                UserSubscription::query()
                    ->where('last_order_id', $order->id)
                    ->where('status', 'pending')
                    ->get()
                    ->each(function (UserSubscription $subscription) use ($source): void {
                        $subscription->update([
                            'status' => 'cancelled',
                            'meta' => OperationHistory::append($subscription->meta ?? [], 'history', OperationHistory::makeEntry('订阅已取消续费', $source, 'cancelled', [
                                'order_no' => $subscription->lastOrder?->order_no,
                            ])),
                        ]);
                    });
            }

            if ($order->order_type === 'event') {
                EventRegistration::query()
                    ->where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'payment_status' => 'closed',
                    ]);
            }
        });
    }
}
