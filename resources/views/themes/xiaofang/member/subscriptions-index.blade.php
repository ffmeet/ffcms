@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '我的订阅'])

@section('content')
    @php
        use App\Support\CommerceLabels;
    @endphp

    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'themes.xiaofang.member.partials.page-header'), [
                'eyebrow' => 'My Subscriptions',
                'title' => '我的订阅',
                'description' => '这里承接会员套餐订阅申请、生效状态和对应订单。当前先完成记录承接，下一阶段接支付与自动续费。',
            ])

            @include(\App\Support\SiteTheme::view('member.partials.overview-cards', 'themes.xiaofang.member.partials.overview-cards'), [
                'ariaLabel' => '订阅状态概览',
                'cards' => $subscriptionSummaryCards ?? [],
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
                    <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#c2410c]">Subscriptions</span>
                    <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-semibold text-[#1d4ed8]">共 {{ $subscriptions->total() }} 条订阅记录</span>
                </div>
                <div class="space-y-4">
                    @forelse ($subscriptions as $subscription)
                        @php($subscriptionFrontUrl = $subscription->plan?->slug ? route('pricing') : ($subscription->lastOrder?->purchasable instanceof \App\Models\Product ? route('shop.show', $subscription->lastOrder->purchasable->slug) : ($subscription->lastOrder?->purchasable instanceof \App\Models\Event ? route('events.show', $subscription->lastOrder->purchasable->slug) : null)))
                        <article class="grid gap-4 rounded-[24px] border border-[#e5e7eb] bg-white px-5 py-4 md:grid-cols-[minmax(0,1fr)_180px_auto] md:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-semibold text-[#1d4ed8]">{{ CommerceLabels::subscriptionStatus($subscription->status) }}</span>
                                    <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-semibold text-[#9a3412]">{{ $subscription->auto_renew ? '自动续费开启' : '自动续费关闭' }}</span>
                                </div>
                                <h3 class="mt-3 text-lg font-semibold text-[#181512]">
                                    @if ($subscriptionFrontUrl)
                                        <a href="{{ $subscriptionFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $subscription->plan?->name ?? '未关联套餐' }}</a>
                                    @else
                                        {{ $subscription->plan?->name ?? '未关联套餐' }}
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-[#6b6256]">
                                    最近订单：{{ $subscription->lastOrder?->order_no ?? '暂无' }}
                                    @if ($subscription->started_at)
                                        <span class="text-[#d6d3d1]"> · </span>生效 {{ $subscription->started_at->format('Y-m-d') }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-sm text-[#6b6256]">
                                <div>自动续费：{{ $subscription->auto_renew ? '开启' : '关闭' }}</div>
                                <div class="mt-1">到期：{{ $subscription->expires_at?->format('Y-m-d H:i') ?? '待生效' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="inline-flex rounded-full bg-[#f5f5f4] px-3 py-1 text-xs font-semibold text-[#6b6256]">{{ CommerceLabels::subscriptionStatus($subscription->status) }}</div>
                                @if ($subscription->lastOrder && $subscription->lastOrder->status !== 'paid' && (float) $subscription->lastOrder->amount > 0)
                                    <div class="mt-3">
                                        <a href="{{ route('member.orders.pay', $subscription->lastOrder) }}" class="inline-flex rounded-full bg-[#1d4ed8] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#1e40af]">继续支付</a>
                                    </div>
                                @endif
                                @if ($subscriptionFrontUrl)
                                    <div class="mt-3">
                                        <a href="{{ $subscriptionFrontUrl }}" class="inline-flex rounded-full border border-[#e5e7eb] bg-white px-4 py-2 text-xs font-semibold text-[#6b6256] transition hover:border-[#151515] hover:text-[#151515]">查看前台</a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-[#d6d3d1] bg-white p-8 text-sm text-[#78716c]">当前还没有订阅记录。</div>
                    @endforelse
                </div>

                <div class="site-pagination mt-6">
                    {{ $subscriptions->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
