<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Support\OperationHistory;
use App\Support\PaymentProviderRegistry;
use App\Support\PaymentWebhookVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $provider): JsonResponse
    {
        abort_unless(array_key_exists($provider, PaymentProviderRegistry::definitions()) && $provider !== 'manual', 404);

        $businessSettings = SiteSetting::current()->business_settings ?? [];
        $verification = PaymentWebhookVerifier::verify($request, $provider, $businessSettings);

        if (! ($verification['verified'] ?? false)) {
            Log::warning('payment.webhook.rejected', [
                'provider' => $provider,
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
                'reason' => $verification['reason'] ?? 'unverified',
            ]);

            return response()->json([
                'ok' => false,
                'provider' => $provider,
                'message' => 'Webhook verification failed.',
                'reason' => $verification['reason'] ?? 'unverified',
            ], 403);
        }

        Log::channel(config('logging.default'))->info('payment.webhook.received', [
            'provider' => $provider,
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'verification' => $verification,
        ]);

        $payment = $this->resolvePayment($request, $provider);

        if ($payment) {
            $payload = $payment->payload ?? [];
            $payload = OperationHistory::append($payload, 'webhook_receipts', [
                'at' => now()->toDateTimeString(),
                'provider' => $provider,
                'status' => (string) $request->input('status', 'received'),
                'provider_payment_no' => $request->input('provider_payment_no'),
                'order_no' => $request->input('order_no', $payment->order?->order_no),
                'note' => '收到支付回调诊断数据',
                'verification' => $verification['reason'] ?? null,
            ]);
            $payload = OperationHistory::append($payload, 'history', OperationHistory::makeEntry('收到支付回调', 'webhook-'.$provider, (string) $request->input('status', 'received'), [
                'provider' => $provider,
                'order_no' => $request->input('order_no', $payment->order?->order_no),
                'entry' => data_get($payment->payload ?? [], 'entry'),
                'verification' => $verification['reason'] ?? null,
            ]));

            $payment->update([
                'provider_payment_no' => $request->input('provider_payment_no', $payment->provider_payment_no),
                'payload' => array_merge($payload, [
                    'last_webhook_status' => $request->input('status', 'received'),
                    'last_webhook_at' => now()->toDateTimeString(),
                ]),
            ]);
        }

        return response()->json([
            'ok' => true,
            'provider' => $provider,
            'message' => 'Webhook accepted for diagnostics pipeline.',
        ]);
    }

    protected function resolvePayment(Request $request, string $provider): ?Payment
    {
        $paymentId = $request->integer('payment_id');

        if ($paymentId > 0) {
            return Payment::query()->whereKey($paymentId)->where('provider', $provider)->first();
        }

        $orderNo = (string) $request->input('order_no', '');

        if ($orderNo === '') {
            return null;
        }

        return Payment::query()
            ->where('provider', $provider)
            ->whereHas('order', fn ($query) => $query->where('order_no', $orderNo))
            ->latest('id')
            ->first();
    }
}
