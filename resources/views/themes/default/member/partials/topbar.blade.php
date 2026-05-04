@php
    $settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults());
    $frontendLogoUrl = filled($settings->frontend_logo_path) ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->frontend_logo_path) : null;
    $memberUser = auth()->user();
    $memberAvatarSmall = $memberUser?->avatarUrl('small');
    $publicEntries = $publicRouteEntries ?? \App\Support\RouteRuleManager::publicEntries($settings->toArray());
    $memberEntries = $memberRouteEntries ?? \App\Support\RouteRuleManager::memberEntries($settings->toArray());
    $tabs = [
        ['label' => $publicEntries['home']['label'], 'url' => $publicEntries['home']['url'], 'route' => 'site.home'],
        ['label' => $memberEntries['dashboard']['label'], 'url' => $memberEntries['dashboard']['url'], 'route' => 'member.dashboard'],
        ['label' => $memberEntries['posts']['label'], 'url' => $memberEntries['posts']['url'], 'route' => 'member.posts.index'],
        ['label' => $memberEntries['comments']['label'], 'url' => $memberEntries['comments']['url'], 'route' => 'member.comments.index'],
    ];
@endphp

<section class="relative left-1/2 right-1/2 -mx-[50vw] mb-5 w-screen border-b border-slate-200/80 bg-white/96 shadow-sm">
    <div class="mx-auto flex max-w-[88rem] flex-col gap-3 px-4 py-2.5 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ $memberEntries['dashboard']['url'] }}" class="flex items-center gap-3 pr-3">
                @if (filled($frontendLogoUrl))
                    <img src="{{ $frontendLogoUrl }}" alt="{{ $settings->site_name }}" class="h-10 w-10 rounded-2xl object-cover shadow-[0_14px_30px_rgba(148,163,184,0.20)]">
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-blue-600 to-slate-900 text-base font-bold text-white shadow-[0_14px_30px_rgba(37,99,235,0.22)]">{{ $settings->logo_text ?: '帝' }}</span>
                @endif
                <span class="text-sm font-semibold tracking-wide text-slate-900">{{ $settings->site_name ?? '会员中心' }}会员中心</span>
            </a>

            @foreach ($tabs as $tab)
                <a
                    href="{{ $tab['url'] }}"
                    class="inline-flex items-center justify-center rounded-lg px-3.5 py-1.5 text-sm font-semibold transition {{ request()->routeIs($tab['route']) || (str_ends_with($tab['route'], '.index') && request()->routeIs(str_replace('.index', '.*', $tab['route']))) ? 'bg-sky-50 text-sky-700 shadow-sm ring-1 ring-sky-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                >
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ $memberEntries['create_post']['url'] }}"
                class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
            >
                {{ $memberEntries['create_post']['label'] }}
            </a>
            <div class="flex items-center gap-3 px-1 py-1">
                @if ($memberAvatarSmall)
                    <img src="{{ $memberAvatarSmall }}" alt="{{ $memberUser?->public_display_name }}" class="h-10 w-10 rounded-full object-cover">
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[radial-gradient(circle_at_30%_30%,#60a5fa,#1d4ed8_65%,#0f172a)] text-sm font-semibold text-white">
                        {{ str($memberUser?->public_display_name ?? 'M')->substr(0, 1)->upper() }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-slate-900">{{ $memberUser?->public_display_name }}</div>
                    <div class="text-xs text-slate-500">{{ $memberUser?->memberGroup?->name ?? '会员用户' }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
