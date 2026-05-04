@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => $product->title . ' - 商店 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @php
        $currentUser = auth()->user();
        $deliveryLabel = match($product->delivery_type) {
            'download' => '下载',
            'membership' => '会员权益',
            'event-access' => '活动资格',
            'physical' => '实体商品',
            default => '商品',
        };
        $canPurchase = $currentUser?->hasMemberPermission('shop.access') ?? false;
    @endphp

    @include(\App\Support\SiteTheme::view('components.feature-hero', 'themes.xiaofang.components.feature-hero'), [
        'eyebrow' => 'Shop',
        'title' => $product->title,
        'description' => $product->summary ?: '商品详情页继续沿用前台编辑式展示，让内容介绍、价格信息和购买动作保持统一的阅读节奏。',
        'actions' => [
            ['label' => '查看全部商品', 'url' => route('shop.index'), 'variant' => 'primary'],
        ],
        'metrics' => [
            ['label' => '交付', 'value' => $deliveryLabel],
            ['label' => '价格', 'value' => '¥'.number_format((float) $product->price, 2)],
            ['label' => '库存', 'value' => $product->stock ?? '不限'],
        ],
    ])

    <section class="mt-10 grid gap-8 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="overflow-hidden border border-[#e5dfd7] bg-white">
            @if ($product->cover_image_url)
                <div class="border-b border-[#ece7e0] bg-[#f5f3ef]">
                    <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="h-[260px] w-full object-cover sm:h-[360px] lg:h-[440px]">
                </div>
            @endif
            <div class="p-8 lg:p-10">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="border border-[#d8d1c8] bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#151515]">{{ $deliveryLabel }}</span>
                    <span class="border border-[#d8d1c8] bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#151515]">{{ $product->status === 'published' ? '已上架' : $product->status }}</span>
                    @if ($product->stock !== null)
                        <span class="border border-[#d8d1c8] bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#151515]">
                            {{ $product->stock > 0 ? '库存 ' . $product->stock : '已售罄' }}
                        </span>
                    @endif
                </div>
                <div class="site-prose mt-8 max-w-none">
                    {!! filled($product->content) ? nl2br(e($product->content)) : '<p>当前商品还没有详情说明。</p>' !!}
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="border border-[#e5dfd7] bg-[#fcfaf7] p-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Purchase</p>
                <h2 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-[#151515]">商品信息</h2>
                <div class="mt-6 space-y-4">
                    <div class="border border-[#e5dfd7] bg-white px-5 py-5">
                        <div class="text-xs uppercase tracking-[0.18em] text-[#a8a29e]">售价</div>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-3xl font-black text-[#181512]">¥{{ number_format((float) $product->price, 2) }}</span>
                            @if ($product->compare_at_price)
                                <span class="text-sm text-[#a8a29e] line-through">¥{{ number_format((float) $product->compare_at_price, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="border border-[#e5dfd7] bg-white px-5 py-5 text-sm leading-7 text-[#5f574f]">
                        <div>库存：{{ $product->stock ?? '不限' }}</div>
                        <div>状态：{{ $product->status === 'published' ? '已上架' : $product->status }}</div>
                        <div>交付形式：{{ $deliveryLabel }}</div>
                    </div>
                    <div class="rounded-[1.6rem] border border-dashed border-[#d6d3d1] bg-[#fafaf9] px-5 py-5">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-[#ecfdf5] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#047857]">支持创建订单</span>
                            <span class="rounded-full bg-[#f5f5f4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6b6256]">支付流程可模拟</span>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-[#78716c]">购买动作已经可以生成待处理订单，这一版先完成订单承接，下一阶段接正式支付。</p>
                    </div>
                    @auth
                        @if ($canPurchase)
                            <form method="POST" action="{{ route('shop.purchase', $product->slug) }}">
                                @csrf
                                <button type="submit" class="w-full bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">立即创建订单</button>
                            </form>
                        @else
                            <div class="rounded-[1.6rem] border border-amber-200 bg-amber-50 px-5 py-5 text-sm leading-7 text-amber-800">
                                当前会员组还没有商店权限，请先升级会员组或联系管理员开通。
                            </div>
                            <a href="{{ route('pricing') }}" class="block w-full rounded-full border border-[#e7d8c9] bg-white px-5 py-3 text-center text-sm font-semibold text-[#6b6256] transition hover:border-[#fdba74] hover:text-[#9a3412]">查看会员计划</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-[#151515] px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">登录后购买</a>
                    @endauth
                </div>
            </section>

            @if ($relatedProducts->isNotEmpty())
                <section class="border border-[#e5dfd7] bg-[#fcfaf7] p-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Related</p>
                    <h2 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-[#151515]">相关商品</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($relatedProducts as $item)
                            <a href="{{ route('shop.show', $item->slug) }}" class="block border border-[#e5dfd7] bg-white px-4 py-4 transition hover:border-[#151515]">
                                <div class="text-base font-bold text-[#181512]">{{ $item->title }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-[#78716c]">
                                    <span>¥{{ number_format((float) $item->price, 2) }}</span>
                                    <span class="text-[#d6d3d1]">·</span>
                                    <span>{{ match($item->delivery_type) {
                                        'download' => '下载',
                                        'membership' => '会员权益',
                                        'event-access' => '活动资格',
                                        'physical' => '实体商品',
                                        default => '商品',
                                    } }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </section>
@endsection
