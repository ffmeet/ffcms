@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '我的订单'])

@section('content')
    @php
        use App\Support\CommerceLabels;
    @endphp

    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'site.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'site.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'site.member.partials.page-header'), [
                'eyebrow' => 'My Orders',
                'title' => '我的订单',
                'description' => '这里承接商品、会员订阅和活动报名产生的订单记录。当前先完成待处理订单承接，下一阶段接正式支付。',
            ])

            <section class="ecms-settings-overview" aria-label="订单状态概览">
                @foreach (($orderSummaryCards ?? []) as $card)
                    <article class="ecms-settings-overview-card">
                        <p class="ecms-settings-overview-label">{{ $card['label'] }}</p>
                        <strong class="ecms-settings-overview-value">{{ $card['value'] }}</strong>
                        <p class="ecms-settings-overview-copy">{{ $card['description'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-4 xl:grid-cols-2">
                @foreach (($attentionCards ?? []) as $card)
                    <article class="rounded-[24px] border border-slate-200 bg-white/96 p-5 shadow-[0_20px_50px_rgba(15,23,42,0.06)]">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-base font-semibold text-slate-900">{{ $card['title'] }}</h2>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ ($card['tone'] ?? 'healthy') === 'warning' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ ($card['tone'] ?? 'healthy') === 'warning' ? '待处理' : '正常' }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $card['summary'] }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (($card['actions'] ?? []) as $action)
                                <a href="{{ $action['url'] }}" class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">
                                    {{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="space-y-4">
                    @forelse ($orders as $order)
                        <article class="grid gap-4 rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,250,252,.92))] px-5 py-4 md:grid-cols-[160px_minmax(0,1fr)_auto] md:items-center">
                            <div class="text-sm font-semibold text-sky-700">{{ $order->order_no }}</div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $order->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ CommerceLabels::orderType($order->order_type) }} · {{ optional($order->created_at)->format('Y-m-d H:i') }}
                                    <span class="text-slate-300"> · </span>
                                    {{ $order->payments->isNotEmpty() ? CommerceLabels::paymentProvider($order->payments->first()->provider) : '无需支付 / 待接入' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-semibold text-slate-900">¥{{ number_format((float) $order->amount, 2) }}</div>
                                <div class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ CommerceLabels::orderStatus($order->status) }}</div>
                                @if ($order->payments->isNotEmpty())
                                    <div class="mt-2 text-xs text-slate-400">支付状态：{{ CommerceLabels::paymentStatus($order->payments->first()->status) }}</div>
                                @endif
                                @if ($order->status !== 'paid' && (float) $order->amount > 0 && $order->payments->isNotEmpty())
                                    <div class="mt-3">
                                        <a href="{{ route('member.orders.pay', $order) }}" class="inline-flex rounded-full bg-slate-950 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-600">继续支付</a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-sm text-slate-500">当前还没有订单记录。</div>
                    @endforelse
                </div>

                <div class="site-pagination mt-6">
                    {{ $orders->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
