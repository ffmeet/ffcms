<section class="overflow-hidden rounded-[32px] border border-[#e5e7eb] bg-white p-6 shadow-[0_22px_60px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            @if (! empty($eyebrow ?? null))
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#6b7280]">{{ $eyebrow }}</p>
            @endif
            <h1 class="mt-2 text-3xl font-black tracking-tight text-[#181512]">{{ $title }}</h1>
            @if (! empty($description ?? null))
                <p class="mt-2 max-w-2xl text-sm leading-7 text-[#5f574f]">{{ $description }}</p>
            @endif
        </div>

        @if (! empty($actions ?? null))
            <div class="flex flex-wrap gap-3">
                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ ($action['variant'] ?? 'ghost') === 'primary'
                            ? 'inline-flex items-center justify-center rounded-full bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]'
                            : 'inline-flex items-center justify-center rounded-full border border-[#e5e7eb] bg-white px-4 py-2 text-sm font-medium text-[#6b6256] transition hover:border-[#151515] hover:text-[#151515]' }}"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
