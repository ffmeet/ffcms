<?php

namespace App\Support;

class CommerceLabels
{
    public static function orderType(string $type): string
    {
        return match ($type) {
            'membership' => '会员订阅',
            'event' => '活动报名',
            default => '商品购买',
        };
    }

    public static function orderStatus(string $status): string
    {
        return match ($status) {
            'paid' => '已支付',
            'cancelled' => '已取消',
            'refunded' => '已退款',
            'closed' => '已关闭',
            default => '待支付',
        };
    }

    public static function paymentProvider(string $provider): string
    {
        return PaymentProviderRegistry::label($provider);
    }

    public static function paymentStatus(string $status): string
    {
        return match ($status) {
            'processing' => '支付中',
            'paid' => '已支付',
            'failed' => '失败',
            'closed' => '已关闭',
            default => '待发起',
        };
    }

    public static function subscriptionStatus(string $status): string
    {
        return match ($status) {
            'active' => '生效中',
            'pending' => '待生效',
            'cancelled' => '已取消续费',
            'expired' => '已过期',
            'inactive' => '未生效',
            default => '待处理',
        };
    }

    public static function registrationStatus(string $status): string
    {
        return match ($status) {
            'approved' => '已确认',
            'cancelled' => '已取消',
            default => '待确认',
        };
    }

    public static function registrationPaymentStatus(string $status): string
    {
        return match ($status) {
            'paid' => '已支付',
            'not_required' => '无需支付',
            'closed' => '已关闭',
            default => '待支付',
        };
    }
}
