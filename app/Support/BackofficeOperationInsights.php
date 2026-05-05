<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserSubscription;
use Illuminate\Support\HtmlString;

class BackofficeOperationInsights
{
    public static function orderOverview(?Order $order): HtmlString|string
    {
        if (! $order) {
            return '保存订单后，这里会显示支付链路、会员信息和下一步处理建议。';
        }

        $payment = $order->payments()->latest('id')->first();
        $subscription = $order->order_type === 'membership'
            ? UserSubscription::query()->with('plan')->where('last_order_id', $order->id)->latest('id')->first()
            : null;

        $stats = [
            ['label' => '订单状态', 'value' => CommerceLabels::orderStatus((string) $order->status)],
            ['label' => '订单类型', 'value' => CommerceLabels::orderType((string) $order->order_type)],
            ['label' => '会员', 'value' => $order->user?->username ?? '未关联'],
            ['label' => '金额', 'value' => self::money($order->currency, $order->amount)],
            ['label' => '来源', 'value' => (string) data_get($order->meta, 'source', '后台录入')],
            ['label' => '最新支付', 'value' => $payment ? CommerceLabels::paymentStatus((string) $payment->status).' · '.CommerceLabels::paymentProvider((string) $payment->provider) : '未创建'],
            ['label' => '订阅状态', 'value' => $subscription ? CommerceLabels::subscriptionStatus((string) $subscription->status) : '无订阅关联'],
            ['label' => '更新时间', 'value' => optional($order->updated_at)->format('Y-m-d H:i') ?: '未记录'],
        ];

        $actions = [];

        if (! $payment) {
            $actions[] = '当前订单还没有支付记录，适合先补建支付单或回到订单台确认来源。';
        } elseif ($payment->status === 'pending') {
            $actions[] = '支付仍在待处理，建议先进入支付页核对渠道参数、流水号和回调信息。';
        } elseif ($payment->status === 'failed') {
            $actions[] = '最近一次支付失败，建议核对渠道配置或引导会员重新发起支付。';
        } elseif ($payment->status === 'closed') {
            $actions[] = '支付已关闭，如业务仍要继续，建议重新生成一笔新的支付记录。';
        }

        if ($subscription && $subscription->status === 'pending' && $payment?->status === 'paid') {
            $actions[] = '支付已经成功，但订阅仍待生效，适合立即核对订阅开始和到期时间。';
        }

        if ($order->status === 'paid') {
            $actions[] = '订单已完成，当前更适合核对关联业务记录是否已经同步生效。';
        }

        return self::panel('订单链路概览', $stats, $actions, self::riskSection(self::orderRisks($order, $payment, $subscription)).self::historySection($order->meta ?? [], '订单最近变化'));
    }

    public static function paymentOverview(?Payment $payment): HtmlString|string
    {
        if (! $payment) {
            return '保存支付记录后，这里会显示订单链路、回调线索和处理建议。';
        }

        $order = $payment->order()->with('user')->first();
        $subscription = $order?->order_type === 'membership'
            ? UserSubscription::query()->with('plan')->where('last_order_id', $order->id)->latest('id')->first()
            : null;

        $stats = [
            ['label' => '支付状态', 'value' => CommerceLabels::paymentStatus((string) $payment->status)],
            ['label' => '支付渠道', 'value' => CommerceLabels::paymentProvider((string) $payment->provider)],
            ['label' => '订单号', 'value' => $order?->order_no ?? '未关联'],
            ['label' => '订单标题', 'value' => $order?->title ?? '未记录'],
            ['label' => '会员', 'value' => $order?->user?->username ?? '未关联'],
            ['label' => '业务来源', 'value' => (string) data_get($payment->payload, 'entry', data_get($order?->meta ?? [], 'source', '未记录'))],
            ['label' => '渠道流水号', 'value' => $payment->provider_payment_no ?: '暂未回写'],
            ['label' => '订阅状态', 'value' => $subscription ? CommerceLabels::subscriptionStatus((string) $subscription->status) : '无订阅关联'],
        ];

        $actions = [];

        if ($payment->status === 'pending') {
            $actions[] = '支付仍未完成，适合先核对回调地址、渠道参数和会员当前看到的支付入口。';
        }

        if ($payment->status === 'failed') {
            $actions[] = '支付失败后不会自动关闭订单，建议确认是否需要重试或人工关闭。';
        }

        if ($payment->status === 'paid' && $order?->status !== 'paid') {
            $actions[] = '支付已成功但订单未同步完成，建议优先检查状态回写链路。';
        }

        if ($subscription && $subscription->status === 'pending' && $payment->status === 'paid') {
            $actions[] = '支付已完成但订阅仍待生效，建议继续检查会员订阅记录。';
        }

        return self::panel('支付链路概览', $stats, $actions, self::riskSection(self::paymentRisks($payment, $order, $subscription)).self::paymentDiagnostics($payment));
    }

