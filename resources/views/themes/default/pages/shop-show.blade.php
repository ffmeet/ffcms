@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => $product->title . ' - 商店 - ' . ($siteSettings->site_name ?? '年度科技先生')])

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
        $deliveryChipClass = match($product->delivery_type) {
            'download', 'membership' => 'site-chip--brand',
            'event-access' => 'site-chip--emerald',
            'physical' => 'site-chip--amber',
            default => 'site-chip--slate',
        };
        $canPurchase = $currentUser?->hasMemberPermission('shop.access') ?? false;
    @endphp

    <section class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="site-feature-shell overflow-hidden">
            @if ($product->cover_image_url)
                <div class="border-b border-slate-200 bg-slate-100">
                    <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="h-[260px] w-full object-cover sm:h-[360px] lg:h-[440px]">
                </div>
            @endif
            <div class="p-8 lg:p-10">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="site-chip {{ $deliveryChipClass }}">{{ $deliveryLabel }}</span>
                    <span class="site-chip {{ $product->status === 'published' ? 'site-chip--emerald' : 'site-chip--slate' }}">{{ $product->status === 'published' ? '已上架' : $product->status }}</span>
                    @if ($product->stock !== null)
                        <span class="site-chip {{ $product->stock > 0 ? 'site-chip--slate' : 'site-chip--rose' }}">
                            {{ $product->stock > 0 ? '库存 ' . $product->stock : '已售罄' }}
                        </span>
                    @endif
                </div>
                <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $product->title }}</h1>
                @if ($product->summary)
                    <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $product->summary }}</p>
                @endif
                <div class="site-prose mt-8 max-w-none">
                    {!! filled($product->content) ? nl2br(e($product->content)) : '<p>当前商品还没有详情说明。</p>' !!}
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="site-section-shell p-6">
                <p class="site-section-kicker">Purchase</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">商品信息</h2>
                <div class="mt-6 space-y-4">
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-400">售价</div>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-3xl font-semibold text-slate-950">¥{{ number_format((float) $product->price, 2) }}</span>
                            @if ($product->compare_at_price)
                                <span class="text-sm text-slate-400 line-through">¥{{ number_format((float) $product->compare_at_price, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
                        <div>库存：{{ $product->stock ?? '不限' }}</div>
                        <div>状态：{{ $product->status === 'published' ? '已上架' : $product->status }}</div>
                        <div>交付形式：{{ $deliveryLabel }}</div>
                    </div>
                    <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-5 py-5">
                        <div class="flex flex-wrap gap-2">
                            <span class="site-chip site-chip--emerald">支持创建订单</span>
                            <span class="site-chip site-chip--slate">支付流程可模拟</span>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-slate-500">购买动作已经可以生成待处理订单，这一版先完成订单承接，下一阶段接正式支付。</p>
                    </div>
                    @auth
                        @if ($canPurchase)
                            <form method="POST" action="{{ route('shop.purchase', $product->slug) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">立即创建订单</button>
                            </form>
                        @else
                            <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-5 text-sm leading-7 text-amber-800">
                                当前会员组还没有商店权限，请先升级会员组或联系管理员开通。
                            </div>
                            <a href="{{ route('pricing') }}" class="block w-full rounded-full border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950">查看会员计划</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full rounded-full bg-slate-950 px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-blue-600">登录后购买</a>
                    @endauth
                </div>
            </section>

            @if ($relatedProducts->isNotEmpty())
                <section class="site-section-shell p-6">
                    <p class="site-section-kicker">Related</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">相关商品</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($relatedProducts as $item)
                            <a href="{{ route('shop.show', $item->slug) }}" class="block rounded-[24px] border border-slate-200/80 bg-slate-50 px-4 py-4 transition hover:border-slate-900 hover:bg-white">
                                <div class="text-base font-semibold text-slate-950">{{ $item->title }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                    <span>¥{{ number_format((float) $item->price, 2) }}</span>
                                    <span class="text-slate-300">·</span>
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
