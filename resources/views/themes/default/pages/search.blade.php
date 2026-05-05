@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => ($query ? '搜索：' . $query : '搜索') . ' - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    <section class="rounded-[34px] border border-white/70 bg-white/92 p-8 shadow-[0_24px_80px_rgba(15,23,42,0.08)] lg:p-10">
        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Search</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">站内搜索</h1>
        <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">支持按标题、正文、Slug 和标签关键词检索，帮助读者快速在内容、专题与快讯之间跳转。</p>

        <form method="GET" action="{{ route('search') }}" class="mt-7 flex flex-col gap-3 rounded-[28px] border border-slate-200/80 bg-slate-50 p-3 sm:flex-row">
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="搜索文章标题、正文关键词或标签"
                class="min-w-0 flex-1 rounded-[22px] border border-slate-200 bg-white px-5 py-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-slate-900"
            >
            <button type="submit" class="rounded-[22px] bg-slate-950 px-6 py-4 text-sm font-semibold text-white transition hover:bg-blue-600">开始搜索</button>
        </form>
    </section>

    @if (filled($query))
        <section class="mt-8 rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Results</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">“{{ $query }}” 的搜索结果</h2>
                </div>
                <div class="rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600">{{ $posts->total() }} 条结果</div>
            </div>

            <div class="grid gap-5">
                @forelse ($posts as $post)
                    @include(\App\Support\SiteTheme::view('components.post-card', 'site.partials.post-card'), ['post' => $post, 'compact' => true])
                @empty
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-sm text-slate-500">
                        没有找到和 “{{ $query }}” 相关的已发布内容，可以换个关键词再试。
                    </div>
                @endforelse
            </div>
        </section>

        <div class="site-pagination mt-6">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
