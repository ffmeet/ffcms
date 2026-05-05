@php
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
@endphp

<article class="site-card group">
    @if ($product->cover_image_url)
        <a href="{{ route('shop.show', $product->slug) }}" class="block overflow-hidden border-b border-slate-200/70 bg-slate-100">
            <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        </a>
    @endif

    <div class="p-6">
        <div class="flex flex-wrap items-center gap-2">
            <span class="site-chip {{ $deliveryChipClass }}">{{ $deliveryLabel }}</span>
            @if ($product->stock !== null)
                <span class="site-chip {{ $product->stock > 0 ? 'site-chip--slate' : 'site-chip--rose' }}">
                    {{ $product->stock > 0 ? '库存 ' . $product->stock : '已售罄' }}
                </span>
            @endif
        </div>

        <h3 class="mt-4 text-2xl font-semibold leading-9 tracking-tight text-slate-950">
            <a href="{{ route('shop.show', $product->slug) }}">{{ $product->title }}</a>
        </h3>

        @if ($product->summary)
            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $product->summary }}</p>
        @endif

        <div class="mt-5 flex items-end justify-between gap-4">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Price</div>
                <div class="mt-2 flex items-center gap-3">
                    <span class="text-2xl font-semibold text-slate-950">¥{{ number_format((float) $product->price, 2) }}</span>
                    @if ($product->compare_at_price)
                        <span class="text-sm text-slate-400 line-through">¥{{ number_format((float) $product->compare_at_price, 2) }}</span>
                    @endif
                </div>
            </div>

            <a href="{{ route('shop.show', $product->slug) }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600">查看详情</a>
        </div>
    </div>
</article>
