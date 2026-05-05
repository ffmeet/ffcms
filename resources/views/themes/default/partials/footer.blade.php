@php
    $settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults());
    $footerNavigation = $settings->footer_navigation ?: \App\Models\SiteSetting::defaults()['footer_navigation'];
    $socialLinks = $settings->social_links ?: \App\Models\SiteSetting::defaults()['social_links'];
@endphp

<footer class="mt-16 border-t border-slate-200/80 pt-8">
    <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr_0.8fr]">
        <div class="rounded-[28px] border border-white/70 bg-white/88 p-6 shadow-[0_20px_60px_rgba(15,23,42,0.06)]">
            <div class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">About</div>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950">{{ $settings->site_name }}</h2>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">{{ $settings->site_description }}</p>
        </div>

        <div class="rounded-[28px] border border-white/70 bg-white/88 p-6 shadow-[0_20px_60px_rgba(15,23,42,0.06)]">
            <div class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Navigate</div>
            <div class="mt-4 grid gap-2 text-sm text-slate-700">
                @foreach ($footerNavigation as $item)
                    <a href="{{ $item['url'] ?? '#' }}" class="rounded-2xl px-3 py-2 transition hover:bg-slate-100 hover:text-slate-950">
                        {{ $item['label'] ?? '链接' }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="rounded-[28px] border border-white/70 bg-white/88 p-6 shadow-[0_20px_60px_rgba(15,23,42,0.06)]">
            <div class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Follow</div>
            <div class="mt-4 grid gap-2 text-sm text-slate-700">
                @foreach ($socialLinks as $item)
                    <a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noreferrer" class="rounded-2xl px-3 py-2 transition hover:bg-slate-100 hover:text-slate-950">
                        {{ $item['label'] ?? '链接' }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 pb-10 text-sm text-slate-500">
        {{ $settings->footer_copyright }}
    </div>
</footer>