    public static function subscriptionOverview(?UserSubscription $subscription): HtmlString|string
    {
        if (! $subscription) {
            return '保存订阅后，这里会显示最近订单、支付状态和处理建议。';
        }

        $order = $subscription->lastOrder()->with('user')->first();
        $payment = $order?->payments()->latest('id')->first();

        $stats = [
            ['label' => '订阅状态', 'value' => CommerceLabels::subscriptionStatus((string) $subscription->status)],
            ['label' => '会员', 'value' => $subscription->user?->username ?? '未关联'],
            ['label' => '套餐', 'value' => $subscription->plan?->name ?? '未设置'],
            ['label' => '自动续费', 'value' => $subscription->auto_renew ? '已开启' : '未开启'],
            ['label' => '最近订单', 'value' => $order?->order_no ?? '未关联'],
            ['label' => '订单状态', 'value' => $order ? CommerceLabels::orderStatus((string) $order->status) : '未关联'],
            ['label' => '支付状态', 'value' => $payment ? CommerceLabels::paymentStatus((string) $payment->status) : '未创建'],
            ['label' => '有效期', 'value' => self::dateRange($subscription->started_at, $subscription->expires_at)],
        ];

        $actions = [];

        if ($subscription->status === 'pending' && $payment?->status === 'pending') {
            $actions[] = '订阅还卡在待支付阶段，建议先处理最近一笔支付记录。';
        }

        if ($subscription->status === 'pending' && $payment?->status === 'paid') {
            $actions[] = '支付已完成但订阅仍待生效，建议优先检查生效时间和到期时间是否需要补齐。';
        }

        if ($subscription->status === 'cancelled') {
            $actions[] = '当前订阅已取消续费，如会员要恢复服务，建议先确认是否需要补新订单。';
        }

        if ($subscription->status === 'active') {
            $actions[] = '订阅已生效，当前更适合核对套餐权益、到期时间和自动续费状态。';
        }

        return self::panel('订阅链路概览', $stats, $actions, self::riskSection(self::subscriptionRisks($subscription, $order, $payment)).self::historySection($subscription->meta ?? [], '订阅最近变化'));
    }

    protected static function panel(string $title, array $stats, array $actions, ?string $extraSection = null): HtmlString
    {
        $statsHtml = collect($stats)
            ->map(fn (array $item): string => self::statCard($item['label'], $item['value']))
            ->implode('');

        $actionsHtml = collect($actions)
            ->filter(fn (?string $item): bool => filled($item))
            ->map(fn (string $item): string => '<li style="margin:0;color:#475569;line-height:1.6;">'.e($item).'</li>')
            ->implode('');

        if ($actionsHtml === '') {
            $actionsHtml = '<li style="margin:0;color:#475569;line-height:1.6;">当前链路没有明显阻塞点，可以继续核对扩展数据和业务备注。</li>';
        }

        return new HtmlString(
            '<div style="display:flex;flex-direction:column;gap:16px;">'
            .'<div>'
            .'<div style="font-size:1rem;font-weight:700;color:#0f172a;">'.e($title).'</div>'
            .'<div style="margin-top:4px;font-size:0.9rem;color:#64748b;">把当前记录的上下游链路先整理清楚，再决定下一步处理动作。</div>'
            .'</div>'
            .'<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">'.$statsHtml.'</div>'
            .'<div style="border:1px solid #e2e8f0;border-radius:18px;padding:16px 18px;background:#fff7ed;">'
            .'<div style="font-size:0.76rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#c2410c;">处理建议</div>'
            .'<ul style="display:flex;flex-direction:column;gap:8px;margin:12px 0 0;padding-left:18px;">'.$actionsHtml.'</ul>'
            .'</div>'
            .($extraSection ? $extraSection : '')
            .'</div>'
        );
    }

