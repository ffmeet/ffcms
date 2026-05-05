@php
    $statusLabel = match($event->status) {
        'registration-open' => '报名中',
        'sold-out' => '已满员',
        'finished' => '已结束',
        default => '活动',
    };
    $statusChipClass = match($event->status) {
        'registration-open' => 'site-chip--emerald',
        'sold-out' => 'site-chip--amber',
        'finished' => 'site-chip--slate',
        default => 'site-chip--brand',
    };
@endphp

<article class="site-card group">
    @if ($event->cover_image_url)
        <a href="{{ route('events.show', $event->slug) }}" class="block overflow-hidden border-b border-slate-200/70 bg-slate-100">
            <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        </a>
    @endif

    <div class="p-6">
        <div class="flex flex-wrap items-center gap-2">
            <span class="site-chip {{ $statusChipClass }}">{{ $statusLabel }}</span>
            <span class="site-chip {{ $event->is_paid ? 'site-chip--amber' : 'site-chip--brand' }}">
                {{ $event->is_paid ? '付费活动' : '免费活动' }}
            </span>
            @if ($event->memberGroup)
                <span class="site-chip site-chip--slate">{{ $event->memberGroup->name }}</span>
            @endif
            @if ($event->starts_at)
                <span class="site-chip site-chip--slate">{{ $event->starts_at->format('Y-m-d H:i') }}</span>
            @endif
        </div>

        <h3 class="mt-4 text-2xl font-semibold leading-9 tracking-tight text-slate-950">
            <a href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a>
        </h3>

        @if ($event->summary)
            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $event->summary }}</p>
        @endif

        <div class="mt-5 flex items-end justify-between gap-4">
            <div class="space-y-1 text-sm text-slate-500">
                @if ($event->location)
                    <div>{{ $event->location }}</div>
                @endif
                @if ($event->capacity)
                    <div>名额上限 {{ $event->capacity }}</div>
                @endif
            </div>

            <a href="{{ route('events.show', $event->slug) }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600">查看活动</a>
        </div>
    </div>
</article>
