@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => $category->name . ' - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    <section class="rounded-[34px] border border-white/70 bg-white/92 p-8 shadow-[0_24px_80px_rgba(15,23,42,0.08)] lg:p-10">
        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Category</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $category->name }}</h1>
        <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $category->description ?: '当前栏目还没有栏目描述，后续会在这里承接专题定位、编辑导语和内容说明。' }}</p>
        <div class="mt-6 flex flex-wrap gap-3 text-sm text-slate-500">
            <span class="rounded-full bg-slate-100 px-4 py-2">{{ $posts->total() }} 篇内容</span>
            <span class="rounded-full bg-slate-100 px-4 py-2">{{ $relatedCategories->count() }} 个可延伸栏目</span>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('search', ['q' => $category->name]) }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600">搜索本栏目</a>
            <a href="{{ route('site.home') }}" class="rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">返回首页</a>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-5">
            @forelse ($posts as $post)
                @include(\App\Support\SiteTheme::view('components.post-card', 'site.partials.post-card'), ['post' => $post])
            @empty
                <div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-sm text-slate-500">
                    该栏目下暂时还没有已发布内容。
                </div>
            @endforelse

            <div class="site-pagination">
                {{ $posts->links() }}
            </div>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">More Sections</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">继续浏览</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($relatedCategories as $item)
                        <a href="{{ route('categories.show', $item->slug) }}" class="block rounded-[24px] border border-slate-200/80 bg-slate-50 px-4 py-4 transition hover:border-slate-900 hover:bg-white">
                            <div class="text-base font-semibold text-slate-950">{{ $item->name }}</div>
                            <div class="mt-2 text-sm text-slate-500">{{ $item->posts_count }} 篇内容</div>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Topics</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">热门标签</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach ($trendingTags as $item)
                        <a href="{{ route('tags.show', $item->slug) }}" class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-900 hover:bg-white hover:text-slate-950">
                            # {{ $item->name }}
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </section>
@endsection
