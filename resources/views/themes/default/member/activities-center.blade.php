@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '活动中心'])

@section('content')
    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'site.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'site.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'site.member.partials.page-header'), [
                'eyebrow' => 'Activity Center',
                'title' => '活动中心',
                'description' => '这里承接活动报名、订单处理和订阅状态，让会员前台逐步形成真实的交易与活动中心。',
                'actions' => [
                    ['label' => '查看我的活动', 'url' => route('member.activities.index')],
                ],
            ])

            <div class="grid gap-5 xl:grid-cols-3">
                <article class="rounded-[30px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-slate-500">活动报名</p>
                    <p class="mt-3 text-4xl font-semibold text-slate-900">{{ $activitySummary['event_registrations'] }}</p>
                </article>
                <article class="rounded-[30px] border border-sky-100/80 bg-sky-50/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-sky-700">待支付订单</p>
                    <p class="mt-3 text-4xl font-semibold text-sky-700">{{ $activitySummary['pending_orders'] }}</p>
                </article>
                <article class="rounded-[30px] border border-emerald-100/80 bg-emerald-50/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-emerald-700">生效订阅</p>
                    <p class="mt-3 text-4xl font-semibold text-emerald-700">{{ $activitySummary['active_subscriptions'] }}</p>
                </article>
            </div>

            <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">活动面板</h2>
                        <p class="mt-1 text-sm text-slate-500">这里已经开始承接即将开始的活动、最近订单和订阅状态。</p>
                    </div>
                    <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center rounded-[18px] bg-sky-50 px-4 py-2.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">查看会员计划</a>
                </div>

                <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                    <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-lg font-semibold text-slate-900">即将开始的活动</h3>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Upcoming</span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse ($upcomingRegistrations as $registration)
                                <article class="rounded-[18px] border border-slate-200 bg-white px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h4 class="text-base font-semibold text-slate-900">{{ $registration->event?->title ?? '活动已删除' }}</h4>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $registration->event?->location ?: '线上 / 待定地点' }}
                                                @if ($registration->event?->starts_at)
                                                    <span class="text-slate-400"> · {{ $registration->event->starts_at->format('Y-m-d H:i') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $registration->status === 'approved' ? '已确认' : '待支付' }}</span>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-[18px] border border-dashed border-slate-300 bg-white/80 p-5 text-sm text-slate-500">当前没有即将开始的活动报名。</div>
                            @endforelse
                        </div>
                    </article>

                    <div class="space-y-4">
                        <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                            <h3 class="text-lg font-semibold text-slate-900">最近订单</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($recentOrders as $order)
                                    <div class="rounded-[18px] border border-slate-200 bg-white px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-slate-900">{{ $order->title }}</div>
                                                <div class="mt-1 text-xs text-slate-400">{{ $order->order_no }}</div>
                                            </div>
                                            <span class="text-sm font-semibold text-slate-700">¥{{ number_format((float) $order->amount, 2) }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-[18px] border border-dashed border-slate-300 bg-white/80 p-4 text-sm text-slate-500">还没有订单记录。</div>
                                @endforelse
                            </div>
                        </article>

                        <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                            <h3 class="text-lg font-semibold text-slate-900">最近订阅</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($recentSubscriptions as $subscription)
                                    <div class="rounded-[18px] border border-slate-200 bg-white px-4 py-3">
                                        <div class="text-sm font-semibold text-slate-900">{{ $subscription->plan?->name ?? '未关联套餐' }}</div>
                                        <div class="mt-1 text-xs text-slate-400">状态：{{ $subscription->status }} · 订单：{{ $subscription->lastOrder?->order_no ?? '暂无' }}</div>
                                    </div>
                                @empty
                                    <div class="rounded-[18px] border border-dashed border-slate-300 bg-white/80 p-4 text-sm text-slate-500">还没有订阅记录。</div>
                                @endforelse
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
