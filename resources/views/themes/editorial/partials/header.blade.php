@php
    $settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults());
    $navigation = $settings->primary_navigation ?: \App\Models\SiteSetting::defaults()['primary_navigation'];
    $currentUser = auth()->user();
    $canAccessMemberCenter = $currentUser?->hasMemberPermission('member.center') ?? false;
    $canAccessAdmin = $currentUser?->is_staff_account ?? false;
    $previewTheme = \App\Support\SiteTheme::previewTheme();
@endphp

<header class="mb-12 border-b border-[#231f1a]/10 pb-7">
    @if ($previewTheme)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-[22px] border border-[#d6c0a4] bg-[#fff7ed] px-4 py-3 text-sm text-[#7c4a03]">
            <div>当前正在预览主题：<span class="font-semibold">{{ \App\Support\SiteTheme::themeCard($previewTheme)['label'] }}</span></div>
            <a href="{{ \App\Support\SiteTheme::clearPreviewUrl() }}" class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#8a6f4d] transition hover:bg-[#f7efe4]">结束预览</a>
        </div>
    @endif

    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-5">
            <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8a6f4d]">
                <span class="rounded-full border border-[#8a6f4d]/20 bg-white/70 px-3 py-1.5">Editorial Theme</span>
                <span>{{ $settings->hero_eyebrow ?: 'CONTENT PORTAL' }}</span>
            </div>
            <a href="{{ route('site.home') }}" class="block">
                <div class="text-4xl font-semibold tracking-tight text-[#231f1a] sm:text-5xl">{{ $settings->site_name }}</div>
                <div class="mt-2 max-w-2xl text-sm leading-7 text-[#6b5a48]">{{ $settings->site_tagline }}</div>
            </a>
        </div>

        <div class="space-y-4 lg:text-right">
            <nav class="flex flex-wrap gap-2 lg:justify-end">
                @foreach ($navigation as $item)
                    @php
                        $url = $item['url'] ?? '#';
                        $active = $url === '/' ? request()->routeIs('site.home') : str_starts_with(request()->path(), trim($url, '/'));
                    @endphp
                    <a href="{{ $url }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ $active ? 'bg-[#231f1a] text-white' : 'bg-white/80 text-[#4f4338] hover:bg-white hover:text-[#231f1a]' }}">
                        {{ $item['label'] ?? '链接' }}
                    </a>
                @endforeach
            </nav>

            <div class="flex flex-wrap gap-2 lg:justify-end">
                @auth
                    @if ($canAccessMemberCenter)
                        <a href="{{ route('member.dashboard') }}" class="rounded-full border border-[#231f1a]/10 bg-white/80 px-4 py-2 text-sm font-medium text-[#4f4338] transition hover:bg-white hover:text-[#231f1a]">会员中心</a>
                    @endif
                    @if ($canAccessAdmin)
                        <a href="{{ url('/admin') }}" class="rounded-full bg-[#8a6f4d] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6f583d]">后台</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="rounded-full border border-[#231f1a]/10 bg-white/80 px-4 py-2 text-sm font-medium text-[#4f4338] transition hover:bg-white hover:text-[#231f1a]">登录</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-[#231f1a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#3a3128]">注册</a>
                    <a href="{{ url('/admin/login') }}" class="rounded-full border border-[#231f1a]/10 bg-white/80 px-4 py-2 text-sm font-medium text-[#4f4338] transition hover:bg-white hover:text-[#231f1a]">后台</a>
                @endauth
            </div>
        </div>
    </div>
</header>
