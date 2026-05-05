<?php

namespace App\Support;

use App\Filament\Pages\PaymentCenter;
use App\Filament\Pages\RouteRuleCenter;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\User;

class MemberOperationsSummary
{
    public static function dashboardMetrics(User $user): array
    {
        return [
            'drafts' => $user->posts()->where('status', 'draft')->count(),
            'pending_posts' => $user->posts()->where('status', 'pending')->count(),
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'comments' => $user->comments()->count(),
            'orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->whereIn('status', ['pending', 'processing'])->count(),
            'closed_orders' => $user->orders()->where('status', 'closed')->count(),
            'subscriptions' => $user->subscriptions()->count(),
            'pending_subscriptions' => $user->subscriptions()->whereIn('status', ['pending', 'inactive'])->count(),
            'active_subscriptions' => $user->subscriptions()->where('status', 'active')->count(),
            'registrations' => $user->eventRegistrations()->count(),
        ];
    }

    public static function attentionCards(User $user): array
    {
        $metrics = static::dashboardMetrics($user);
        $memberEntries = RouteRuleManager::memberEntries();
        $cards = [];

        if ($metrics['pending_orders'] > 0) {
            $cards[] = [
                'title' => '还有待支付订单',
                'summary' => '当前有 '.$metrics['pending_orders'].' 笔订单仍处于待支付或处理中，建议优先回到订单页继续支付或切换渠道。',
                'tone' => 'warning',
                'actions' => [
                    ['label' => '查看我的订单', 'url' => $memberEntries['orders']['url']],
                ],
            ];
        }

        if ($metrics['pending_subscriptions'] > 0) {
            $cards[] = [
                'title' => '订阅还未完全生效',
                'summary' => '当前有 '.$metrics['pending_subscriptions'].' 条订阅处于待支付或待生效状态，适合联动检查最近订单和支付状态。',
                'tone' => 'warning',
                'actions' => [
                    ['label' => '查看我的订阅', 'url' => $memberEntries['subscriptions']['url']],
                    ['label' => '查看我的订单', 'url' => $memberEntries['orders']['url']],
                ],
            ];
        }

        if ($metrics['closed_orders'] > 0) {
            $cards[] = [
                'title' => '最近有已关闭订单',
                'summary' => '当前已有 '.$metrics['closed_orders'].' 笔订单被关闭，适合回看关闭原因并决定是否重新下单。',
                'tone' => 'neutral',
                'actions' => [
                    ['label' => '查看我的订单', 'url' => $memberEntries['orders']['url']],
                ],
            ];
        }

        if ($user->is_staff_account) {
            $cards[] = [
                'title' => '后台快捷处理',
                'summary' => '你当前具备后台访问能力，可以直接跳转支付记录、支付中心或规则中心继续排查和处理。',
                'tone' => 'healthy',
                'actions' => [
                    ['label' => '支付记录', 'url' => PaymentResource::getUrl()],
                    ['label' => '支付中心', 'url' => PaymentCenter::getUrl()],
                    ['label' => '规则中心', 'url' => RouteRuleCenter::getUrl()],
                ],
            ];
        }

        if ($cards === []) {
            $cards[] = [
                'title' => '当前没有明显阻塞',
                'summary' => '订单、订阅和稿件状态目前比较平稳，可以继续投稿、报名活动或处理新的商业化流程。',
                'tone' => 'healthy',
                'actions' => [
                    ['label' => '发布新稿件', 'url' => $memberEntries['create_post']['url']],
                ],
            ];
        }

        return $cards;
    }

    public static function orderSummary(User $user): array
    {
        return [
            [
                'label' => '待支付 / 处理中',
                'value' => (string) $user->orders()->whereIn('status', ['pending', 'processing'])->count(),
                'description' => '需要继续支付或等待支付结果回写的订单。',
            ],
            [
                'label' => '已支付',
                'value' => (string) $user->orders()->where('status', 'paid')->count(),
                'description' => '已经完成支付并同步回写到业务流程的订单。',
            ],
            [
                'label' => '已关闭',
                'value' => (string) $user->orders()->where('status', 'closed')->count(),
                'description' => '支付关闭或交易终止的订单，适合回看原因后重试。',
            ],
        ];
    }

    public static function subscriptionSummary(User $user): array
    {
        return [
            [
                'label' => '待生效',
                'value' => (string) $user->subscriptions()->whereIn('status', ['pending', 'inactive'])->count(),
                'description' => '通常意味着订单还没支付完成，或订阅还在等待回写。',
            ],
            [
                'label' => '生效中',
                'value' => (string) $user->subscriptions()->where('status', 'active')->count(),
                'description' => '已经可正常享受会员权益的订阅记录。',
            ],
            [
                'label' => '已取消 / 关闭',
                'value' => (string) $user->subscriptions()->whereIn('status', ['cancelled'])->count(),
                'description' => '可以结合最近订单一起判断是否需要重新订阅。',
            ],
        ];
    }
}
