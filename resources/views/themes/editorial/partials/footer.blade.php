@php
    $settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults());
@endphp

<footer class="mt-16 border-t border-[#231f1a]/10 pt-8 pb-10">
    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-[28px] border border-[#231f1a]/10 bg-white/70 p-6">
            <div class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8a6f4d]">Editorial Theme</div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-[#231f1a]">{{ $settings->site_name }}</div>
            <p class="mt-3 text-sm leading-7 text-[#6b5a48]">{{ $settings->site_description }}</p>
        </div>
        <div class="rounded-[28px] border border-[#231f1a]/10 bg-white/70 p-6">
            <div class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8a6f4d]">Theme Note</div>
            <p class="mt-3 text-sm leading-7 text-[#6b5a48]">这是示例二主题骨架。未单独覆盖的页面会继续回退到默认主题页面，但仍然套用当前主题的 layout 与 header/footer。</p>
        </div>
    </div>
</footer>
