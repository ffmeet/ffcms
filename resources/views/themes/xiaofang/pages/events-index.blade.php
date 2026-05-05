@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '活动系统 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'themes.xiaofang.components.feature-hero'), [
        'eyebrow' => 'Events',
        'title' => '活动系统',
        'description' => '活动、会员与支付会逐步并到同一条商业化链路里，当前页先承接公开浏览与报名入口。',
        'actions' => [
            ['label' => '查看会员计划', 'url' => route('pricing'), 'variant' => 'primary'],
        ],
        'metrics' => [
            ['label' => '公开活动', 'value' => $eventMetrics['all_events']],
            ['label' => '报名中', 'value' => $eventMetrics['open_events']],
            ['label' => '付费活动', 'value' => $eventMetrics['paid_events']],
        ],
    ])

    @if ($highlightedEvents->isNotEmpty())
        <section class="mt-12">
            <div class="mb-8 text-center">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Highlights</p>
                <h2 class="mt-3 font-serif text-4xl font-semibold text-[#151515]">近期重点活动</h2>
            </div>
            <div class="grid gap-x-10 gap-y-16 md:grid-cols-2 xl:grid-cols-3" style="column-gap: 2.5rem; row-gap: 4rem;">
                @foreach ($highlightedEvents as $event)
                    @include(\App\Support\SiteTheme::view('components.event-card', 'themes.xiaofang.components.event-card'), ['event' => $event])
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-14">
        <div class="mb-8 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Calendar</p>
            <h2 class="mt-3 font-serif text-4xl font-semibold text-[#151515]">全部活动</h2>
        </div>

        <div class="grid gap-x-10 gap-y-16 md:grid-cols-2 xl:grid-cols-3" style="column-gap: 2.5rem; row-gap: 4rem;">
            @forelse ($events as $event)
                @include(\App\Support\SiteTheme::view('components.event-card', 'themes.xiaofang.components.event-card'), ['event' => $event])
            @empty
                <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-10 text-sm text-[#78716c] md:col-span-2 xl:col-span-3">
                    当前还没有可公开展示的活动。
                </div>
            @endforelse
        </div>

        <div class="site-pagination mt-10">
            {{ $events->links() }}
        </div>
    </section>
@endsection