    protected static function paymentDiagnostics(Payment $payment): string
    {
        $history = self::historySection($payment->payload ?? [], '支付最近变化');
        $receipts = self::webhookSection($payment->payload ?? []);

        return $history.$receipts;
    }

    protected static function riskSection(array $risks): string
    {
        $items = collect($risks)
            ->filter(fn (?string $item): bool => filled($item))
            ->take(3)
            ->map(fn (string $item): string => '<li style="margin:0;color:#7c2d12;line-height:1.6;">'.e($item).'</li>')
            ->implode('');

        if ($items === '') {
            return '';
        }

        return '<div style="border:1px solid #fed7aa;border-radius:18px;padding:16px 18px;background:#fff7ed;">'
            .'<div style="font-size:0.76rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#c2410c;">风险提示</div>'
            .'<ul style="display:flex;flex-direction:column;gap:8px;margin:12px 0 0;padding-left:18px;">'.$items.'</ul>'
            .'</div>';
    }

    protected static function historySection(array $data, string $title): string
    {
        $entries = collect($data['history'] ?? [])
            ->filter(fn (mixed $item): bool => is_array($item))
            ->take(4)
            ->map(function (array $item): string {
                $headline = collect([
                    data_get($item, 'event'),
                    data_get($item, 'status'),
                ])->filter()->implode(' · ');

                $meta = collect([
                    data_get($item, 'source'),
                    data_get($item, 'provider'),
                    data_get($item, 'order_no'),
                    data_get($item, 'entry'),
                ])->filter()->implode(' / ');

                return '<li style="margin:0;display:flex;flex-direction:column;gap:4px;">'
                    .'<div style="font-size:0.92rem;font-weight:600;color:#0f172a;">'.e($headline ?: '状态更新').'</div>'
                    .'<div style="font-size:0.82rem;color:#64748b;">'.e((string) data_get($item, 'at', '未记录')).'</div>'
                    .($meta !== '' ? '<div style="font-size:0.84rem;color:#475569;">'.e($meta).'</div>' : '')
                    .(filled(data_get($item, 'note')) ? '<div style="font-size:0.84rem;color:#475569;">'.e((string) data_get($item, 'note')).'</div>' : '')
                    .'</li>';
            })
            ->implode('');

        if ($entries === '') {
            $entries = '<li style="margin:0;color:#64748b;line-height:1.6;">还没有操作留痕，后续后台处理和回调接入会自动沉淀到这里。</li>';
        }

        return '<div style="border:1px solid #e2e8f0;border-radius:18px;padding:16px 18px;background:#ffffff;">'
            .'<div style="font-size:0.76rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#334155;">'.e($title).'</div>'
            .'<ul style="display:flex;flex-direction:column;gap:12px;margin:12px 0 0;padding-left:18px;">'.$entries.'</ul>'
            .'</div>';
    }

