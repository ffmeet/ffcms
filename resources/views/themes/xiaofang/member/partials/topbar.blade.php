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

<section class="relative left-1/2 right-1/2 -mx-[50vw] mb-5 w-screen border-b border-[#202020] bg-[#111111] text-white shadow-sm">
    <div class="mx-auto flex max-w-[90rem] flex-col gap-3 px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ $memberEntries['dashboard']['url'] }}" class="flex items-center gap-3 pr-3">
                @if (filled($frontendLogoUrl))
                    <img src="{{ $frontendLogoUrl }}" alt="{{ $settings->site_name }}" class="h-10 w-10 object-cover">
                @else
                    <span class="font-serif text-3xl font-semibold leading-none tracking-[0.08em] text-white">{{ $settings->logo_text ?: '芳' }}</span>
                @endif
                <span class="text-sm font-semibold tracking-wide text-white">{{ $settings->site_name ?? '小芳侠' }}会员中心</span>
            </a>

            @foreach ($tabs as $tab)
                <a
                    href="{{ $tab['url'] }}"
                    class="inline-flex items-center justify-center px-3.5 py-1.5 text-sm font-semibold transition {{ request()->routeIs($tab['route']) || (str_ends_with($tab['route'], '.index') && request()->routeIs(str_replace('.index', '.*', $tab['route']))) ? 'text-white' : 'text-white/68 hover:text-white' }}"
                >
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ $memberEntries['create_post']['url'] }}"
                class="inline-flex items-center justify-center border border-white/14 bg-white/8 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/12"
            >
                {{ $memberEntries['create_post']['label'] }}
            </a>
            <div class="flex items-center gap-3 px-1 py-1">
                @if ($memberAvatarSmall)
                    <img src="{{ $memberAvatarSmall }}" alt="{{ $memberUser?->public_display_name }}" class="h-10 w-10 rounded-full object-cover">
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[radial-gradient(circle_at_30%_30%,#fb923c,#1d4ed8_70%,#0f172a)] text-sm font-semibold text-white">
                        {{ str($memberUser?->public_display_name ?? 'M')->substr(0, 1)->upper() }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-white">{{ $memberUser?->public_display_name }}</div>
                    <div class="text-xs text-white/62">{{ $memberUser?->memberGroup?->name ?? '会员用户' }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
