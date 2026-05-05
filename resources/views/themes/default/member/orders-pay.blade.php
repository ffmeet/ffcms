@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '订单支付'])

@section('content')
    @php
        use App\Support\CommerceLabels;
    @endphp

    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'site.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'site.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'site.member.partials.page-header'), [
                'eyebrow' => 'Checkout',
                'title' => '订单支付',
                'description' => '这里是前台支付体验页。当前先提供模拟支付，用来完整验证商品、订阅和活动报名的订单流转。',
            ])

            <section class="site-feature-shell p-6 lg:p-8">
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div class="space-y-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="site-chip site-chip--brand">{{ CommerceLabels::orderType($order->order_type) }}</span>
                                <span class="site-chip site-chip--slate">{{ CommerceLabels::orderStatus($order->status) }}</span>
                                <span class="site-chip site-chip--slate">{{ $order->order_no }}</span>
                            </div>
                            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950">{{ $order->title }}</h1>
                            <p class="mt-3 text-base leading-8 text-slate-600">
                                当前会基于你选择的渠道写入支付记录，并模拟成功、失败或关闭后的状态回写。
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">订单金额</div>
                                <div class="mt-3 text-3xl font-semibold text-slate-950">¥{{ number_format((float) $order->amount, 2) }}</div>
                            </article>
                            <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">当前支付记录</div>
                                <div class="mt-3 text-lg font-semibold text-slate-950">{{ CommerceLabels::paymentStatus($payment->status) }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ CommerceLabels::paymentProvider($payment->provider) }}</div>
                            </article>
                        </div>

                        <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-5">
                            <div class="flex flex-wrap gap-2">
                                <span class="site-chip site-chip--emerald">可模拟支付成功</span>
                                <span class="site-chip site-chip--amber">可模拟支付失败</span>
                                <span class="site-chip site-chip--slate">可模拟关闭订单</span>
                            </div>
                            <p class="mt-4 text-sm leading-7 text-slate-500">
                                模拟支付成功后，商品订单、会员订阅、活动报名都会自动同步到对应状态，方便你完整测试整个商业化链路。
                            </p>
                        </div>
                    </div>

                    <aside class="space-y-5">
                        <section class="rounded-[28px] border border-slate-200 bg-white/96 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.06)]">
                            <h2 class="text-xl font-semibold text-slate-950">选择支付渠道</h2>
                            <form method="POST" action="{{ route('member.orders.simulate-payment', $order) }}" class="mt-5 space-y-5">
                                @csrf
                                <div class="space-y-3">
                                    @foreach ($providers as $providerKey => $providerLabel)
                                        <label class="flex cursor-pointer items-center justify-between rounded-[20px] border border-slate-200 bg-slate-50/80 px-4 py-4 transition hover:border-slate-900 hover:bg-white">
                                            <div>
                                                <div class="font-semibold text-slate-900">{{ $providerLabel }}</div>
                                                <div class="mt-1 text-sm text-slate-500">
                                                    {{ in_array($providerKey, ['wechat', 'alipay'], true) ? '优先支付通道' : '扩展或兜底通道' }}
                                                </div>
                                            </div>
                                            <input
                                                type="radio"
                                                name="provider"
                                                value="{{ $providerKey }}"
                                                class="h-4 w-4 border-slate-300 text-slate-950 focus:ring-slate-950"
                                                @checked(old('provider', $payment->provider) === $providerKey)
                                            >
                                        </label>
                                    @endforeach
                                </div>

                                <div class="grid gap-3">
                                    <button type="submit" name="action" value="paid" class="w-full rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">模拟支付成功</button>
                                    <button type="submit" name="action" value="failed" class="w-full rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">模拟支付失败</button>
                                    <button type="submit" name="action" value="closed" class="w-full rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">关闭支付并返回订单</button>
                                </div>
                            </form>
                        </section>
                    </aside>
                </div>
            </section>
        </div>
    </div>
@endsection
