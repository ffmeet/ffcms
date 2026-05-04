<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Support\CommerceLabels;
use App\Support\MemberOperationsSummary;
use App\Support\PaymentProviderRegistry;
use App\Support\PaymentLifecycleManager;
use App\Support\SiteTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberOrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view(SiteTheme::view('member.orders-index', 'themes.default.member.orders-index'), [
            'orders' => $user->orders()
                ->with(['payments', 'purchasable'])
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'orderSummaryCards' => MemberOperationsSummary::orderSummary($user),
            'attentionCards' => MemberOperationsSummary::attentionCards($user),
        ]);
    }

    public function pay(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['payments', 'purchasable']);

        if ((float) $order->amount <= 0 || $order->status === 'paid') {
            return redirect()->route('member.orders.index')
                ->with('status', '当前订单无需继续支付。');
        }

        $payment = $order->payments->sortByDesc('id')->first();

        if (! $payment) {
            return redirect()->route('member.orders.index')
                ->with('status', '当前订单还没有可用的支付记录。');
        }

        return view(SiteTheme::view('member.orders-pay', 'themes.default.member.orders-pay'), [
            'order' => $order,
            'payment' => $payment,
            'providers' => $this->availableProviders(),
        ]);
    }

    public function simulatePayment(Request $request, Order $order): RedirectResponse
    {
        abort_if(app()->environment('production'), 404);

        abort_unless($order->user_id === $request->user()->id, 404);

        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:wechat,alipay,paypal,stripe,manual'],
            'action' => ['required', 'string', 'in:paid,failed,closed'],
        ]);

        $payment = $order->payments()->latest('id')->first();

        if (! $payment) {
            return redirect()->route('member.orders.index')
                ->with('status', '当前订单缺少支付记录，暂时无法模拟支付。');
        }

        $payment->update([
            'provider' => $validated['provider'],
        ]);

        match ($validated['action']) {
            'paid' => PaymentLifecycleManager::markPaid($payment, [
                'simulated' => true,
                'entry' => 'member-checkout',
                'provider_label' => CommerceLabels::paymentProvider($validated['provider']),
            ]),
            'failed' => PaymentLifecycleManager::markFailed($payment, [
                'simulated' => true,
                'entry' => 'member-checkout',
                'provider_label' => CommerceLabels::paymentProvider($validated['provider']),
            ]),
            'closed' => PaymentLifecycleManager::markClosed($payment, [
                'simulated' => true,
                'entry' => 'member-checkout',
                'provider_label' => CommerceLabels::paymentProvider($validated['provider']),
            ]),
        };

        return match ($validated['action']) {
            'paid' => redirect()->route('member.orders.index')
                ->with('status', '支付已模拟完成，订单和相关业务状态已经回写。'),
            'failed' => redirect()->route('member.orders.pay', $order)
                ->with('status', '支付失败状态已模拟写入，你可以切换渠道再次尝试。'),
            'closed' => redirect()->route('member.orders.index')
                ->with('status', '支付已关闭，订单已同步更新。'),
        };
    }

    protected function availableProviders(): array
    {
        return PaymentProviderRegistry::checkoutProviders(
            SiteSetting::current()->business_settings ?? []
        );
    }
}
