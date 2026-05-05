@php
    $settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults());
    $frontendLogoUrl = filled($settings->frontend_logo_path) ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->frontend_logo_path) : null;
    $navigation = $settings->primary_navigation ?: \App\Models\SiteSetting::defaults()['primary_navigation'];
    $currentUser = auth()->user();
    $canAccessMemberCenter = $currentUser?->hasMemberPermission('member.center') ?? false;
    $canAccessAdmin = $currentUser?->is_staff_account ?? false;
    $previewTheme = \App\Support\SiteTheme::previewTheme();
    $publicEntries = $publicRouteEntries ?? \App\Support\RouteRuleManager::publicEntries($settings->toArray());
@endphp

<header class="mb-10 border-b border-white/60 pb-6">
    @if ($previewTheme)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-[22px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <div>当前正在预览主题：<span class="font-semibold">{{ \App\Support\SiteTheme::themeCard($previewTheme)['label'] }}</span></div>
            <a href="{{ \App\Support\SiteTheme::clearPreviewUrl() }}" class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-amber-800 transition hover:bg-amber-100">结束预览</a>
        </div>
    @endif

    <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div class="flex flex-col gap-5">
            <a href="{{ route('site.home') }}" class="inline-flex items-center gap-4">
                @if (filled($frontendLogoUrl))
                    <img src="{{ $frontendLogoUrl }}" alt="{{ $settings->site_name }}" class="h-14 w-14 rounded-2xl object-cover shadow-[0_18px_44px_rgba(148,163,184,0.22)]">
                @else
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[linear-gradient(145deg,#0f172a_0%,#2249b7_48%,#6ea8ff_100%)] text-2xl font-bold text-white shadow-[0_18px_44px_rgba(37,99,235,0.24)]">
                        {{ $settings->logo_text ?: '帝' }}
                    </span>
                @endif
                <span class="flex flex-col">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">{{ $settings->hero_eyebrow ?: 'CONTENT PORTAL' }}</span>
                    <span class="mt-1 text-3xl font-semibold tracking-tight text-slate-950">{{ $settings->site_name }}</span>
                    <span class="mt-1 text-sm text-slate-600">{{ $settings->site_tagline }}</span>
                </span>
            </a>

            <form method="GET" action="{{ $publicEntries['search']['url'] }}" class="flex w-full max-w-xl items-center gap-3 rounded-full border border-slate-200/80 bg-white/92 px-4 py-3 shadow-sm shadow-slate-200/60 backdrop-blur">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="搜索文章、标签、专题和作者"
                    class="min-w-0 flex-1 bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400"
                >
                <button type="submit" class="rounded-full bg-slate-950 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-blue-600">
                    搜索
                </button>
            </form>
        </div>

        <div class="flex flex-col gap-4 xl:items-end">
            <nav class="flex flex-wrap items-center gap-2 text-sm">
                @foreach ($navigation as $item)
                    @php
                        $url = $item['url'] ?? '#';
                        $active = $url === '/' ? request()->routeIs('site.home') : str_starts_with(request()->path(), trim($url, '/'));
                    @endphp
                    <a
                        href="{{ $url }}"
                        class="rounded-full px-4 py-2.5 font-medium transition {{ $active ? 'bg-slate-950 text-white shadow-sm' : 'bg-white/80 text-slate-700 hover:bg-white hover:text-slate-950' }}"
                    >
                        {{ $item['label'] ?? '链接' }}
                    </a>
                @endforeach
            </nav>

            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a class="rounded-full border border-slate-200 bg-white/85 px-4 py-2.5 font-medium text-slate-700 transition hover:border-slate-900 hover:text-slate-950" href="{{ $publicEntries['pricing']['url'] }}">{{ $publicEntries['pricing']['label'] }}</a>
                <a class="rounded-full border border-slate-200 bg-white/85 px-4 py-2.5 font-medium text-slate-700 transition hover:border-slate-900 hover:text-slate-950" href="{{ $publicEntries['events']['url'] }}">{{ $publicEntries['events']['label'] }}</a>
                <a class="rounded-full border border-slate-200 bg-white/85 px-4 py-2.5 font-medium text-slate-700 transition hover:border-slate-900 hover:text-slate-950" href="{{ $publicEntries['shop']['url'] }}">{{ $publicEntries['shop']['label'] }}</a>
                @auth
                    @if ($canAccessMemberCenter)
                        <a class="rounded-full border border-slate-200 bg-white/85 px-4 py-2.5 font-medium text-slate-700 transition hover:border-slate-900 hover:text-slate-950" href="{{ $publicEntries['member']['url'] }}">{{ $publicEntries['member']['label'] }}</a>
                    @endif
                    @if ($canAccessAdmin)
                        <a class="rounded-full bg-slate-950 px-4 py-2.5 font-medium text-white transition hover:bg-blue-600" href="{{ $publicEntries['admin']['url'] }}">{{ $publicEntries['admin']['label'] }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-full border border-slate-200 bg-white/85 px-4 py-2.5 font-medium text-slate-700 transition hover:border-rose-300 hover:text-rose-700" type="submit">退出</button>
                    </form>
                @else
                    <a class="rounded-full border border-slate-200 bg-white/85 px-4 py-2.5 font-medium text-slate-700 transition hover:border-slate-900 hover:text-slate-950" href="{{ $publicEntries['login']['url'] }}">{{ $publicEntries['login']['label'] }}</a>
                    <a class="rounded-full bg-slate-950 px-4 py-2.5 font-medium text-white transition hover:bg-blue-600" href="{{ $publicEntries['register']['url'] }}">{{ $publicEntries['register']['label'] }}</a>
                    <a class="rounded-full border border-slate-200 bg-white/85 px-4 py-2.5 font-medium text-slate-700 transition hover:border-slate-900 hover:text-slate-950" href="{{ $publicEntries['admin']['url'] }}">{{ $publicEntries['admin']['label'] }}</a>
                @endauth
            </div>
        </div>
    </div>
</header>
