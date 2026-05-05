<?php

namespace App\Support;

use App\Models\SiteSetting;

class PaymentProviderRegistry
{
    public const PROVIDERS = [
        'wechat' => [
            'label' => '微信支付',
            'toggle_key' => 'wechat_enabled',
            'credentials' => [
                'wechat_app_id' => 'App ID',
                'wechat_mch_id' => '商户号',
                'wechat_api_v3_key' => 'API v3 Key',
                'wechat_private_key' => '商户私钥',
                'wechat_serial_no' => '商户证书序列号',
                'wechat_platform_certificate' => '微信平台证书',
            ],
        ],
        'alipay' => [
            'label' => '支付宝',
            'toggle_key' => 'alipay_enabled',
            'credentials' => [
                'alipay_app_id' => 'App ID',
                'alipay_pid' => 'PID / 商户身份',
                'alipay_public_key' => '支付宝公钥',
                'alipay_private_key' => '商户私钥',
            ],
        ],
        'paypal' => [
            'label' => 'PayPal',
            'toggle_key' => 'paypal_enabled',
            'credentials' => [
                'paypal_client_id' => 'Client ID',
                'paypal_client_secret' => 'Client Secret',
                'paypal_webhook_id' => 'Webhook ID',
            ],
        ],
        'stripe' => [
            'label' => 'Stripe',
            'toggle_key' => 'stripe_enabled',
            'credentials' => [
                'stripe_publishable_key' => 'Publishable Key',
                'stripe_secret_key' => 'Secret Key',
                'stripe_webhook_secret' => 'Webhook Secret',
            ],
        ],
        'manual' => [
            'label' => '线下 / 人工',
            'toggle_key' => null,
            'credentials' => [],
        ],
    ];

    public static function definitions(): array
    {
        return self::PROVIDERS;
    }

    public static function label(string $provider): string
    {
        return self::PROVIDERS[$provider]['label'] ?? CommerceLabels::paymentProvider($provider);
    }

    public static function isEnabled(string $provider, ?array $settings = null): bool
    {
        if ($provider === 'manual') {
            return true;
        }

        $toggleKey = self::PROVIDERS[$provider]['toggle_key'] ?? null;

        if (! $toggleKey) {
            return false;
        }

        return data_get($settings ?? SiteSetting::current()->business_settings ?? [], $toggleKey, false) === true;
    }

    public static function missingCredentials(string $provider, ?array $settings = null): array
    {
        $settings ??= SiteSetting::current()->business_settings ?? [];
        $credentials = self::PROVIDERS[$provider]['credentials'] ?? [];

        return collect($credentials)
            ->filter(fn (string $label, string $key): bool => blank(data_get($settings, $key)))
            ->all();
    }

    public static function isReady(string $provider, ?array $settings = null): bool
    {
        if ($provider === 'manual') {
            return true;
        }

        if (! static::isEnabled($provider, $settings)) {
            return false;
        }

        return static::missingCredentials($provider, $settings) === [];
    }

    public static function readyProviders(?array $settings = null): array
    {
        return collect(array_keys(self::PROVIDERS))
            ->reject(fn (string $provider): bool => $provider === 'manual')
            ->filter(fn (string $provider): bool => static::isReady($provider, $settings))
            ->values()
            ->all();
    }

    public static function enabledProviders(?array $settings = null): array
    {
        return collect(array_keys(self::PROVIDERS))
            ->reject(fn (string $provider): bool => $provider === 'manual')
            ->filter(fn (string $provider): bool => static::isEnabled($provider, $settings))
            ->values()
            ->all();
    }

    public static function checkoutProviders(?array $settings = null): array
    {
        $providers = collect(static::readyProviders($settings))
            ->mapWithKeys(fn (string $provider): array => [$provider => static::label($provider)])
            ->all();

        $providers['manual'] = static::label('manual');

        return $providers;
    }

    public static function defaultProvider(?array $settings = null): string
    {
        return static::readyProviders($settings)[0] ?? 'manual';
    }
}
