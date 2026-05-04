@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '活动中心'])

@section('content')
    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'themes.xiaofang.member.partials.page-header'), [
                'eyebrow' => 'Activity Center',
                'title' => '活动中心',
                'description' => '这里承接活动报名、订单处理和订阅状态，让会员前台逐步形成真实的交易与活动中心。',
                'actions' => [
                    ['label' => '查看我的活动', 'url' => route('member.activities.index')],
                ],
            ])

            <div class="grid gap-5 xl:grid-cols-3">
                <article class="rounded-[30px] border border-[#efe5db] bg-[#fffaf5] p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-[#78716c]">活动报名</p>
                    <p class="mt-3 text-4xl font-semibold text-[#181512]">{{ $activitySummary['event_registrations'] }}</p>
                </article>
                <article class="rounded-[30px] border border-[#dbeafe] bg-[#eff6ff] p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-[#1d4ed8]">待支付订单</p>
                    <p class="mt-3 text-4xl font-semibold text-[#1d4ed8]">{{ $activitySummary['pending_orders'] }}</p>
                </article>
                <article class="rounded-[30px] border border-[#d1fae5] bg-[#ecfdf5] p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-[#047857]">生效订阅</p>
                    <p class="mt-3 text-4xl font-semibold text-[#047857]">{{ $activitySummary['active_subscriptions'] }}</p>
                </article>
            </div>

            <section class="rounded-[32px] border border-[#efe5db] bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold text-[#181512]">活动面板</h2>
                        <p class="mt-1 text-sm text-[#6b6256]">这里已经开始承接即将开始的活动、最近订单和订阅状态。</p>
                    </div>
                    <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center rounded-[18px] bg-[#eff6ff] px-4 py-2.5 text-sm font-semibold text-[#1d4ed8] transition hover:bg-[#dbeafe]">查看会员计划</a>
                </div>

                <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                    <article class="rounded-[24px] border border-[#efe5db] bg-[#fffaf5] p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-lg font-semibold text-[#181512]">即将开始的活动</h3>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-[#a8a29e]">Upcoming</span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse ($upcomingRegistrations as $registration)
                                @php($upcomingEventFrontUrl = $registration->event?->slug ? route('events.show', $registration->event->slug) : null)
                                <article class="rounded-[18px] border border-[#efe5db] bg-white px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h4 class="text-base font-semibold text-[#181512]">
                                                @if ($upcomingEventFrontUrl)
                                                    <a href="{{ $upcomingEventFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $registration->event?->title ?? '活动已删除' }}</a>
                                                @else
                                                    {{ $registration->event?->title ?? '活动已删除' }}
                                                @endif
                                            </h4>
                                            <p class="mt-1 text-sm text-[#6b6256]">
                                                {{ $registration->event?->location ?: '线上 / 待定地点' }}
                                                @if ($registration->event?->starts_at)
                                                    <span class="text-[#d6d3d1]"> · {{ $registration->event->starts_at->format('Y-m-d H:i') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <span class="rounded-full {{ $registration->status === 'approved' ? 'bg-[#ecfdf5] text-[#047857]' : 'bg-[#fff7ed] text-[#c2410c]' }} px-3 py-1 text-xs font-semibold">{{ $registration->status === 'approved' ? '已确认' : '待支付' }}</span>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-[18px] border border-dashed border-[#d6d3d1] bg-white/80 p-5 text-sm text-[#78716c]">当前没有即将开始的活动报名。</div>
                            @endforelse
                        </div>
                    </article>

                    <div class="space-y-4">
                        <article class="rounded-[24px] border border-[#efe5db] bg-[#fffaf5] p-5">
                            <h3 class="text-lg font-semibold text-[#181512]">最近订单</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($recentOrders as $order)
                                    @php($recentOrderFrontUrl = $order->purchasable instanceof \App\Models\Product ? route('shop.show', $order->purchasable->slug) : ($order->purchasable instanceof \App\Models\Event ? route('events.show', $order->purchasable->slug) : ($order->purchasable instanceof \App\Models\MembershipPlan ? route('pricing') : null)))
                                    <div class="rounded-[18px] border border-[#efe5db] bg-white px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-[#181512]">
                                                    @if ($recentOrderFrontUrl)
                                                        <a href="{{ $recentOrderFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $order->title }}</a>
                                                    @else
                                                        {{ $order->title }}
                                                    @endif
                                                </div>
                                                <div class="mt-1 text-xs text-[#78716c]">{{ $order->order_no }}</div>
                                            </div>
                                            <span class="text-sm font-semibold text-[#181512]">¥{{ number_format((float) $order->amount, 2) }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-[18px] border border-dashed border-[#d6d3d1] bg-white/80 p-4 text-sm text-[#78716c]">还没有订单记录。</div>
                                @endforelse
                            </div>
                        </article>

                        <article class="rounded-[24px] border border-[#efe5db] bg-[#fffaf5] p-5">
                            <h3 class="text-lg font-semibold text-[#181512]">最近订阅</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($recentSubscriptions as $subscription)
                                    @php($recentSubscriptionFrontUrl = $subscription->plan?->slug ? route('pricing') : ($subscription->lastOrder?->purchasable instanceof \App\Models\Product ? route('shop.show', $subscription->lastOrder->purchasable->slug) : ($subscription->lastOrder?->purchasable instanceof \App\Models\Event ? route('events.show', $subscription->lastOrder->purchasable->slug) : null)))
                                    <div class="rounded-[18px] border border-[#efe5db] bg-white px-4 py-3">
                                        <div class="text-sm font-semibold text-[#181512]">
                                            @if ($recentSubscriptionFrontUrl)
                                                <a href="{{ $recentSubscriptionFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $subscription->plan?->name ?? '未关联套餐' }}</a>
                                            @else
                                                {{ $subscription->plan?->name ?? '未关联套餐' }}
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-[#78716c]">状态：{{ $subscription->status }} · 订单：{{ $subscription->lastOrder?->order_no ?? '暂无' }}</div>
                                    </div>
                                @empty
                                    <div class="rounded-[18px] border border-dashed border-[#d6d3d1] bg-white/80 p-4 text-sm text-[#78716c]">还没有订阅记录。</div>
                                @endforelse
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
