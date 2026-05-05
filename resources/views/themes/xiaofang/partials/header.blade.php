@php
    $settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults());
    $navigation = $settings->primary_navigation ?: \App\Models\SiteSetting::defaults()['primary_navigation'];
    $frontendLogoUrl = filled($settings->frontend_logo_path) ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->frontend_logo_path) : null;
    $currentUser = auth()->user();
    $canAccessMemberCenter = $currentUser?->hasMemberPermission('member.center') ?? false;
    $canAccessAdmin = $currentUser?->is_staff_account ?? false;
    $previewTheme = \App\Support\SiteTheme::previewTheme();
    $publicEntries = $publicRouteEntries ?? \App\Support\RouteRuleManager::publicEntries($settings->toArray());
@endphp

@if ($previewTheme)
    <div class="mb-4 border border-[#e7dccf] bg-[#f8f4ee] px-4 py-3 text-sm text-[#3f3a35]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>当前正在预览主题：<span class="font-semibold">{{ \App\Support\SiteTheme::themeCard($previewTheme)['label'] }}</span></div>
            <a href="{{ \App\Support\SiteTheme::clearPreviewUrl() }}" class="inline-flex items-center border border-[#d8d1c8] bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-[#111111] transition hover:bg-[#f5f5f5]">结束预览</a>
        </div>
    </div>
@endif

