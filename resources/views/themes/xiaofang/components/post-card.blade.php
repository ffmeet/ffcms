@php
    $compact = $compact ?? false;
    $titleStyle = $compact
        ? 'font-size:1.75rem;line-height:1.16;'
        : 'font-size:1.95rem;line-height:1.12;';
@endphp

<article class="group" style="display: flex; flex-direction: column; gap: 1.75rem;">
    @if ($post->cover_image_url)
        <a href="{{ route('posts.show', $post->slug) }}" class="block overflow-hidden bg-[#f3f0ea]">
            <img
                src="{{ $post->cover_image_url }}"
                alt="{{ $post->title }}"
                class="w-full object-cover transition duration-500 group-hover:scale-[1.02]"
                style="height: 220px; object-position: center top;"
            >
        </a>
    @endif

    <div style="padding-bottom: 0.5rem;">
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
            @if ($post->isFlashModel())
                <span>快讯</span>
            @endif
            <span>{{ $post->category?->name ?? '未分类' }}</span>
            <span class="text-[#c9c2b9]">•</span>
            <span>{{ optional($post->published_at)->format($post->isFlashModel() ? 'm-d H:i' : 'Y-m-d') }}</span>
        </div>

        <h3 class="mt-5 font-serif font-semibold text-[#151515]" style="letter-spacing:-0.02em; {{ $titleStyle }}">
            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
        </h3>

        @if ($post->summary)
            <p class="mt-5 text-[15px] leading-8 text-[#5f574f]">{{ $post->summary }}</p>
        @endif

        <div class="mt-7 text-sm text-[#8b8175]">
            By {{ $post->display_author }}
            @unless ($post->isFlashModel())
                · {{ max(1, (int) ceil(str_word_count(strip_tags($post->content ?? $post->summary ?? '')) / 250)) }} min read
            @endunless
        </div>
    </div>
</article>
