@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '商店系统 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'site.partials.feature-hero'), [
        'eyebrow' => 'Shop',
        'title' => '商店系统',
        'description' => '这里先承接通用商品浏览体验，不把前台锁死成纯数字商品。后续无论接入数字权益还是实体商品，都会基于同一套商品底座延展。',
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
        <section class="site-section-shell mt-8 p-6">
            <div class="site-section-header mb-6">
                <div>
                <p class="site-section-kicker">Highlights</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">精选商品</h2>
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($featuredProducts as $product)
                    @include(\App\Support\SiteTheme::view('components.product-card', 'site.partials.product-card'), ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-8">
        <div class="site-section-header mb-6">
            <div>
                <p class="site-section-kicker">Catalog</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">全部商品</h2>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($products as $product)
                @include(\App\Support\SiteTheme::view('components.product-card', 'site.partials.product-card'), ['product' => $product])
            @empty
                <div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-sm text-slate-500 md:col-span-2 xl:col-span-3">
                    当前还没有已上架的商品。
                </div>
            @endforelse
        </div>

        <div class="site-pagination mt-6">
            {{ $products->links() }}
        </div>
    </section>
@endsection