<header class="relative left-1/2 right-1/2 -mx-[50vw] mb-10 w-screen border-b border-[#202020] bg-[#111111] text-white" data-xf-shell>
    <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-3 px-4 py-3.5 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-2.5 lg:gap-4">
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center text-white transition hover:bg-white/8"
                aria-label="打开最新活动侧边层"
                data-xf-events-trigger
            >
                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.45" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <a href="{{ route('site.home') }}" class="flex min-w-0 items-center gap-2.5">
                @if ($frontendLogoUrl)
                    <img src="{{ $frontendLogoUrl }}" alt="{{ $settings->site_name }}" class="h-11 w-11 object-cover">
                @else
                    <span class="font-serif text-4xl font-semibold leading-none tracking-[0.08em] text-white">{{ $settings->site_name ?? '小芳侠' }}</span>
                @endif
            </a>
        </div>

        <nav class="hidden items-center lg:flex">
            @foreach ($navigation as $item)
                @php
                    $url = $item['url'] ?? '#';
                    $active = $url === '/' ? request()->routeIs('site.home') : str_starts_with(request()->path(), trim($url, '/'));
                @endphp
                <a
                    href="{{ $url }}"
                    class="border-l border-white/10 px-6 py-1.5 text-sm font-semibold text-white/72 transition hover:text-white {{ $loop->first ? 'border-l-0' : '' }} {{ $active ? 'text-white' : '' }}"
                >
                    {{ $item['label'] ?? '链接' }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            <button type="button" class="hidden p-2 text-white/80 transition hover:text-white sm:inline-flex" aria-label="{{ $publicEntries['search']['label'] }}" data-xf-search-trigger>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
            </button>
            @auth
                @if ($canAccessMemberCenter)
                    <a href="{{ $publicEntries['member']['url'] }}" class="hidden text-sm font-medium text-white/88 transition hover:text-white md:inline-flex">{{ $publicEntries['member']['label'] }}</a>
                @endif
                @if ($canAccessAdmin)
                    <a href="{{ $publicEntries['admin']['url'] }}" class="hidden border border-white/12 bg-white/6 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10 md:inline-flex">{{ $publicEntries['admin']['label'] }}</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hidden text-sm font-medium text-white/88 transition hover:text-white md:inline-flex">退出</button>
                </form>
            @else
                <a href="{{ $publicEntries['login']['url'] }}" class="hidden text-sm font-medium text-white/88 transition hover:text-white md:inline-flex">{{ $publicEntries['login']['label'] }}</a>
                <a href="{{ $publicEntries['register']['url'] }}" class="inline-flex text-sm font-medium text-white/88 transition hover:text-white md:inline-flex">{{ $publicEntries['register']['label'] }}</a>
            @endauth

            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center text-white transition hover:bg-white/8 lg:hidden"
                aria-label="打开导航菜单"
                data-xf-mobile-nav-trigger
            >
                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.45" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <div class="hidden border-t border-white/10 px-4 py-3 lg:hidden" data-xf-mobile-nav>
        <nav class="grid gap-2">
            @foreach ($navigation as $item)
                <a href="{{ $item['url'] ?? '#' }}" class="px-1 py-2 text-sm font-semibold text-white/80 transition hover:text-white">{{ $item['label'] ?? '链接' }}</a>
            @endforeach
            <div class="mt-2 flex flex-wrap gap-2 border-t border-white/10 pt-3">
                <button type="button" class="border border-white/12 px-4 py-2 text-sm font-medium text-white" data-xf-search-trigger>{{ $publicEntries['search']['label'] }}</button>
                <a href="{{ $publicEntries['events']['url'] }}" class="border border-white/12 px-4 py-2 text-sm font-medium text-white">{{ $publicEntries['events']['label'] }}</a>
                <a href="{{ $publicEntries['pricing']['url'] }}" class="border border-white/12 px-4 py-2 text-sm font-medium text-white">{{ $publicEntries['pricing']['label'] }}</a>
            </div>
            @guest
                <div class="mt-2 flex flex-wrap gap-2">
                    <a href="{{ $publicEntries['login']['url'] }}" class="border border-white/12 px-4 py-2 text-sm font-medium text-white">{{ $publicEntries['login']['label'] }}</a>
                    <a href="{{ $publicEntries['register']['url'] }}" class="px-4 py-2 text-sm font-medium text-white">{{ $publicEntries['register']['label'] }}</a>
                </div>
            @endguest
        </nav>
    </div>

    <div class="pointer-events-none fixed inset-0 z-40 bg-black/38 opacity-0 backdrop-blur-[3px] transition duration-200" data-xf-search-overlay></div>
    <div class="pointer-events-none fixed inset-0 z-50 flex items-start justify-center px-4 pt-24 opacity-0 transition duration-200 sm:px-6" data-xf-search-modal>
        <div class="w-full max-w-[660px] -translate-y-4 transition duration-200" data-xf-search-dialog>
            <form method="GET" action="{{ $publicEntries['search']['url'] }}" class="flex items-center gap-3 rounded-[14px] bg-white px-5 py-4 shadow-[0_18px_40px_rgba(0,0,0,0.18)]">
                <svg class="h-5 w-5 shrink-0 text-[#111111]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search posts, tags and authors"
                    class="min-w-0 flex-1 bg-transparent text-[15px] text-[#111111] outline-none placeholder:text-[#94a3b8]"
                    data-xf-search-input
                >
                <button type="submit" class="sr-only">搜索</button>
                <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center text-[#94a3b8] transition hover:text-[#111111]" aria-label="关闭搜索弹层" data-xf-search-close>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M6 18 18 6" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <div class="pointer-events-none fixed inset-0 z-40 bg-black/35 opacity-0 transition duration-200" data-xf-events-overlay></div>
    <aside class="pointer-events-none fixed inset-y-0 left-0 z-50 flex w-full max-w-[420px] -translate-x-full flex-col border-r border-[#d9d2ca] bg-[#f7f4ef] text-[#151515] opacity-0 transition duration-300 ease-out" data-xf-events-panel>
        <div class="flex items-center justify-between border-b border-[#e3ddd6] px-5 py-5">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#8b8175]">Latest Events</p>
                <h2 class="mt-2 font-serif text-2xl font-semibold text-[#151515]">最新活动</h2>
            </div>
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center border border-[#d8d1c8] bg-white text-[#111111] transition hover:bg-[#f5f3ef]" aria-label="关闭活动侧边层" data-xf-events-close>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M6 18 18 6" />
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto px-5 py-5">
            <div class="mb-6 border-b border-[#e3ddd6] pb-5 text-sm leading-7 text-[#5f574f]">
                左侧侧边层用于承接站点最新活动，保持与主页刊物流并行，不打断正文阅读。
            </div>

            <div class="space-y-4">
                @forelse ($xiaofangLatestEvents as $event)
                    <a href="{{ $event['url'] }}" class="block border border-[#ddd6cf] bg-white px-4 py-4 transition hover:-translate-y-0.5 hover:border-[#151515] hover:shadow-[0_12px_30px_rgba(17,17,17,0.08)]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">
                                {{ $event['status'] === 'registration-open' ? 'Open' : ($event['status'] === 'sold-out' ? 'Sold Out' : 'Archive') }}
                            </span>
                            <span class="text-xs text-[#8b8175]">
                                {{ $event['starts_at']?->format('m.d H:i') ?? '待更新' }}
                            </span>
                        </div>
                        <h3 class="mt-3 font-serif text-xl font-semibold leading-8 text-[#151515]">{{ $event['title'] }}</h3>
                        <p class="mt-2 text-sm leading-7 text-[#5f574f]">{{ $event['summary'] }}</p>
                        <div class="mt-4 flex items-center justify-between gap-3 text-xs text-[#8b8175]">
                            <span>{{ $event['location'] }}</span>
                            <span>{{ $event['is_paid'] ? '¥'.number_format((float) $event['price'], 0) : '免费' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="border border-dashed border-[#d6cfc7] bg-white px-4 py-6 text-sm text-[#6b6256]">
                        暂时还没有最新活动，后续这里会持续滚动更新。
                    </div>
                @endforelse
            </div>
        </div>
    </aside>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var shell = document.querySelector('[data-xf-shell]');

        if (! shell) {
            return;
        }

        var mobileTrigger = shell.querySelector('[data-xf-mobile-nav-trigger]');
        var mobileNav = shell.querySelector('[data-xf-mobile-nav]');
        var searchTriggers = document.querySelectorAll('[data-xf-search-trigger]');
        var searchModal = shell.querySelector('[data-xf-search-modal]');
        var searchOverlay = shell.querySelector('[data-xf-search-overlay]');
        var searchClose = shell.querySelector('[data-xf-search-close]');
        var searchInput = shell.querySelector('[data-xf-search-input]');
        var eventsTriggers = document.querySelectorAll('[data-xf-events-trigger]');
        var eventsPanel = shell.querySelector('[data-xf-events-panel]');
        var eventsOverlay = shell.querySelector('[data-xf-events-overlay]');
        var eventsClose = shell.querySelector('[data-xf-events-close]');

        mobileTrigger?.addEventListener('click', function () {
            mobileNav?.classList.toggle('hidden');
        });

        function openSearchModal() {
            if (! searchModal || ! searchOverlay) {
                return;
            }

            searchModal.classList.remove('opacity-0', 'pointer-events-none');
            searchOverlay.classList.remove('opacity-0', 'pointer-events-none');
            var dialog = searchModal.querySelector('[data-xf-search-dialog]');
            dialog?.classList.remove('-translate-y-4');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                searchInput?.focus();
            }, 60);
        }

        function closeSearchModal() {
            if (! searchModal || ! searchOverlay) {
                return;
            }

            searchModal.classList.add('opacity-0', 'pointer-events-none');
            searchOverlay.classList.add('opacity-0', 'pointer-events-none');
            var dialog = searchModal.querySelector('[data-xf-search-dialog]');
            dialog?.classList.add('-translate-y-4');
            document.body.classList.remove('overflow-hidden');
        }

        function openEventsPanel() {
            if (! eventsPanel || ! eventsOverlay) {
                return;
            }

            eventsPanel.classList.remove('-translate-x-full', 'opacity-0', 'pointer-events-none');
            eventsOverlay.classList.remove('opacity-0', 'pointer-events-none');
            document.body.classList.add('overflow-hidden');
        }

        function closeEventsPanel() {
            if (! eventsPanel || ! eventsOverlay) {
                return;
            }

            eventsPanel.classList.add('-translate-x-full', 'opacity-0', 'pointer-events-none');
            eventsOverlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
        }

        searchTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', openSearchModal);
        });
        searchClose?.addEventListener('click', closeSearchModal);
        searchOverlay?.addEventListener('click', closeSearchModal);
        eventsTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', openEventsPanel);
        });
        eventsClose?.addEventListener('click', closeEventsPanel);
        eventsOverlay?.addEventListener('click', closeEventsPanel);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSearchModal();
                closeEventsPanel();
            }
        });
    });
</script>
