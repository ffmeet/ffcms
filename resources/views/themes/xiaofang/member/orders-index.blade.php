@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '我的订单'])

@section('content')
    @php
        use App\Support\CommerceLabels;
    @endphp

    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'themes.xiaofang.member.partials.page-header'), [
                'eyebrow' => 'My Orders',
                'title' => '我的订单',
                'description' => '这里承接商品、会员订阅和活动报名产生的订单记录。当前先完成待处理订单承接，下一阶段接正式支付。',
            ])

            @include(\App\Support\SiteTheme::view('member.partials.overview-cards', 'themes.xiaofang.member.partials.overview-cards'), [
                'ariaLabel' => '订单状态概览',
                'cards' => $orderSummaryCards ?? [],
            ])

            <section class="grid gap-4 xl:grid-cols-2">
                @foreach (($attentionCards ?? []) as $card)
                    <article class="rounded-[1.6rem] border border-[#e5e7eb] bg-white p-5 shadow-[0_18px_46px_rgba(15,23,42,0.05)]">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-base font-black text-[#181512]">{{ $card['title'] }}</h2>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ ($card['tone'] ?? 'healthy') === 'warning' ? 'bg-[#fff7ed] text-[#c2410c]' : 'bg-[#eff6ff] text-[#1d4ed8]' }}">
                                {{ ($card['tone'] ?? 'healthy') === 'warning' ? '待处理' : '正常' }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-[#5f574f]">{{ $card['summary'] }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (($card['actions'] ?? []) as $action)
                                <a href="{{ $action['url'] }}" class="inline-flex rounded-full border border-[#e5e7eb] bg-white px-4 py-2 text-xs font-semibold text-[#6b6256] transition hover:border-[#151515] hover:text-[#151515]">
                                    {{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="rounded-[32px] border border-[#e5e7eb] bg-white p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="mb-5 flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#c2410c]">Orders</span>
                    <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-semibold text-[#1d4ed8]">共 {{ $orders->total() }} 条订单记录</span>
                </div>
                <div class="space-y-4">
                    @forelse ($orders as $order)
                        @php($orderFrontUrl = $order->purchasable instanceof \App\Models\Product ? route('shop.show', $order->purchasable->slug) : ($order->purchasable instanceof \App\Models\Event ? route('events.show', $order->purchasable->slug) : ($order->purchasable instanceof \App\Models\MembershipPlan ? route('pricing') : null)))
                        <article class="grid gap-4 rounded-[24px] border border-[#e5e7eb] bg-white px-5 py-4 md:grid-cols-[160px_minmax(0,1fr)_auto] md:items-center">
                            <div class="text-sm font-semibold text-[#1d4ed8]">{{ $order->order_no }}</div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-semibold text-[#9a3412]">{{ CommerceLabels::orderType($order->order_type) }}</span>
                                    <span class="rounded-full bg-[#f5f5f4] px-3 py-1 text-xs font-semibold text-[#6b6256]">{{ CommerceLabels::orderStatus($order->status) }}</span>
                                </div>
                                <h3 class="mt-3 text-lg font-semibold text-[#181512]">
                                    @if ($orderFrontUrl)
                                        <a href="{{ $orderFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $order->title }}</a>
                                    @else
                                        {{ $order->title }}
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-[#6b6256]">
                                    {{ CommerceLabels::orderType($order->order_type) }} · {{ optional($order->created_at)->format('Y-m-d H:i') }}
                                    <span class="text-[#d6d3d1]"> · </span>
                                    {{ $order->payments->isNotEmpty() ? CommerceLabels::paymentProvider($order->payments->first()->provider) : '无需支付 / 待接入' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-semibold text-[#181512]">¥{{ number_format((float) $order->amount, 2) }}</div>
                                <div class="mt-2 inline-flex rounded-full bg-[#f5f5f4] px-3 py-1 text-xs font-semibold text-[#6b6256]">{{ CommerceLabels::orderStatus($order->status) }}</div>
                                @if ($order->payments->isNotEmpty())
                                    <div class="mt-2 text-xs text-[#78716c]">支付状态：{{ CommerceLabels::paymentStatus($order->payments->first()->status) }}</div>
                                @endif
                                @if ($order->status !== 'paid' && (float) $order->amount > 0 && $order->payments->isNotEmpty())
                                    <div class="mt-3">
                                        <a href="{{ route('member.orders.pay', $order) }}" class="inline-flex rounded-full bg-[#1d4ed8] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#1e40af]">继续支付</a>
                                    </div>
                                @endif
                                @if ($orderFrontUrl)
                                    <div class="mt-3">
                                        <a href="{{ $orderFrontUrl }}" class="inline-flex rounded-full border border-[#e5e7eb] bg-white px-4 py-2 text-xs font-semibold text-[#6b6256] transition hover:border-[#151515] hover:text-[#151515]">查看前台</a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-[#d6d3d1] bg-white p-8 text-sm text-[#78716c]">当前还没有订单记录。</div>
                    @endforelse
                </div>

                <div class="site-pagination mt-6">
                    {{ $orders->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
