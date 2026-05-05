<?php

namespace App\Support;

use App\Filament\Pages\PaymentCenter;
use App\Filament\Pages\RouteRuleCenter;
use App\Filament\Pages\UploadDiagnosticsCenter;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Schema;

class OperationalHealth
{
    public static function overview(): array
    {
        $settings = SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $uploadSummary = UploadLogReader::summary();
        $readyProviders = PaymentProviderRegistry::readyProviders($businessSettings);
        $enabledProviders = PaymentProviderRegistry::enabledProviders($businessSettings);
        $publicEntries = RouteRuleManager::publicEntries($settings->toArray());

        return [
            [
                'eyebrow' => 'Uploads',
                'title' => '上传链路',
                'status' => $uploadSummary['errors'] > 0 ? 'warning' : 'healthy',
                'summary' => $uploadSummary['errors'] > 0
                    ? '最近有上传错误或警告，优先检查 PHP 上传限制、媒体写入和前台封面流程。'
                    : '最近没有明显上传异常，前台封面和媒体链路状态正常。',
                'metrics' => [
                    ['label' => '最近记录', 'value' => (string) $uploadSummary['total']],
                    ['label' => '错误 / 警告', 'value' => (string) ($uploadSummary['errors'] + $uploadSummary['warnings'])],
                    ['label' => '最近异常', 'value' => $uploadSummary['latest_failed_at'] ?? '暂无'],
                ],
                'actions' => [
                    ['label' => '查看全部上传诊断', 'url' => UploadDiagnosticsCenter::getUrl()],
                    ['label' => '只看警告', 'url' => UploadDiagnosticsCenter::getUrl().'?level=warning'],
                ],
            ],
            [
                'eyebrow' => 'Payments',
                'title' => '支付链路',
                'status' => $enabledProviders !== [] && $readyProviders === [] ? 'warning' : 'healthy',
                'summary' => $enabledProviders === []
                    ? '当前还没有启用在线渠道，前台会统一回退到人工支付。'
                    : ($readyProviders === []
                        ? '虽然已经启用在线渠道，但关键参数还没补齐，前台结算会回退到人工支付。'
                        : '至少有一条在线支付渠道已经就绪，前台下单会优先命中这条渠道。'),
                'metrics' => [
                    ['label' => '已启用渠道', 'value' => (string) count($enabledProviders)],
                    ['label' => '可结算渠道', 'value' => (string) count($readyProviders)],
                    ['label' => '待支付订单', 'value' => static::pendingOrderCount()],
                ],
                'actions' => [
                    ['label' => '进入支付中心', 'url' => PaymentCenter::getUrl()],
                    ['label' => '查看支付记录', 'url' => url('/admin/payments')],
                ],
            ],
            [
                'eyebrow' => 'Routes',
                'title' => '入口规则',
                'status' => 'healthy',
                'summary' => '前台头部、登录注册、后台入口和会员中心导航都已经并入统一规则层，避免多主题入口漂移。',
                'metrics' => [
                    ['label' => '搜索入口', 'value' => $publicEntries['search']['label']],
                    ['label' => '会员入口', 'value' => $publicEntries['member']['label']],
                    ['label' => '后台入口', 'value' => $publicEntries['admin']['label']],
                ],
                'actions' => [
                    ['label' => '进入规则中心', 'url' => RouteRuleCenter::getUrl()],
                ],
            ],
            [
                'eyebrow' => 'Members',
                'title' => '会员与订单',
                'status' => static::pendingSubscriptionCount() > 0 ? 'warning' : 'healthy',
                'summary' => static::pendingSubscriptionCount() > 0
                    ? '目前还有待支付或待生效订阅，适合联动支付中心一起检查。'
                    : '会员订阅和会员中心入口当前没有明显阻塞。',
                'metrics' => [
                    ['label' => '待生效订阅', 'value' => static::pendingSubscriptionCount()],
                    ['label' => '待支付订单', 'value' => static::pendingOrderCount()],
                    ['label' => '已关闭订单', 'value' => static::closedOrderCount()],
                ],
                'actions' => [
                    ['label' => '查看会员入口规则', 'url' => RouteRuleCenter::getUrl()],
                    ['label' => '查看支付中心', 'url' => PaymentCenter::getUrl()],
                ],
            ],
        ];
    }

    protected static function pendingOrderCount(): string
    {
        if (! Schema::hasTable('orders')) {
            return '0';
        }

        return (string) Order::query()
            ->whereIn('status', ['pending', 'processing'])
            ->count();
    }

    protected static function closedOrderCount(): string
    {
        if (! Schema::hasTable('orders')) {
            return '0';
        }

        return (string) Order::query()
            ->where('status', 'closed')
            ->count();
    }

    protected static function pendingSubscriptionCount(): string
    {
        if (! Schema::hasTable('user_subscriptions')) {
            return '0';
        }

        return (string) UserSubscription::query()
            ->whereIn('status', ['pending', 'inactive'])
            ->count();
    }
}