    protected static function webhookSection(array $data): string
    {
        $entries = collect($data['webhook_receipts'] ?? [])
            ->filter(fn (mixed $item): bool => is_array($item))
            ->take(3)
            ->map(function (array $item): string {
                return '<li style="margin:0;display:flex;flex-direction:column;gap:4px;">'
                    .'<div style="font-size:0.92rem;font-weight:600;color:#0f172a;">'.e((string) data_get($item, 'provider', 'webhook')).' · '.e((string) data_get($item, 'status', 'received')).'</div>'
                    .'<div style="font-size:0.82rem;color:#64748b;">'.e((string) data_get($item, 'at', '未记录')).'</div>'
                    .'<div style="font-size:0.84rem;color:#475569;">'.e(collect([
                        data_get($item, 'order_no'),
                        data_get($item, 'provider_payment_no'),
                        data_get($item, 'note'),
                    ])->filter()->implode(' / ')).'</div>'
                    .'</li>';
            })
            ->implode('');

        if ($entries === '') {
            return '';
        }

        return '<div style="border:1px solid #e2e8f0;border-radius:18px;padding:16px 18px;background:#f8fafc;">'
            .'<div style="font-size:0.76rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#334155;">最近 webhook 回写</div>'
            .'<ul style="display:flex;flex-direction:column;gap:12px;margin:12px 0 0;padding-left:18px;">'.$entries.'</ul>'
            .'</div>';
    }

    protected static function statCard(string $label, mixed $value): string
    {
        return '<div style="border:1px solid #e2e8f0;border-radius:18px;padding:14px 16px;background:#f8fafc;">'
            .'<div style="font-size:0.74rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">'.e($label).'</div>'
            .'<div style="margin-top:6px;font-size:0.95rem;font-weight:600;line-height:1.5;color:#0f172a;">'.e((string) $value).'</div>'
            .'</div>';
    }

    protected static function money(?string $currency, mixed $amount): string
    {
        $prefix = filled($currency) ? strtoupper((string) $currency).' ' : '';

        return $prefix.number_format((float) $amount, 2, '.', '');
    }

    protected static function dateRange(mixed $start, mixed $end): string
    {
        $startLabel = optional($start)->format('Y-m-d H:i') ?: '未开始';
        $endLabel = optional($end)->format('Y-m-d H:i') ?: '未设置';

        return $startLabel.' -> '.$endLabel;
    }

    protected static function orderRisks(Order $order, ?Payment $payment, ?UserSubscription $subscription): array
    {
        $risks = [];

        if ($order->status === 'paid' && $payment && $payment->status !== 'paid') {
            $risks[] = '订单已经是已支付，但最新支付记录不是已支付，状态可能已经出现分叉。';
        }

        if ($order->order_type === 'membership' && $order->status === 'paid' && $subscription && $subscription->status !== 'active') {
            $risks[] = '会员订单已完成，但订阅还没有生效，会员权益可能没有及时发放。';
        }

        if (! $payment && (float) $order->amount > 0 && $order->status === 'pending') {
            $risks[] = '订单金额大于 0 但没有支付记录，这笔交易可能还没有真正进入支付链路。';
        }

        return $risks;
    }

    protected static function paymentRisks(Payment $payment, ?Order $order, ?UserSubscription $subscription): array
    {
        $risks = [];

        if ($payment->status === 'paid' && $order?->status !== 'paid') {
            $risks[] = '支付已经成功，但订单还不是已支付，回写链路可能有遗漏。';
        }

        if ($payment->status === 'failed' && $order?->status === 'paid') {
            $risks[] = '支付记录显示失败，但订单已经完成，建议核对是否曾被人工回写。';
        }

        if ($payment->status === 'paid' && $order?->order_type === 'membership' && $subscription && $subscription->status !== 'active') {
            $risks[] = '会员支付已完成，但订阅没有同步生效，可能影响会员即时开通。';
        }

        return $risks;
    }

    protected static function subscriptionRisks(UserSubscription $subscription, ?Order $order, ?Payment $payment): array
    {
        $risks = [];

        if ($subscription->status === 'active' && blank($subscription->expires_at)) {
            $risks[] = '订阅已经生效，但到期时间为空，后续续费和失效判断可能不准确。';
        }

        if ($subscription->status === 'pending' && $payment?->status === 'paid') {
            $risks[] = '订阅仍待生效，但支付已经完成，会员可能已经付款却暂时拿不到权益。';
        }

        if ($subscription->status === 'cancelled' && $order?->status === 'paid') {
            $risks[] = '最近订单已支付，但订阅处于已取消续费，建议确认是手动停用还是链路回写异常。';
        }

        return $risks;
    }
}
