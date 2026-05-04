@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '#' . $tag->name . ' - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'themes.xiaofang.components.feature-hero'), [
        'eyebrow' => 'Tag',
        'title' => '# ' . $tag->name,
        'description' => '围绕这个标签继续浏览相关文章、专题和快讯，让同一兴趣线索可以在当前主题里自然延展。',
        'actions' => [
            ['label' => '搜索这个标签', 'url' => route('search', ['q' => $tag->name]), 'variant' => 'primary'],
        ],
    ])

    <section class="mt-12 grid gap-x-10 gap-y-16 md:grid-cols-2 xl:grid-cols-3" style="column-gap: 2.5rem; row-gap: 4rem;">
        @forelse ($posts as $post)
            @include(\App\Support\SiteTheme::view('components.post-card', 'themes.xiaofang.components.post-card'), ['post' => $post])
        @empty
            <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-10 text-sm text-[#78716c] md:col-span-2 xl:col-span-3">
                当前标签下暂时还没有已发布内容。
            </div>
        @endforelse
    </section>

    <div class="site-pagination mt-10">
        {{ $posts->links() }}
    </div>
@endsection
