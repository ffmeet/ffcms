@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => ($query ? '搜索：' . $query : '搜索') . ' - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'themes.xiaofang.components.feature-hero'), [
        'eyebrow' => 'Search',
        'title' => $query ? '“' . $query . '”' : '站内搜索',
        'description' => $query
            ? '这里汇总当前关键词命中的文章、专题和快讯结果，保持与首页一致的频道式阅读结构。'
            : '支持按标题、正文、Slug 和标签关键词检索，帮助读者快速在内容与专题之间跳转。',
    ])

    <section class="mx-auto mt-8 max-w-2xl">
        <form method="GET" action="{{ route('search') }}" class="flex flex-col gap-3 border border-[#d8d1c8] bg-[#fcfaf7] p-2.5 sm:flex-row sm:items-center">
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="搜索文章标题、正文关键词或标签"
                class="min-w-0 flex-1 border border-[#e3ddd6] bg-white px-4 py-3 text-sm text-[#5f574f] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515]"
            >
            <button type="submit" class="bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">搜索</button>
        </form>
    </section>

    @if (filled($query))
        <section class="mt-12 grid gap-x-10 gap-y-16 md:grid-cols-2 xl:grid-cols-3" style="column-gap: 2.5rem; row-gap: 4rem;">
            @forelse ($posts as $post)
                @include(\App\Support\SiteTheme::view('components.post-card', 'themes.xiaofang.components.post-card'), ['post' => $post, 'compact' => true])
            @empty
                <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-10 text-sm text-[#78716c] md:col-span-2 xl:col-span-3">
                    没有找到和 “{{ $query }}” 相关的已发布内容，可以换个关键词再试。
                </div>
            @endforelse
        </section>

        <div class="site-pagination mt-10">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
