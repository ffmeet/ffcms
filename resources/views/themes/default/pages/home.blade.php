@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => ($siteSettings->seo_title ?? $siteSettings->site_name ?? '年度科技先生') . ' - 首页'])

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1.4fr_0.85fr]">
        <div class="overflow-hidden rounded-[34px] border border-white/70 bg-[linear-gradient(135deg,rgba(255,255,255,0.96),rgba(243,247,255,0.9))] p-8 shadow-[0_24px_80px_rgba(15,23,42,0.08)] lg:p-10">
            <div class="max-w-4xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.38em] text-slate-500">{{ $siteSettings->hero_eyebrow }}</p>
                <h1 class="mt-5 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl lg:text-[3.6rem] lg:leading-[1.08]">{{ $siteSettings->hero_title }}</h1>
                <p class="mt-6 max-w-3xl text-base leading-8 text-slate-600">{{ $siteSettings->hero_body }}</p>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $siteSettings->hero_primary_url }}" class="rounded-full bg-slate-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">{{ $siteSettings->hero_primary_label }}</a>
                <a href="{{ $siteSettings->hero_secondary_url }}" class="rounded-full border border-slate-200 bg-white/80 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">{{ $siteSettings->hero_secondary_label }}</a>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3">
                <article class="rounded-[28px] border border-slate-200/80 bg-white/88 px-5 py-5">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">已发布内容</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $metrics['published_posts'] }}</div>
                </article>
                <article class="rounded-[28px] border border-slate-200/80 bg-white/88 px-5 py-5">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">栏目数</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $metrics['categories'] }}</div>
                </article>
                <article class="rounded-[28px] border border-slate-200/80 bg-white/88 px-5 py-5">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">标签数</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $metrics['tags'] }}</div>
                </article>
            </div>
        </div>

        <div class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Lead Story</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">今日聚焦</h2>
                </div>
                <a href="{{ route('search') }}" class="text-sm font-medium text-slate-500 transition hover:text-slate-950">查看全部</a>
            </div>

            @if ($leadPost)
                <div class="mt-5">
                    @include(\App\Support\SiteTheme::view('components.post-card', 'site.partials.post-card'), ['post' => $leadPost, 'compact' => true])
                </div>
            @else
                <div class="mt-5 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-sm text-slate-500">
                    当前还没有可展示的重点内容。
                </div>
            @endif
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">{{ $homeContent['sections_eyebrow'] }}</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $homeContent['sections_title'] }}</h2>
                    </div>
                    <a href="{{ route('search') }}" class="text-sm font-medium text-slate-500 transition hover:text-slate-950">{{ $homeContent['sections_cta'] }}</a>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}" class="rounded-[28px] border border-slate-200/80 bg-slate-50/80 px-5 py-5 transition hover:border-slate-900 hover:bg-white">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Category</div>
                            <div class="mt-4 text-2xl font-semibold tracking-tight text-slate-950">{{ $category->name }}</div>
                            <div class="mt-2 text-sm text-slate-500">{{ $category->posts_count }} 篇内容</div>
                            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $category->description ?: '承接该栏目下的专题、文章和最新内容。' }}</p>
                        </a>
                    @empty
                        <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-sm text-slate-500 md:col-span-2 xl:col-span-3">
                            当前还没有可展示栏目。
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">{{ $homeContent['latest_eyebrow'] }}</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $homeContent['latest_title'] }}</h2>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    @forelse ($featuredPosts as $post)
                        @include(\App\Support\SiteTheme::view('components.post-card', 'site.partials.post-card'), ['post' => $post])
                    @empty
                        <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-sm text-slate-500 md:col-span-2">
                            当前还没有已发布文章。
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">{{ $homeContent['tags_eyebrow'] }}</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $homeContent['tags_title'] }}</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    @forelse ($tags as $tag)
                        <a href="{{ route('tags.show', $tag->slug) }}" class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-900 hover:bg-white hover:text-slate-950">
                            # {{ $tag->name }}
                            <span class="ml-1 text-xs text-slate-400">{{ $tag->count }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">当前还没有可展示标签。</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">{{ $homeContent['flash_eyebrow'] }}</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $homeContent['flash_title'] }}</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($flashPosts as $post)
                        <a href="{{ route('posts.show', $post->slug) }}" class="block rounded-[24px] border border-slate-200/80 bg-slate-50 px-4 py-4 transition hover:border-slate-900 hover:bg-white">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-800">{{ optional($post->published_at)->format('m-d H:i') }}</div>
                            <div class="mt-3 text-base font-semibold leading-7 text-slate-950">{{ $post->title }}</div>
                        </a>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                            快讯模块暂时还没有内容。
                        </div>
                    @endforelse
                </div>
            </section>

            @if ($siteSettings->show_membership_section || $siteSettings->show_events_section || $siteSettings->show_shop_section)
                <section class="rounded-[34px] border border-white/70 bg-slate-950 p-6 text-white shadow-[0_24px_80px_rgba(15,23,42,0.20)]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-400">{{ $homeContent['roadmap_eyebrow'] }}</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">{{ $homeContent['roadmap_title'] }}</h2>
                    <div class="mt-5 space-y-4">
                        @if ($siteSettings->show_membership_section)
                            <a href="{{ route('pricing') }}" class="block rounded-[24px] border border-white/10 bg-white/5 px-4 py-4 transition hover:bg-white/10">
                                <div class="text-sm font-semibold">{{ $homeContent['membership_title'] }}</div>
                                <div class="mt-2 text-sm leading-7 text-slate-300">{{ $homeContent['membership_copy'] }}</div>
                            </a>
                        @endif
                        @if ($siteSettings->show_events_section)
                            <a href="{{ route('events.index') }}" class="block rounded-[24px] border border-white/10 bg-white/5 px-4 py-4 transition hover:bg-white/10">
                                <div class="text-sm font-semibold">{{ $homeContent['events_title'] }}</div>
                                <div class="mt-2 text-sm leading-7 text-slate-300">{{ $homeContent['events_copy'] }}</div>
                            </a>
                        @endif
                        @if ($siteSettings->show_shop_section)
                            <a href="{{ route('shop.index') }}" class="block rounded-[24px] border border-white/10 bg-white/5 px-4 py-4 transition hover:bg-white/10">
                                <div class="text-sm font-semibold">{{ $homeContent['shop_title'] }}</div>
                                <div class="mt-2 text-sm leading-7 text-slate-300">{{ $homeContent['shop_copy'] }}</div>
                            </a>
                        @endif
                    </div>
                </section>
            @endif
        </aside>
    </section>
@endsection
