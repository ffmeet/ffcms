@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '活动系统 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'site.partials.feature-hero'), [
        'eyebrow' => 'Events',
        'title' => '活动系统',
        'description' => '活动模块会承接免费与付费活动的公开浏览体验，下一阶段再把报名、支付和会员限制完整接进来。',
        'actions' => [
            ['label' => '查看会员计划', 'url' => route('pricing'), 'variant' => 'primary'],
            ['label' => '查看商店', 'url' => route('shop.index')],
        ],
        'metrics' => [
            ['label' => '公开活动', 'value' => $eventMetrics['all_events']],
            ['label' => '报名中', 'value' => $eventMetrics['open_events']],
            ['label' => '付费活动', 'value' => $eventMetrics['paid_events']],
        ],
    ])

    @if ($highlightedEvents->isNotEmpty())
        <section class="site-section-shell mt-8 p-6">
            <div class="site-section-header mb-6">
                <div>
                <p class="site-section-kicker">Highlights</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">近期重点活动</h2>
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($highlightedEvents as $event)
                    @include(\App\Support\SiteTheme::view('components.event-card', 'site.partials.event-card'), ['event' => $event])
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-8">
        <div class="site-section-header mb-6">
            <div>
            <p class="site-section-kicker">Calendar</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">全部活动</h2>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($events as $event)
                @include(\App\Support\SiteTheme::view('components.event-card', 'site.partials.event-card'), ['event' => $event])
            @empty
                <div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-sm text-slate-500 md:col-span-2 xl:col-span-3">
                    当前还没有可公开展示的活动。
                </div>
            @endforelse
        </div>

        <div class="site-pagination mt-6">
            {{ $events->links() }}
        </div>
    </section>
@endsection
