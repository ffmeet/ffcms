@php
    $compact = $compact ?? false;
    $mode = $mode ?? 'default';
@endphp

<article class="group overflow-hidden rounded-[28px] border border-white/70 bg-white/90 shadow-[0_18px_60px_rgba(15,23,42,0.06)] transition hover:-translate-y-1 hover:shadow-[0_26px_90px_rgba(15,23,42,0.10)]">
    @if ($post->cover_image_url)
        <a href="{{ route('posts.show', $post->slug) }}" class="block overflow-hidden border-b border-slate-200/70 bg-slate-100">
            <img
                src="{{ $post->cover_image_url }}"
                alt="{{ $post->title }}"
                class="{{ $compact ? 'h-44' : 'h-56' }} w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            >
        </a>
    @endif

    <div class="{{ $compact ? 'p-5' : 'p-6' }}">
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">
            @if ($post->isFlashModel())
                <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">快讯</span>
            @endif
            @if ($post->category)
                <a href="{{ route('categories.show', $post->category->slug) }}" class="transition hover:text-slate-900">{{ $post->category->name }}</a>
            @else
                <span>未分类</span>
            @endif
            <span class="text-slate-300">•</span>
            <span>{{ optional($post->published_at)->format($post->isFlashModel() ? 'm-d H:i' : 'Y-m-d') }}</span>
        </div>

        <h3 class="mt-4 {{ $compact ? 'text-xl leading-8' : 'text-2xl leading-9' }} font-semibold tracking-tight text-slate-950">
            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
        </h3>

        @if ($post->summary)
            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $post->summary }}</p>
        @endif

        <div class="mt-5 flex flex-wrap gap-4 text-sm text-slate-500">
            <span>作者 {{ $post->display_author }}</span>
            <span>浏览 {{ $post->statistics?->views ?? 0 }}</span>
            @unless ($post->isFlashModel())
                <span>评论 {{ $post->statistics?->comments_count ?? 0 }}</span>
            @endunless
        </div>
    </div>
</article>
