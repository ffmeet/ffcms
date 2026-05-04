@php
    $deliveryLabel = match($product->delivery_type) {
        'download' => '下载',
        'membership' => '会员权益',
        'event-access' => '活动资格',
        'physical' => '实体商品',
        default => '商品',
    };
@endphp

<article class="group" style="display: flex; flex-direction: column; gap: 1.75rem;">
    @if ($product->cover_image_url)
        <a href="{{ route('shop.show', $product->slug) }}" class="block overflow-hidden bg-[#f3f0ea]">
            <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-full object-cover transition duration-500 group-hover:scale-[1.02]" style="height: 220px; object-position: center top;">
        </a>
    @endif

    <div style="padding-bottom: 0.5rem;">
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
            <span>{{ $deliveryLabel }}</span>
            @if ($product->stock !== null)
                <span class="text-[#c9c2b9]">•</span>
                <span>{{ $product->stock > 0 ? '库存 ' . $product->stock : '已售罄' }}</span>
            @endif
        </div>

        <h3 class="mt-5 font-serif font-semibold text-[#151515]" style="font-size:1.9rem;line-height:1.12;letter-spacing:-0.02em;">
            <a href="{{ route('shop.show', $product->slug) }}">{{ $product->title }}</a>
        </h3>

        @if ($product->summary)
            <p class="mt-5 text-[15px] leading-8 text-[#5f574f]">{{ $product->summary }}</p>
        @endif

        <div class="mt-7 flex items-end justify-between gap-4">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">Price</div>
                <div class="mt-2 flex items-center gap-3">
                    <span class="text-2xl font-semibold text-[#151515]">¥{{ number_format((float) $product->price, 2) }}</span>
                    @if ($product->compare_at_price)
                        <span class="text-sm text-[#a8a29e] line-through">¥{{ number_format((float) $product->compare_at_price, 2) }}</span>
                    @endif
                </div>
            </div>

            <a href="{{ route('shop.show', $product->slug) }}" class="text-sm font-semibold text-[#151515] transition hover:text-[#5f574f]">查看详情</a>
        </div>
    </div>
</article>
