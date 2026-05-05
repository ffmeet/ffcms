<section class="rounded-[30px] border border-sky-100/70 bg-white/88 p-6 shadow-[0_22px_60px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            @if (! empty($eyebrow ?? null))
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-600">{{ $eyebrow }}</p>
            @endif
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
            @if (! empty($description ?? null))
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $description }}</p>
            @endif
        </div>

        @if (! empty($actions ?? null))
            <div class="flex flex-wrap gap-3">
                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ ($action['variant'] ?? 'ghost') === 'primary'
                            ? 'inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-blue-600 hover:to-slate-900'
                            : 'inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700' }}"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
