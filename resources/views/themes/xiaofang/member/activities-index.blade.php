@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '我的活动'])

@section('content')
    @php
        use App\Support\CommerceLabels;
    @endphp

    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'themes.xiaofang.member.partials.page-header'), [
                'eyebrow' => 'My Activities',
                'title' => '我的活动',
                'description' => '这里汇总你的活动报名、支付中的订单和订阅状态，让会员中心开始承接真实业务流程。',
            ])

            <section class="rounded-[32px] border border-[#efe5db] bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="grid gap-4 md:grid-cols-4">
                    <article class="rounded-[24px] border border-[#efe5db] bg-[#fffaf5] p-4">
                        <p class="text-sm text-[#78716c]">活动报名</p>
                        <p class="mt-2 text-2xl font-semibold text-[#181512]">{{ $activitySummary['event_registrations'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-[#dbeafe] bg-[#eff6ff] p-4">
                        <p class="text-sm text-[#1d4ed8]">待确认</p>
                        <p class="mt-2 text-2xl font-semibold text-[#1d4ed8]">{{ $activitySummary['pending_registrations'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-[#fed7aa] bg-[#fff7ed] p-4">
                        <p class="text-sm text-[#c2410c]">即将开始</p>
                        <p class="mt-2 text-2xl font-semibold text-[#c2410c]">{{ $activitySummary['upcoming_events'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-[#d1fae5] bg-[#ecfdf5] p-4">
                        <p class="text-sm text-[#047857]">生效订阅</p>
                        <p class="mt-2 text-2xl font-semibold text-[#047857]">{{ $activitySummary['active_subscriptions'] }}</p>
                    </article>
                </div>
            </section>

            <section class="rounded-[32px] border border-[#efe5db] bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold text-[#181512]">活动报名</h2>
                        <p class="mt-1 text-sm text-[#6b6256]">这里优先展示你的活动报名状态、支付情况和关联订单。</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($registrations as $registration)
                        @php($eventFrontUrl = $registration->event?->slug ? route('events.show', $registration->event->slug) : null)
                        <article class="grid gap-4 rounded-[24px] border border-[#efe5db] bg-[linear-gradient(180deg,rgba(255,250,245,.98),rgba(255,255,255,.94),rgba(239,246,255,.86))] px-5 py-4 md:grid-cols-[minmax(0,1.1fr)_220px_170px] md:items-center">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-[#1d4ed8]">活动报名</div>
                                <h3 class="mt-1 text-lg font-semibold text-[#181512]">
                                    @if ($eventFrontUrl)
                                        <a href="{{ $eventFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $registration->event?->title ?? '活动已删除' }}</a>
                                    @else
                                        {{ $registration->event?->title ?? '活动已删除' }}
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-[#6b6256]">
                                    {{ $registration->event?->location ?: '线上 / 待定地点' }}
                                    @if ($registration->event?->starts_at)
                                        <span class="text-[#d6d3d1]"> · {{ $registration->event->starts_at->format('Y-m-d H:i') }}</span>
                                    @endif
                                </p>
                                @if ($registration->order?->order_no)
                                    <p class="mt-2 text-xs text-[#78716c]">关联订单：{{ $registration->order->order_no }}</p>
                                @endif
                            </div>
                            <div class="text-sm text-[#6b6256]">
                                <div>报名状态：{{ CommerceLabels::registrationStatus($registration->status) }}</div>
                                <div class="mt-1">支付状态：{{ CommerceLabels::registrationPaymentStatus($registration->payment_status) }}</div>
                                <div class="mt-1 text-xs text-[#78716c]">{{ optional($registration->created_at)->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="text-right">
                                @if ($registration->order)
                                    <div class="text-lg font-semibold text-[#181512]">¥{{ number_format((float) $registration->order->amount, 2) }}</div>
                                    <div class="mt-2 text-xs text-[#78716c]">支付渠道：{{ $registration->order->payments->isNotEmpty() ? CommerceLabels::paymentProvider($registration->order->payments->first()->provider) : '待分配' }}</div>
                                    @if ($registration->order->status !== 'paid' && (float) $registration->order->amount > 0)
                                        <div class="mt-3">
                                            <a href="{{ route('member.orders.pay', $registration->order) }}" class="inline-flex rounded-full bg-[#1d4ed8] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#1e40af]">继续支付</a>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-lg font-semibold text-[#047857]">免费</div>
                                    <div class="mt-2 text-xs text-[#78716c]">无需支付</div>
                                @endif
                                @if ($eventFrontUrl)
                                    <div class="mt-3">
                                        <a href="{{ $eventFrontUrl }}" class="inline-flex rounded-full border border-[#e7d8c9] bg-white px-4 py-2 text-xs font-semibold text-[#6b6256] transition hover:border-[#151515] hover:text-[#151515]">查看活动</a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-[#d6d3d1] bg-[#fffaf5] p-8 text-sm text-[#78716c]">当前还没有活动报名记录。</div>
                    @endforelse
                </div>

                <div class="site-pagination mt-6">
                    {{ $registrations->links() }}
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-[32px] border border-[#efe5db] bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-semibold text-[#181512]">最近订单</h2>
                            <p class="mt-1 text-sm text-[#6b6256]">商品、会员和活动都会汇总到这里。</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($recentOrders as $order)
                            @php($recentOrderFrontUrl = $order->purchasable instanceof \App\Models\Product ? route('shop.show', $order->purchasable->slug) : ($order->purchasable instanceof \App\Models\Event ? route('events.show', $order->purchasable->slug) : ($order->purchasable instanceof \App\Models\MembershipPlan ? route('pricing') : null)))
                            <article class="grid gap-4 rounded-[24px] border border-[#efe5db] bg-[#fffaf5] px-5 py-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                                <div class="min-w-0">
                                    <h3 class="text-lg font-semibold text-[#181512]">
                                        @if ($recentOrderFrontUrl)
                                            <a href="{{ $recentOrderFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $order->title }}</a>
                                        @else
                                            {{ $order->title }}
                                        @endif
                                    </h3>
                                    <p class="mt-1 text-sm text-[#6b6256]">{{ $order->order_no }} · {{ CommerceLabels::orderType($order->order_type) }} · {{ optional($order->created_at)->format('Y-m-d H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-semibold text-[#181512]">¥{{ number_format((float) $order->amount, 2) }}</div>
                                    <div class="mt-2 text-xs text-[#78716c]">{{ $order->payments->isNotEmpty() ? CommerceLabels::paymentProvider($order->payments->first()->provider) : '无需支付 / 待接入' }}</div>
                                    <div class="mt-1 text-xs text-[#78716c]">{{ CommerceLabels::orderStatus($order->status) }}</div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-[#d6d3d1] bg-[#fffaf5] p-8 text-sm text-[#78716c]">当前还没有订单记录。</div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[32px] border border-[#efe5db] bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-semibold text-[#181512]">订阅状态</h2>
                            <p class="mt-1 text-sm text-[#6b6256]">多付费层会在这里承接生效时间和最近订单。</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($recentSubscriptions as $subscription)
                            @php($recentSubscriptionFrontUrl = $subscription->plan?->slug ? route('pricing') : ($subscription->lastOrder?->purchasable instanceof \App\Models\Product ? route('shop.show', $subscription->lastOrder->purchasable->slug) : ($subscription->lastOrder?->purchasable instanceof \App\Models\Event ? route('events.show', $subscription->lastOrder->purchasable->slug) : null)))
                            <article class="rounded-[24px] border border-[#efe5db] bg-[#fffaf5] px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-semibold text-[#181512]">
                                            @if ($recentSubscriptionFrontUrl)
                                                <a href="{{ $recentSubscriptionFrontUrl }}" class="transition hover:text-[#1d4ed8]">{{ $subscription->plan?->name ?? '未关联套餐' }}</a>
                                            @else
                                                {{ $subscription->plan?->name ?? '未关联套餐' }}
                                            @endif
                                        </h3>
                                        <p class="mt-1 text-sm text-[#6b6256]">最近订单：{{ $subscription->lastOrder?->order_no ?? '暂无' }}</p>
                                        <p class="mt-1 text-xs text-[#78716c]">
                                            生效：{{ $subscription->started_at?->format('Y-m-d H:i') ?? '待生效' }}
                                            <span class="text-[#d6d3d1]"> / </span>
                                            到期：{{ $subscription->expires_at?->format('Y-m-d H:i') ?? '待确认' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-semibold text-[#1d4ed8]">{{ CommerceLabels::subscriptionStatus($subscription->status) }}</span>
                                        <div class="mt-2 text-xs text-[#78716c]">{{ $subscription->auto_renew ? '自动续费开启' : '自动续费关闭' }}</div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-[#d6d3d1] bg-[#fffaf5] p-8 text-sm text-[#78716c]">当前还没有订阅记录。</div>
                        @endforelse
                    </div>
                </section>
            </section>
        </div>
    </div>
@endsection
