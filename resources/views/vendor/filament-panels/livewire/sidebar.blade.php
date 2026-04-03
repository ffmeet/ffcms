<div
    x-data="ecmsAdminQuickSearch({
        endpoint: @js(route('admin.quick-search')),
    })"
    x-on:keydown.window.prevent.meta.k="open()"
    x-on:keydown.window.prevent.ctrl.k="open()"
    x-on:keydown.window.escape="close()"
>
    @php
        $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
        $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
        $homeUrl = filament()->getHomeUrl() ?? url('/admin');
        $currentUser = filament()->auth()->user();
        $postIndexUrl = \App\Filament\Resources\Posts\PostResource::getUrl();
        $articlePostUrl = $postIndexUrl . '?view=article';
        $flashPostUrl = $postIndexUrl . '?view=flash';
        $draftPostUrl = $postIndexUrl . '?view=draft';
        $newArticleUrl = \App\Filament\Resources\Posts\PostResource::getUrl('create', ['kind' => 'article']);
        $categoryUrl = \App\Filament\Resources\Categories\CategoryResource::getUrl();
        $tagUrl = \App\Filament\Resources\Tags\TagResource::getUrl();
        $commentUrl = \App\Filament\Resources\Comments\CommentResource::getUrl();
        $userUrl = \App\Filament\Resources\Users\UserResource::getUrl();
        $userCount = \App\Models\User::query()->count();
        $mediaUrl = url('/admin/media-manager');
        $settingsUrl = \App\Filament\Pages\SettingsCenter::getUrl();
        $helpUrl = \App\Filament\Pages\HelpCenter::getUrl();
        $profileUrl = $currentUser ? \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $currentUser]) : $userUrl;
        $currentView = request('view');
        $isArticleChildActive = request()->routeIs('filament.admin.resources.posts.index') && $currentView === 'article';
        $isFlashChildActive = request()->routeIs('filament.admin.resources.posts.index') && $currentView === 'flash';
        $isDraftChildActive = request()->routeIs('filament.admin.resources.posts.index') && $currentView === 'draft';
        $isContentChildActive = $isArticleChildActive || $isFlashChildActive || $isDraftChildActive;
        $isContentActive = request()->routeIs('filament.admin.resources.posts.*') && ! $isContentChildActive;
        $contentChildren = [
            [
                'label' => '文章',
                'url' => $articlePostUrl,
                'active' => $isArticleChildActive,
            ],
            [
                'label' => '快讯',
                'url' => $flashPostUrl,
                'active' => $isFlashChildActive,
            ],
            [
                'label' => '草稿',
                'url' => $draftPostUrl,
                'active' => $isDraftChildActive,
            ],
        ];
        $primaryItems = [
            [
                'label' => '内容',
                'icon' => 'heroicon-o-pencil-square',
                'url' => $postIndexUrl,
                'active' => $isContentActive,
                'children' => $contentChildren,
                'create_url' => $newArticleUrl,
            ],
            [
                'label' => '栏目',
                'icon' => 'heroicon-o-squares-2x2',
                'url' => $categoryUrl,
                'active' => request()->routeIs('filament.admin.resources.categories.*'),
            ],
            [
                'label' => '标签',
                'icon' => 'heroicon-o-tag',
                'url' => $tagUrl,
                'active' => request()->routeIs('filament.admin.resources.tags.*'),
            ],
            [
                'label' => '媒体',
                'icon' => 'heroicon-o-photo',
                'url' => $mediaUrl,
                'active' => request()->is('admin/media-manager'),
            ],
        ];
        $secondaryItems = [
            [
                'label' => '评论',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'url' => $commentUrl,
                'active' => request()->routeIs('filament.admin.resources.comments.*'),
            ],
            [
                'label' => '会员',
                'icon' => 'heroicon-o-user-group',
                'url' => $userUrl,
                'active' => request()->routeIs('filament.admin.resources.users.*'),
                'trailing' => $userCount,
            ],
        ];
    @endphp

    {{-- format-ignore-start --}}
    <aside
        x-data="{}"
        @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
            x-cloak
        @else
            x-cloak="-lg"
        @endif
        x-bind:class="{ 'fi-sidebar-open': $store.sidebar.isOpen }"
        class="fi-sidebar fi-main-sidebar"
    >
        <div class="fi-sidebar-header-ctn">
            <header class="fi-sidebar-header">
                <div class="fi-sidebar-header-logo-ctn">
                    <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                        <span x-show="$store.sidebar.isOpen" x-cloak>
                            <x-filament-panels::logo />
                        </span>

                        <span x-show="! $store.sidebar.isOpen" x-cloak class="ecms-brand-compact ecms-sidebar-floating-mark">
                            帝
                        </span>
                    </a>
                </div>
            </header>
        </div>

        <div
            class="ecms-sidebar-search-wrap"
            @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
                x-show="$store.sidebar.isOpen"
            @endif
        >
            <button type="button" class="ecms-sidebar-search-fallback" aria-label="搜索站点" @click="open()">
                <x-heroicon-o-magnifying-glass class="ecms-sidebar-search-fallback-icon" />
                <span class="ecms-sidebar-search-fallback-text">搜索内容</span>
                <span class="ecms-sidebar-search-shortcut">Ctrl+K</span>
            </button>
        </div>

        <nav class="fi-sidebar-nav ecms-sidebar-nav">
            <div class="ecms-sidebar-section">
                @foreach ($primaryItems as $item)
                    <div @class([
                        'ecms-sidebar-node',
                        'is-active' => $item['active'],
                        'has-children' => filled($item['children'] ?? []),
                    ])>
                        <div class="ecms-sidebar-node-head {{ $item['active'] ? 'is-active' : '' }}">
                            <a href="{{ $item['url'] }}" class="ecms-sidebar-link {{ $item['active'] ? 'is-active' : '' }}">
                                <x-dynamic-component :component="$item['icon']" class="ecms-sidebar-link-icon" />
                                <span x-show="$store.sidebar.isOpen" x-cloak class="ecms-sidebar-link-label">{{ $item['label'] }}</span>
                            </a>

                            @if (filled($item['create_url'] ?? null))
                                <a
                                    href="{{ $item['create_url'] }}"
                                    class="ecms-sidebar-add-btn"
                                    title="新建{{ $item['label'] }}"
                                    x-show="$store.sidebar.isOpen"
                                    x-cloak
                                >
                                    <x-heroicon-o-plus class="ecms-sidebar-add-icon" />
                                </a>
                            @endif
                        </div>

                        @if (filled($item['children'] ?? []))
                            <div class="ecms-sidebar-children" x-show="$store.sidebar.isOpen" x-cloak>
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="ecms-sidebar-child-link {{ $child['active'] ? 'is-active' : '' }}">
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="ecms-sidebar-section ecms-sidebar-section-lower">
                @foreach ($secondaryItems as $item)
                    <a href="{{ $item['url'] }}" class="ecms-sidebar-link {{ $item['active'] ? 'is-active' : '' }}">
                        <x-dynamic-component :component="$item['icon']" class="ecms-sidebar-link-icon" />
                        <span x-show="$store.sidebar.isOpen" x-cloak class="ecms-sidebar-link-label">{{ $item['label'] }}</span>
                        @if (filled($item['trailing'] ?? null))
                            <span x-show="$store.sidebar.isOpen" x-cloak class="ecms-sidebar-link-trailing">
                                {{ $item['trailing'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="fi-sidebar-footer ecms-sidebar-footer" x-show="$store.sidebar.isOpen" x-cloak>
            <div class="ecms-sidebar-footer-links">
                <a href="{{ $settingsUrl }}" class="ecms-sidebar-link {{ request()->routeIs('filament.admin.pages.settings-center') ? 'is-active' : '' }}">
                    <x-heroicon-o-cog-6-tooth class="ecms-sidebar-link-icon" />
                    <span class="ecms-sidebar-link-label">设置</span>
                </a>

                <a href="{{ $helpUrl }}" class="ecms-sidebar-link {{ request()->routeIs('filament.admin.pages.help-center') ? 'is-active' : '' }}">
                    <x-heroicon-o-question-mark-circle class="ecms-sidebar-link-icon" />
                    <span class="ecms-sidebar-link-label">帮助</span>
                </a>
            </div>

            @if ($currentUser)
                <div x-data="{ open: false }" class="ecms-sidebar-user">
                    <button type="button" class="ecms-sidebar-user-trigger" @click="open = ! open">
                        <span class="ecms-sidebar-user-avatar">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser->name ?: $currentUser->username ?: $currentUser->email, 0, 1)) }}
                        </span>
                        <span class="ecms-sidebar-user-copy">
                            <strong>{{ $currentUser->name ?: $currentUser->username }}</strong>
                            <small>{{ $currentUser->email }}</small>
                        </span>
                        <x-heroicon-o-chevron-up-down class="ecms-sidebar-user-trigger-icon" />
                    </button>

                    <div x-cloak x-show="open" x-transition.origin.bottom.left @click.outside="open = false" class="ecms-sidebar-user-menu">
                        <a href="{{ $profileUrl }}" class="ecms-sidebar-user-menu-link">个人资料</a>
                        <a href="{{ $homeUrl }}" class="ecms-sidebar-user-menu-link">返回后台首页</a>

                        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                            @csrf
                            <button type="submit" class="ecms-sidebar-user-menu-link is-danger">退出登录</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

    </aside>
    {{-- format-ignore-end --}}

    <div
        class="ecms-search-overlay"
        x-cloak
        x-show="isOpen"
        x-transition.opacity
        @click.self="close()"
    >
        <div class="ecms-search-panel" x-transition @click.stop>
            <div class="ecms-search-input-wrap">
                <x-heroicon-o-magnifying-glass class="ecms-search-panel-icon" />

                <input
                    x-ref="input"
                    x-model.debounce.250ms="query"
                    type="search"
                    class="ecms-search-panel-input"
                    placeholder="搜索文章、栏目、标签、会员、评论"
                >

                <button type="button" class="ecms-search-panel-close" @click="close()">Esc</button>
            </div>

            <div class="ecms-search-results">
                <template x-if="query.trim().length === 0">
                    <div class="ecms-search-empty">
                        输入关键词，搜索文章、栏目、标签、会员和评论
                    </div>
                </template>

                <template x-if="query.trim().length > 0 && isLoading">
                    <div class="ecms-search-empty">
                        正在搜索...
                    </div>
                </template>

                <template x-if="query.trim().length > 0 && !isLoading && results.length === 0">
                    <div class="ecms-search-empty">
                        没有找到相关结果
                    </div>
                </template>

                <template x-if="results.length > 0">
                    <div class="ecms-search-result-list">
                        <template x-for="(item, index) in results" :key="`${item.group}-${item.title}-${index}`">
                            <a :href="item.url" class="ecms-search-result-item">
                                <span class="ecms-search-result-group" x-text="item.group"></span>

                                <div class="ecms-search-result-copy">
                                    <strong x-text="item.title"></strong>
                                    <span x-show="item.meta" x-text="item.meta"></span>
                                </div>
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ecmsAdminQuickSearch', ({ endpoint }) => ({
                endpoint,
                isOpen: false,
                isLoading: false,
                query: '',
                results: [],
                abortController: null,
                open() {
                    this.isOpen = true
                    this.$nextTick(() => this.$refs.input?.focus())
                },
                close() {
                    this.isOpen = false
                    this.query = ''
                    this.results = []
                    this.isLoading = false
                    this.abortController?.abort()
                    this.abortController = null
                },
                async fetchResults() {
                    const keyword = this.query.trim()

                    if (keyword.length === 0) {
                        this.results = []
                        this.isLoading = false
                        this.abortController?.abort()
                        this.abortController = null
                        return
                    }

                    this.abortController?.abort()
                    this.abortController = new AbortController()
                    this.isLoading = true

                    try {
                        const response = await fetch(`${this.endpoint}?q=${encodeURIComponent(keyword)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            signal: this.abortController.signal,
                        })

                        if (! response.ok) {
                            throw new Error('Search request failed.')
                        }

                        const payload = await response.json()
                        this.results = Array.isArray(payload.results) ? payload.results : []
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            this.results = []
                        }
                    } finally {
                        this.isLoading = false
                    }
                },
                init() {
                    this.$watch('query', () => {
                        this.fetchResults()
                    })
                },
            }))
        })
    </script>
</div>
