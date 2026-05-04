@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => $event->title . ' - 活动 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @php
        $currentUser = auth()->user();
        $statusLabel = match($event->status) {
            'registration-open' => '报名中',
            'sold-out' => '已满员',
            'finished' => '已结束',
            default => '活动',
        };
        $isRegistrationAvailable = $event->isRegistrationAvailable();
        $isCapacityFull = $event->hasReachedCapacity();
        $isRegistrationClosed = $event->status !== 'registration-open' || $event->registration_closes_at?->isPast();
        $hasEventAccess = $currentUser?->hasMemberPermission('events.access') ?? false;
        $hasGroupAccess = $currentUser ? $currentUser->canAccessMemberGroup($event->memberGroup) : false;
    @endphp

    <article class="pb-14">
        <section class="border-b border-[#ece7e0] pb-12 pt-8 lg:pb-14 lg:pt-12">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1.2fr)_320px] lg:items-end">
                <div class="max-w-4xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Event</p>
                    <h1 class="mt-5 font-serif text-4xl font-semibold leading-[1.04] tracking-[-0.03em] text-[#151515] sm:text-5xl lg:text-[4.4rem]">{{ $event->title }}</h1>
                    <p class="mt-6 max-w-3xl text-lg leading-9 text-[#5f574f]">{{ $event->summary ?: '活动详情页承接公开介绍、会员限制和报名入口，保持与当前主题一致的刊物式阅读结构。' }}</p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('events.index') }}" class="bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">查看全部活动</a>
                        <a href="{{ route('pricing') }}" class="border border-[#d8d1c8] bg-white px-5 py-3 text-sm font-semibold text-[#151515] transition hover:border-[#151515]">查看会员计划</a>
                    </div>
                </div>

                <div class="border-l border-[#ece7e0] pl-0 lg:pl-8">
                    <div class="grid grid-cols-2 gap-x-6 gap-y-5 text-sm">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">状态</div>
                            <div class="mt-2 font-medium text-[#151515]">{{ $statusLabel }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">报名价格</div>
                            <div class="mt-2 font-medium text-[#151515]">{{ $event->is_paid ? '¥' . number_format((float) $event->price, 2) : '免费' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">名额</div>
                            <div class="mt-2 font-medium text-[#151515]">{{ $event->capacity ?: '不限' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">地点</div>
                            <div class="mt-2 font-medium text-[#151515]">{{ $event->location ?: '待定' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-12 grid gap-10 lg:grid-cols-[minmax(0,1.12fr)_360px]">
            <div>
                @if ($event->cover_image_url)
                    <figure class="overflow-hidden bg-[#f5f3ef]">
                        <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="h-[320px] w-full object-cover sm:h-[420px] lg:h-[560px]">
                    </figure>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">
                    <span>{{ $statusLabel }}</span>
                    <span class="text-[#d6d3d1]">•</span>
                    <span>{{ $event->is_paid ? '付费活动' : '免费活动' }}</span>
                    @if ($event->memberGroup)
                        <span class="text-[#d6d3d1]">•</span>
                        <span>{{ $event->memberGroup->name }}</span>
                    @endif
                </div>

                <div class="site-prose mt-8 max-w-none text-[17px] leading-9 text-[#3f3a35]">
                    {!! filled($event->content) ? nl2br(e($event->content)) : '<p>当前活动还没有完整介绍。</p>' !!}
                </div>
            </div>

            <aside class="space-y-8 lg:sticky lg:top-8 lg:self-start">
                <section class="border-t border-[#151515] pt-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Event Info</p>
                    <dl class="mt-6 space-y-5 text-sm leading-7 text-[#5f574f]">
                        <div class="border-b border-[#ece7e0] pb-4">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">时间</dt>
                            <dd class="mt-2 text-[#151515]">{{ $event->starts_at?->format('Y-m-d H:i') ?? '待定' }} @if($event->ends_at) - {{ $event->ends_at->format('Y-m-d H:i') }} @endif</dd>
                        </div>
                        <div class="border-b border-[#ece7e0] pb-4">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">地点</dt>
                            <dd class="mt-2 text-[#151515]">{{ $event->location ?: '待定' }}</dd>
                        </div>
                        <div class="border-b border-[#ece7e0] pb-4">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">价格</dt>
                            <dd class="mt-2 text-[#151515]">{{ $event->is_paid ? '¥' . number_format((float) $event->price, 2) : '免费' }}</dd>
                        </div>
                        <div class="border-b border-[#ece7e0] pb-4">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">人数上限</dt>
                            <dd class="mt-2 text-[#151515]">{{ $event->capacity ?: '不限' }}</dd>
                        </div>
                        @if ($event->memberGroup)
                            <div class="border-b border-[#ece7e0] pb-4">
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">限定会员组</dt>
                                <dd class="mt-2 text-[#151515]">{{ $event->memberGroup->name }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                <section class="bg-[#fcfaf7] px-6 py-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Registration</p>
                    <div class="mt-5 space-y-4 text-sm leading-7 text-[#5f574f]">
                        @if ($isCapacityFull)
                            <div class="border border-[#e5dfd7] bg-white px-5 py-5 text-[#6b6256]">
                                当前活动名额已满，暂时不能继续提交报名。
                            </div>
                        @elseif ($isRegistrationClosed)
                            <div class="border border-[#e5dfd7] bg-white px-5 py-5 text-[#6b6256]">
                                当前活动暂未开放报名或报名时间已结束，请关注后续新场次。
                            </div>
                        @else
                            @auth
                                @if (! $hasEventAccess)
                                    <div class="border border-[#e5dfd7] bg-white px-5 py-5 text-[#6b6256]">
                                        当前会员组还没有活动权限，暂时不能发起报名。
                                    </div>
                                    <a href="{{ route('pricing') }}" class="block border border-[#d8d1c8] bg-white px-5 py-3 text-center text-sm font-semibold text-[#151515] transition hover:border-[#151515]">查看会员计划</a>
                                @elseif ($event->memberGroup && ! $hasGroupAccess)
                                    <div class="border border-[#e5dfd7] bg-white px-5 py-5 text-[#6b6256]">
                                        这场活动仅对 {{ $event->memberGroup->name }} 开放，当前会员级别还不能报名。
                                    </div>
                                    <a href="{{ route('pricing') }}" class="block border border-[#d8d1c8] bg-white px-5 py-3 text-center text-sm font-semibold text-[#151515] transition hover:border-[#151515]">升级会员后报名</a>
                                @elseif ($isRegistrationAvailable)
                                    <form method="POST" action="{{ route('events.register', $event->slug) }}">
                                        @csrf
                                        <button type="submit" class="w-full bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">提交报名</button>
                                    </form>
                                @endif
                            @else
                                @if ($isRegistrationAvailable)
                                    <a href="{{ route('login') }}" class="block w-full bg-[#151515] px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">登录后报名</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </section>

                @if ($relatedEvents->isNotEmpty())
                    <section class="border-t border-[#151515] pt-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Related</p>
                        <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-[#151515]">更多活动</h2>
                        <div class="mt-6 space-y-0 border-t border-[#ece7e0]">
                            @foreach ($relatedEvents as $item)
                                <a href="{{ route('events.show', $item->slug) }}" class="block border-b border-[#ece7e0] py-4 transition hover:pl-2">
                                    <div class="text-lg font-semibold text-[#151515]">{{ $item->title }}</div>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-[#78716c]">
                                        <span>{{ $item->starts_at?->format('Y-m-d H:i') ?? '时间待定' }}</span>
                                        <span class="text-[#d6d3d1]">·</span>
                                        <span>{{ $item->location ?: '地点待定' }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </section>
    </article>
@endsection
