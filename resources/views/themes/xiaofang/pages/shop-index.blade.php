@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '商店系统 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'themes.xiaofang.components.feature-hero'), [
        'eyebrow' => 'Shop',
        'title' => '商店系统',
        'description' => '商品、权益与活动资格继续收拢到同一套公开浏览链路里，保持与内容列表一致的刊物式阅读节奏。',
        'actions' => [
            ['label' => '查看会员计划', 'url' => route('pricing'), 'variant' => 'primary'],
            ['label' => '查看活动', 'url' => route('events.index')],
        ],
        'metrics' => [
            ['label' => '已上架商品', 'value' => $shopMetrics['published_products']],
            ['label' => '精选商品', 'value' => $shopMetrics['featured_products']],
            ['label' => '数字权益', 'value' => $shopMetrics['download_products']],
        ],
    ])

    @if ($featuredProducts->isNotEmpty())
        <section class="mt-12">
            <div class="mb-8 text-center">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Highlights</p>
                <h2 class="mt-3 font-serif text-4xl font-semibold text-[#151515]">精选商品</h2>
            </div>
            <div class="grid gap-x-10 gap-y-16 md:grid-cols-2 xl:grid-cols-3" style="column-gap: 2.5rem; row-gap: 4rem;">
                @foreach ($featuredProducts as $product)
                    @include(\App\Support\SiteTheme::view('components.product-card', 'themes.xiaofang.components.product-card'), ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-14">
        <div class="mb-8 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Catalog</p>
            <h2 class="mt-3 font-serif text-4xl font-semibold text-[#151515]">全部商品</h2>
        </div>

        <div class="grid gap-x-10 gap-y-16 md:grid-cols-2 xl:grid-cols-3" style="column-gap: 2.5rem; row-gap: 4rem;">
            @forelse ($products as $product)
                @include(\App\Support\SiteTheme::view('components.product-card', 'themes.xiaofang.components.product-card'), ['product' => $product])
            @empty
                <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-10 text-sm text-[#78716c] md:col-span-2 xl:col-span-3">
                    当前还没有已上架的商品。
                </div>
            @endforelse
        </div>

        <div class="site-pagination mt-10">
            {{ $products->links() }}
        </div>
    </section>
@endsection
