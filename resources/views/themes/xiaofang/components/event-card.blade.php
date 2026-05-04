@php
    $statusLabel = match($event->status) {
        'registration-open' => '报名中',
        'sold-out' => '已满员',
        'finished' => '已结束',
        default => '活动',
    };
@endphp

<article class="group" style="display: flex; flex-direction: column; gap: 1.75rem;">
    @if ($event->cover_image_url)
        <a href="{{ route('events.show', $event->slug) }}" class="block overflow-hidden bg-[#f3f0ea]">
            <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-full object-cover transition duration-500 group-hover:scale-[1.02]" style="height: 220px; object-position: center top;">
        </a>
    @endif

    <div style="padding-bottom: 0.5rem;">
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
            <span>{{ $statusLabel }}</span>
            <span class="text-[#c9c2b9]">•</span>
            <span>{{ $event->is_paid ? '付费活动' : '免费活动' }}</span>
            @if ($event->starts_at)
                <span class="text-[#c9c2b9]">•</span>
                <span>{{ $event->starts_at->format('Y-m-d') }}</span>
            @endif
        </div>

        <h3 class="mt-5 font-serif font-semibold text-[#151515]" style="font-size:1.9rem;line-height:1.12;letter-spacing:-0.02em;">
            <a href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a>
        </h3>

        @if ($event->summary)
            <p class="mt-5 text-[15px] leading-8 text-[#5f574f]">{{ $event->summary }}</p>
        @endif

        <div class="mt-7 flex flex-wrap gap-x-4 gap-y-2 text-sm text-[#8b8175]">
            @if ($event->location)
                <span>{{ $event->location }}</span>
            @endif
            @if ($event->capacity)
                <span>名额 {{ $event->capacity }}</span>
            @endif
            @if ($event->memberGroup)
                <span>{{ $event->memberGroup->name }}</span>
            @endif
        </div>
    </div>
</article>
