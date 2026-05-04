@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '订单支付'])

@section('content')
    @php
        use App\Support\CommerceLabels;
    @endphp

    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'themes.xiaofang.member.partials.page-header'), [
                'eyebrow' => 'Checkout',
                'title' => '订单支付',
                'description' => '这里是前台支付体验页。当前先提供模拟支付，用来完整验证商品、订阅和活动报名的订单流转。',
            ])

            <section class="rounded-[32px] border border-[#efe5db] bg-[linear-gradient(180deg,rgba(255,255,255,.97),rgba(255,247,237,.93),rgba(239,246,255,.88))] p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)] lg:p-8">
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div class="space-y-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-semibold text-[#9a3412]">{{ CommerceLabels::orderType($order->order_type) }}</span>
                                <span class="rounded-full bg-[#f5f5f4] px-3 py-1 text-xs font-semibold text-[#6b6256]">{{ CommerceLabels::orderStatus($order->status) }}</span>
                                <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-semibold text-[#1d4ed8]">{{ $order->order_no }}</span>
                            </div>
                            <h1 class="mt-4 text-4xl font-black tracking-tight text-[#181512]">{{ $order->title }}</h1>
                            <p class="mt-3 text-base leading-8 text-[#5f574f]">
                                当前会基于你选择的渠道写入支付记录，并模拟成功、失败或关闭后的状态回写。
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <article class="rounded-[24px] border border-[#efe5db] bg-white/92 p-5">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#a8a29e]">订单金额</div>
                                <div class="mt-3 text-3xl font-semibold text-[#181512]">¥{{ number_format((float) $order->amount, 2) }}</div>
                            </article>
                            <article class="rounded-[24px] border border-[#efe5db] bg-white/92 p-5">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#a8a29e]">当前支付记录</div>
                                <div class="mt-3 text-lg font-semibold text-[#181512]">{{ CommerceLabels::paymentStatus($payment->status) }}</div>
                                <div class="mt-1 text-sm text-[#6b6256]">{{ CommerceLabels::paymentProvider($payment->provider) }}</div>
                            </article>
                        </div>

                        <div class="rounded-[24px] border border-dashed border-[#d6d3d1] bg-[#fffaf5] p-5">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-[#ecfdf5] px-3 py-1 text-xs font-semibold text-[#047857]">可模拟支付成功</span>
                                <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-semibold text-[#c2410c]">可模拟支付失败</span>
                                <span class="rounded-full bg-[#f5f5f4] px-3 py-1 text-xs font-semibold text-[#6b6256]">可模拟关闭订单</span>
                            </div>
                            <p class="mt-4 text-sm leading-7 text-[#6b6256]">
                                模拟支付成功后，商品订单、会员订阅、活动报名都会自动同步到对应状态，方便你完整测试整个商业化链路。
                            </p>
                        </div>
                    </div>

                    <aside class="space-y-5">
                        <section class="rounded-[28px] border border-[#efe5db] bg-white/96 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.06)]">
                            <h2 class="text-xl font-semibold text-[#181512]">选择支付渠道</h2>
                            <form method="POST" action="{{ route('member.orders.simulate-payment', $order) }}" class="mt-5 space-y-5">
                                @csrf
                                <div class="space-y-3">
                                    @foreach ($providers as $providerKey => $providerLabel)
                                        <label class="flex cursor-pointer items-center justify-between rounded-[20px] border border-[#efe5db] bg-[#fffaf5] px-4 py-4 transition hover:border-[#fdba74] hover:bg-white">
                                            <div>
                                                <div class="font-semibold text-[#181512]">{{ $providerLabel }}</div>
                                                <div class="mt-1 text-sm text-[#6b6256]">
                                                    {{ in_array($providerKey, ['wechat', 'alipay'], true) ? '优先支付通道' : '扩展或兜底通道' }}
                                                </div>
                                            </div>
                                            <input
                                                type="radio"
                                                name="provider"
                                                value="{{ $providerKey }}"
                                                class="h-4 w-4 border-[#d6d3d1] text-[#1d4ed8] focus:ring-[#93c5fd]"
                                                @checked(old('provider', $payment->provider) === $providerKey)
                                            >
                                        </label>
                                    @endforeach
                                </div>

                                <div class="grid gap-3">
                                    <button type="submit" name="action" value="paid" class="w-full rounded-full bg-[#1d4ed8] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1e40af]">模拟支付成功</button>
                                    <button type="submit" name="action" value="failed" class="w-full rounded-full border border-[#fdba74] bg-[#fff7ed] px-5 py-3 text-sm font-semibold text-[#c2410c] transition hover:border-[#fb923c] hover:bg-[#ffedd5]">模拟支付失败</button>
                                    <button type="submit" name="action" value="closed" class="w-full rounded-full border border-[#e7d8c9] bg-white px-5 py-3 text-sm font-semibold text-[#6b6256] transition hover:border-[#fdba74] hover:text-[#9a3412]">关闭支付并返回订单</button>
                                </div>
                            </form>
                        </section>
                    </aside>
                </div>
            </section>
        </div>
    </div>
@endsection
