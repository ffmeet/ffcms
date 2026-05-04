@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => $event->title . ' - 活动 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    @php
        $currentUser = auth()->user();
        $statusLabel = match($event->status) {
            'registration-open' => '报名中',
            'sold-out' => '已满员',
            'finished' => '已结束',
            default => '活动',
        };
        $statusChipClass = match($event->status) {
            'registration-open' => 'site-chip--emerald',
            'sold-out' => 'site-chip--amber',
            'finished' => 'site-chip--slate',
            default => 'site-chip--brand',
        };
        $isRegistrationAvailable = $event->isRegistrationAvailable();
        $isCapacityFull = $event->hasReachedCapacity();
        $isRegistrationClosed = $event->status !== 'registration-open' || $event->registration_closes_at?->isPast();
        $hasEventAccess = $currentUser?->hasMemberPermission('events.access') ?? false;
        $hasGroupAccess = $currentUser ? $currentUser->canAccessMemberGroup($event->memberGroup) : false;
    @endphp

    <section class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="site-feature-shell overflow-hidden">
            @if ($event->cover_image_url)
                <div class="border-b border-slate-200 bg-slate-100">
                    <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="h-[260px] w-full object-cover sm:h-[360px] lg:h-[440px]">
                </div>
            @endif
            <div class="p-8 lg:p-10">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="site-chip {{ $statusChipClass }}">{{ $statusLabel }}</span>
                    <span class="site-chip {{ $event->is_paid ? 'site-chip--amber' : 'site-chip--brand' }}">{{ $event->is_paid ? '付费活动' : '免费活动' }}</span>
                    @if ($event->memberGroup)
                        <span class="site-chip site-chip--slate">{{ $event->memberGroup->name }}</span>
                    @endif
                </div>
                <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $event->title }}</h1>
                @if ($event->summary)
                    <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $event->summary }}</p>
                @endif
                <div class="site-prose mt-8 max-w-none">
                    {!! filled($event->content) ? nl2br(e($event->content)) : '<p>当前活动还没有完整介绍。</p>' !!}
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="site-section-shell p-6">
                <p class="site-section-kicker">Event Info</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">活动信息</h2>
                <div class="mt-6 space-y-4 text-sm leading-7 text-slate-600">
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-5">
                        <div>时间：{{ $event->starts_at?->format('Y-m-d H:i') ?? '待定' }} @if($event->ends_at) - {{ $event->ends_at->format('Y-m-d H:i') }} @endif</div>
                        <div class="mt-2">地点：{{ $event->location ?: '待定' }}</div>
                        <div class="mt-2">价格：{{ $event->is_paid ? '¥' . number_format((float) $event->price, 2) : '免费' }}</div>
                        <div class="mt-2">人数上限：{{ $event->capacity ?: '不限' }}</div>
                    </div>
                    @if ($event->memberGroup)
                        <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-5">
                            限定会员组：{{ $event->memberGroup->name }}
                        </div>
                    @endif
                    <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-5 py-5">
                        <div class="flex flex-wrap gap-2">
                            <span class="site-chip site-chip--emerald">支持报名记录</span>
                            <span class="site-chip site-chip--slate">支付状态可模拟</span>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-slate-500">报名动作已经可以生成待处理记录，这一版先完成报名承接，下一阶段接支付和名额校验。</p>
                    </div>
                    @if ($isCapacityFull)
                        <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-5 text-sm leading-7 text-amber-800">
                            当前活动名额已满，暂时不能继续提交报名。
                        </div>
                    @elseif ($isRegistrationClosed)
                        <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
                            当前活动暂未开放报名或报名时间已结束，请关注后续新场次。
                        </div>
                    @else
                        @auth
                            @if (! $hasEventAccess)
                                <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-5 text-sm leading-7 text-amber-800">
                                    当前会员组还没有活动权限，暂时不能发起报名。
                                </div>
                                <a href="{{ route('pricing') }}" class="block w-full rounded-full border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">查看会员计划</a>
                            @elseif ($event->memberGroup && ! $hasGroupAccess)
                                <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-5 text-sm leading-7 text-amber-800">
                                    这场活动仅对 {{ $event->memberGroup->name }} 开放，当前会员级别还不能报名。
                                </div>
                                <a href="{{ route('pricing') }}" class="block w-full rounded-full border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">升级会员后报名</a>
                            @elseif ($isRegistrationAvailable)
                                <form method="POST" action="{{ route('events.register', $event->slug) }}">
                                    @csrf
                                    <button type="submit" class="w-full rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">提交报名</button>
                                </form>
                            @endif
                        @else
                            @if ($isRegistrationAvailable)
                                <a href="{{ route('login') }}" class="block w-full rounded-full bg-slate-950 px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-blue-600">登录后报名</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </section>

            @if ($relatedEvents->isNotEmpty())
                <section class="site-section-shell p-6">
                    <p class="site-section-kicker">Related</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">更多活动</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($relatedEvents as $item)
                            <a href="{{ route('events.show', $item->slug) }}" class="block rounded-[24px] border border-slate-200/80 bg-slate-50 px-4 py-4 transition hover:border-slate-900 hover:bg-white">
                                <div class="text-base font-semibold text-slate-950">{{ $item->title }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                    <span>{{ $item->starts_at?->format('Y-m-d H:i') ?? '时间待定' }}</span>
                                    <span class="text-slate-300">·</span>
                                    <span>{{ $item->location ?: '地点待定' }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </section>
@endsection
