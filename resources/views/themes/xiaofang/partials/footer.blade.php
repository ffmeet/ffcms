@php($settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults()))

<footer class="relative left-1/2 right-1/2 -mx-[50vw] mt-0 w-screen bg-[#111111] text-white">
    <div class="mx-auto grid max-w-[1440px] gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.1fr_1fr] lg:px-8">
        <div class="space-y-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-white/45">{{ $settings->hero_eyebrow ?? 'EDITORIAL JOURNAL' }}</p>
            <h2 class="font-serif text-4xl font-semibold tracking-[0.08em]">{{ $settings->site_name ?? 'HOMA' }}</h2>
            @if (filled($settings->footer_copyright ?? null))
                <p class="text-sm leading-7 text-white/55">{{ $settings->footer_copyright }}</p>
            @endif
        </div>

        <div class="grid gap-8 sm:grid-cols-2">
            <div class="space-y-3">
                <p class="text-xs uppercase tracking-[0.24em] text-white/45">Navigation</p>
                <div class="grid gap-2 text-sm text-white/78">
                    @foreach (collect($settings->footer_navigation ?? []) as $item)
                        <a href="{{ $item['url'] ?? '#' }}" class="transition hover:text-white">{{ $item['label'] ?? '链接' }}</a>
                    @endforeach
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-xs uppercase tracking-[0.24em] text-white/45">Follow</p>
                <div class="grid gap-2 text-sm text-white/78">
                    @foreach (collect($settings->social_links ?? []) as $item)
                        <a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noreferrer" class="transition hover:text-white">{{ $item['label'] ?? '链接' }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>
