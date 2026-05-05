@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => $category->name . ' - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'themes.xiaofang.components.feature-hero'), [
        'eyebrow' => 'Category',
        'title' => $category->name,
        'description' => $category->description ?: '当前栏目承接同主题内容、专题文章与快讯归档，延续首页的刊物式阅读节奏。',
    ])

    <section class="mt-12 grid gap-x-10 gap-y-16 md:grid-cols-2 xl:grid-cols-3" style="column-gap: 2.5rem; row-gap: 4rem;">
        @forelse ($posts as $post)
            @include(\App\Support\SiteTheme::view('components.post-card', 'themes.xiaofang.components.post-card'), ['post' => $post])
        @empty
            <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-10 text-sm text-[#78716c] md:col-span-2 xl:col-span-3">
                该栏目下暂时还没有已发布内容。
            </div>
        @endforelse
    </section>

    <div class="site-pagination mt-10">
        {{ $posts->links() }}
    </div>
@endsection
