<?php

namespace App\Support;

use App\Models\SiteSetting;

class RouteRuleManager
{
    public const PUBLIC_ENTRIES = [
        'search' => ['label' => '搜索', 'route' => 'search'],
        'pricing' => ['label' => '会员计划', 'route' => 'pricing'],
        'events' => ['label' => '活动', 'route' => 'events.index'],
        'shop' => ['label' => '商店', 'route' => 'shop.index'],
        'member' => ['label' => '会员中心', 'route' => 'member.dashboard'],
        'admin' => ['label' => '后台', 'url' => '/admin'],
        'login' => ['label' => '登录', 'route' => 'login'],
        'register' => ['label' => '注册', 'route' => 'register'],
        'home' => ['label' => '前台首页', 'route' => 'site.home'],
    ];

    public const MEMBER_ENTRIES = [
        'dashboard' => ['label' => '总览', 'route' => 'member.dashboard'],
        'posts' => ['label' => '我的稿件', 'route' => 'member.posts.index'],
        'comments' => ['label' => '我的评论', 'route' => 'member.comments.index'],
        'orders' => ['label' => '我的订单', 'route' => 'member.orders.index'],
        'subscriptions' => ['label' => '我的订阅', 'route' => 'member.subscriptions.index'],
        'create_post' => ['label' => '发布新稿件', 'route' => 'member.posts.create'],
        'profile' => ['label' => '修改资料', 'route' => 'member.profile.edit'],
        'activity_center' => ['label' => '活动中心', 'route' => 'member.activity.center'],
        'activities' => ['label' => '我的活动', 'route' => 'member.activities.index'],
    ];

    public static function publicEntries(?array $settings = null): array
    {
        $configured = data_get(static::settings($settings), 'business_settings.route_settings.public_entries', []);

        return collect(self::PUBLIC_ENTRIES)
            ->map(function (array $definition, string $key) use ($configured): array {
                $entry = $configured[$key] ?? [];

                return [
                    'key' => $key,
                    'label' => (string) ($entry['label'] ?? $definition['label']),
                    'url' => static::resolveUrl($entry['url'] ?? null, $definition),
                    'route' => $definition['route'] ?? null,
                ];
            })
            ->all();
    }

    public static function publicEntry(string $key, ?array $settings = null): array
    {
        return static::publicEntries($settings)[$key] ?? [
            'key' => $key,
            'label' => $key,
            'url' => '#',
            'route' => null,
        ];
    }

    public static function memberEntries(?array $settings = null): array
    {
        $configured = data_get(static::settings($settings), 'business_settings.route_settings.member_entries', []);

        return collect(self::MEMBER_ENTRIES)
            ->map(function (array $definition, string $key) use ($configured): array {
                $entry = $configured[$key] ?? [];

                return [
                    'key' => $key,
                    'label' => (string) ($entry['label'] ?? $definition['label']),
                    'url' => static::resolveUrl($entry['url'] ?? null, $definition),
                    'route' => $definition['route'] ?? null,
                ];
            })
            ->all();
    }

    public static function memberEntry(string $key, ?array $settings = null): array
    {
        return static::memberEntries($settings)[$key] ?? [
            'key' => $key,
            'label' => $key,
            'url' => '#',
            'route' => null,
        ];
    }

    public static function summaryCards(?array $settings = null): array
    {
        $public = static::publicEntries($settings);
        $member = static::memberEntries($settings);

        return [
            [
                'label' => '公开入口',
                'value' => (string) count($public),
                'description' => collect(['search', 'pricing', 'events', 'shop'])->map(fn (string $key) => $public[$key]['label'])->implode(' / '),
            ],
            [
                'label' => '会员入口',
                'value' => (string) count($member),
                'description' => collect(['dashboard', 'posts', 'orders', 'subscriptions'])->map(fn (string $key) => $member[$key]['label'])->implode(' / '),
            ],
            [
                'label' => '后台与认证',
                'value' => '3',
                'description' => collect(['login', 'register', 'admin'])->map(fn (string $key) => $public[$key]['label'])->implode(' / '),
            ],
        ];
    }

    protected static function settings(?array $settings = null): array
    {
        return $settings ?? SiteSetting::current()->toArray();
    }

    protected static function resolveUrl(mixed $configuredUrl, array $definition): string
    {
        if (is_string($configuredUrl) && trim($configuredUrl) !== '') {
            return trim($configuredUrl);
        }

        if (isset($definition['route'])) {
            return route($definition['route']);
        }

        return (string) ($definition['url'] ?? '#');
    }
}
