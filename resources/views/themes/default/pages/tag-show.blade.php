@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '#' . $tag->name . ' - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    <section class="rounded-[34px] border border-white/70 bg-white/92 p-8 shadow-[0_24px_80px_rgba(15,23,42,0.08)] lg:p-10">
        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Tag Archive</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl"># {{ $tag->name }}</h1>
        <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">这里承接同一标签下的内容，帮助读者沿着同一兴趣继续向下浏览专题、文章和快讯。</p>
        <div class="mt-6 flex flex-wrap gap-3 text-sm text-slate-500">
            <span class="rounded-full bg-slate-100 px-4 py-2">当前共 {{ $posts->total() }} 篇内容</span>
            <span class="rounded-full bg-slate-100 px-4 py-2">热门标签联动 {{ $trendingTags->count() }} 个</span>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('search', ['q' => $tag->name]) }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600">搜索这个标签</a>
            <a href="{{ route('site.home') }}" class="rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">返回首页</a>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div>
            <div class="grid gap-5 md:grid-cols-2">
                @forelse ($posts as $post)
                    @include(\App\Support\SiteTheme::view('components.post-card', 'site.partials.post-card'), ['post' => $post, 'compact' => true])
                @empty
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-sm text-slate-500 md:col-span-2">
                        当前标签下暂时还没有已发布内容。
                    </div>
                @endforelse
            </div>

            <div class="site-pagination mt-6">
                {{ $posts->links() }}
            </div>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">More Topics</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">更多标签</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach ($trendingTags as $item)
                        <a href="{{ route('tags.show', $item->slug) }}" class="rounded-full border {{ $item->is($tag) ? 'border-slate-950 bg-slate-950 text-white' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-900 hover:bg-white hover:text-slate-950' }} px-4 py-2 text-sm font-medium transition">
                            # {{ $item->name }}
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Sections</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">栏目跳转</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($featuredCategories as $item)
                        <a href="{{ route('categories.show', $item->slug) }}" class="block rounded-[24px] border border-slate-200/80 bg-slate-50 px-4 py-4 transition hover:border-slate-900 hover:bg-white">
                            <div class="text-base font-semibold text-slate-950">{{ $item->name }}</div>
                            <div class="mt-2 text-sm text-slate-500">{{ $item->posts_count }} 篇内容</div>
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </section>
@endsection
