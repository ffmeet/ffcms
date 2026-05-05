<section class="site-feature-shell p-8 lg:p-10">
    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_320px] xl:items-end">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">{{ $eyebrow }}</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $title }}</h1>
            <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $description }}</p>

            @if (! empty($actions ?? []))
                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach ($actions as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="{{ ($action['variant'] ?? 'secondary') === 'primary'
                                ? 'rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600'
                                : 'rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950' }}"
                        >
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if (! empty($metrics ?? []))
            <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                @foreach ($metrics as $metric)
                    <article class="rounded-[22px] border border-slate-200 bg-slate-50/80 px-4 py-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">{{ $metric['label'] }}</div>
                        <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $metric['value'] }}</div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
