@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '我的活动'])

@section('content')
    @php
        use App\Support\CommerceLabels;
    @endphp

    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'site.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'site.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'site.member.partials.page-header'), [
                'eyebrow' => 'My Activities',
                'title' => '我的活动',
                'description' => '这里汇总你的活动报名、支付中的订单和订阅状态，让会员中心开始承接真实业务流程。',
            ])

            <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="grid gap-4 md:grid-cols-4">
                    <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-sm text-slate-500">活动报名</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $activitySummary['event_registrations'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-sky-100 bg-sky-50/80 p-4">
                        <p class="text-sm text-sky-700">待确认</p>
                        <p class="mt-2 text-2xl font-semibold text-sky-700">{{ $activitySummary['pending_registrations'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-indigo-100 bg-indigo-50/80 p-4">
                        <p class="text-sm text-indigo-700">即将开始</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-700">{{ $activitySummary['upcoming_events'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-emerald-100 bg-emerald-50/80 p-4">
                        <p class="text-sm text-emerald-700">生效订阅</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $activitySummary['active_subscriptions'] }}</p>
                    </article>
                </div>
            </section>

            <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">活动报名</h2>
                        <p class="mt-1 text-sm text-slate-500">这里优先展示你的活动报名状态、支付情况和关联订单。</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($registrations as $registration)
                        <article class="grid gap-4 rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,250,252,.92))] px-5 py-4 md:grid-cols-[minmax(0,1.1fr)_220px_170px] md:items-center">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-sky-700">活动报名</div>
                                <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $registration->event?->title ?? '活动已删除' }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $registration->event?->location ?: '线上 / 待定地点' }}
                                    @if ($registration->event?->starts_at)
                                        <span class="text-slate-400"> · {{ $registration->event->starts_at->format('Y-m-d H:i') }}</span>
                                    @endif
                                </p>
                                @if ($registration->order?->order_no)
                                    <p class="mt-2 text-xs text-slate-400">关联订单：{{ $registration->order->order_no }}</p>
                                @endif
                            </div>
                            <div class="text-sm text-slate-500">
                                <div>报名状态：{{ CommerceLabels::registrationStatus($registration->status) }}</div>
                                <div class="mt-1">支付状态：{{ CommerceLabels::registrationPaymentStatus($registration->payment_status) }}</div>
                                <div class="mt-1 text-xs text-slate-400">{{ optional($registration->created_at)->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="text-right">
                                @if ($registration->order)
                                    <div class="text-lg font-semibold text-slate-900">¥{{ number_format((float) $registration->order->amount, 2) }}</div>
                                    <div class="mt-2 text-xs text-slate-400">支付渠道：{{ $registration->order->payments->isNotEmpty() ? CommerceLabels::paymentProvider($registration->order->payments->first()->provider) : '待分配' }}</div>
                                    @if ($registration->order->status !== 'paid' && (float) $registration->order->amount > 0)
                                        <div class="mt-3">
                                            <a href="{{ route('member.orders.pay', $registration->order) }}" class="inline-flex rounded-full bg-slate-950 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-600">继续支付</a>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-lg font-semibold text-emerald-700">免费</div>
                                    <div class="mt-2 text-xs text-slate-400">无需支付</div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-sm text-slate-500">当前还没有活动报名记录。</div>
                    @endforelse
                </div>

                <div class="site-pagination mt-6">
                    {{ $registrations->links() }}
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-900">最近订单</h2>
                            <p class="mt-1 text-sm text-slate-500">商品、会员和活动都会汇总到这里。</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($recentOrders as $order)
                            <article class="grid gap-4 rounded-[24px] border border-slate-200 bg-slate-50/70 px-5 py-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                                <div class="min-w-0">
                                    <h3 class="text-lg font-semibold text-slate-900">{{ $order->title }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $order->order_no }} · {{ CommerceLabels::orderType($order->order_type) }} · {{ optional($order->created_at)->format('Y-m-d H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-semibold text-slate-900">¥{{ number_format((float) $order->amount, 2) }}</div>
                                    <div class="mt-2 text-xs text-slate-400">{{ $order->payments->isNotEmpty() ? CommerceLabels::paymentProvider($order->payments->first()->provider) : '无需支付 / 待接入' }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ CommerceLabels::orderStatus($order->status) }}</div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-sm text-slate-500">当前还没有订单记录。</div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-900">订阅状态</h2>
                            <p class="mt-1 text-sm text-slate-500">多付费层会在这里承接生效时间和最近订单。</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($recentSubscriptions as $subscription)
                            <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-semibold text-slate-900">{{ $subscription->plan?->name ?? '未关联套餐' }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">最近订单：{{ $subscription->lastOrder?->order_no ?? '暂无' }}</p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            生效：{{ $subscription->started_at?->format('Y-m-d H:i') ?? '待生效' }}
                                            <span class="text-slate-300"> / </span>
                                            到期：{{ $subscription->expires_at?->format('Y-m-d H:i') ?? '待确认' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ CommerceLabels::subscriptionStatus($subscription->status) }}</span>
                                        <div class="mt-2 text-xs text-slate-400">{{ $subscription->auto_renew ? '自动续费开启' : '自动续费关闭' }}</div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-sm text-slate-500">当前还没有订阅记录。</div>
                        @endforelse
                    </div>
                </section>
            </section>
        </div>
    </div>
@endsection
