<?php

namespace App\Support;

use Illuminate\Http\Request;

class PaymentWebhookVerifier
{
    public static function verify(Request $request, string $provider, array $settings): array
    {
        $mode = (string) data_get($settings, 'payment_mode', 'sandbox');

        if ($mode !== 'production') {
            return [
                'verified' => true,
                'mode' => $mode,
                'reason' => 'sandbox-bypass',
                'signature_type' => 'diagnostic',
            ];
        }

        return match ($provider) {
            'stripe' => static::verifyStripe($request, $settings),
            'wechat' => static::unsupported('wechat-signature-not-implemented'),
            'alipay' => static::unsupported('alipay-signature-not-implemented'),
            'paypal' => static::unsupported('paypal-signature-not-implemented'),
            default => static::unsupported('unknown-provider'),
        };
    }

    protected static function verifyStripe(Request $request, array $settings): array
    {
        $secret = (string) data_get($settings, 'stripe_webhook_secret', '');

        if ($secret === '') {
            return static::unsupported('missing-stripe-webhook-secret');
        }

        $header = (string) $request->header('Stripe-Signature', '');

        if ($header === '') {
            return static::unsupported('missing-stripe-signature-header');
        }

        $parts = collect(explode(',', $header))
            ->mapWithKeys(function (string $chunk): array {
                [$key, $value] = array_pad(explode('=', trim($chunk), 2), 2, '');

                return [$key => $value];
            });

        $timestamp = (string) ($parts->get('t') ?? '');
        $signature = (string) ($parts->get('v1') ?? '');

        if ($timestamp === '' || $signature === '') {
            return static::unsupported('invalid-stripe-signature-format');
        }

        if (! ctype_digit($timestamp)) {
            return static::unsupported('invalid-stripe-timestamp');
        }

        $signedPayload = $timestamp.'.'.$request->getContent();
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        if (! hash_equals($expected, $signature)) {
            return static::unsupported('stripe-signature-mismatch');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return static::unsupported('stripe-signature-expired');
        }

        return [
            'verified' => true,
            'mode' => 'production',
            'reason' => 'stripe-signature-verified',
            'signature_type' => 'stripe-v1',
        ];
    }

    protected static function unsupported(string $reason): array
    {
        return [
            'verified' => false,
            'mode' => 'production',
            'reason' => $reason,
            'signature_type' => null,
        ];
    }
}
