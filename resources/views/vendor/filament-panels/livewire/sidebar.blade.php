<div
    x-data="ecmsAdminQuickSearch({
        endpoint: @js(route('admin.quick-search')),
        staffProfileOpen: @js(session('open_staff_profile_modal', false) || $errors->getBag('staffProfile')->any()),
        settingsOpen: @js(request()->routeIs('filament.admin.pages.settings-center')),
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
        $productUrl = \App\Filament\Resources\Products\ProductResource::getUrl();
        $orderUrl = \App\Filament\Resources\Orders\OrderResource::getUrl();
        $paymentUrl = \App\Filament\Resources\Payments\PaymentResource::getUrl();
        $subscriptionUrl = \App\Filament\Resources\UserSubscriptions\UserSubscriptionResource::getUrl();
        $eventUrl = \App\Filament\Resources\Events\EventResource::getUrl();
        $eventRegistrationUrl = \App\Filament\Resources\EventRegistrations\EventRegistrationResource::getUrl();
        $userCount = \App\Models\User::query()->count();
        $mediaUrl = url('/admin/media-manager');
        $settingsUrl = \App\Filament\Pages\SettingsCenter::getUrl();
        $helpUrl = \App\Filament\Pages\HelpCenter::getUrl();
        $settingsPage = app(\App\Filament\Pages\SettingsCenter::class);
        $settingsSections = $settingsPage->getSettingSections();
        $settingsOverviewCards = $settingsPage->getOverviewCards();
        $profileUrl = $currentUser ? \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $currentUser]) : $userUrl;
        $staffProfileUrl = route('admin.staff-profile.update');
        $staffProfileAvatar = $currentUser?->avatarUrl('medium') ?? $currentUser?->avatarUrl('small');
        $staffProfileName = old('display_name', $currentUser?->display_name ?: $currentUser?->public_display_name);
        $staffProfileEmail = old('email', $currentUser?->email);
        $staffProfileNickname = old('nickname', $currentUser?->nickname);
        $staffProfileBio = old('bio', $currentUser?->bio);
        $staffRoleLabel = $currentUser?->backend_role_label ?? '普通会员';
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
        $isProductChildActive = request()->routeIs('filament.admin.resources.products.*');
        $isOrderChildActive = request()->routeIs('filament.admin.resources.orders.*');
        $isPaymentChildActive = request()->routeIs('filament.admin.resources.payments.*');
        $isSubscriptionChildActive = request()->routeIs('filament.admin.resources.user-subscriptions.*');
        $isCommerceChildActive = $isProductChildActive || $isOrderChildActive || $isPaymentChildActive || $isSubscriptionChildActive;
        $isEventChildActive = request()->routeIs('filament.admin.resources.events.*');
        $isEventRegistrationChildActive = request()->routeIs('filament.admin.resources.event-registrations.*');
        $commerceChildren = [
            [
                'label' => '商品',
                'url' => $productUrl,
                'active' => $isProductChildActive,
            ],
            [
                'label' => '订单',
                'url' => $orderUrl,
                'active' => $isOrderChildActive,
            ],
            [
                'label' => '支付',
                'url' => $paymentUrl,
                'active' => $isPaymentChildActive,
            ],
            [
                'label' => '订阅',
                'url' => $subscriptionUrl,
                'active' => $isSubscriptionChildActive,
            ],
        ];
        $eventChildren = [
            [
                'label' => '活动报名',
                'url' => $eventRegistrationUrl,
                'active' => $isEventRegistrationChildActive,
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
        $commerceItems = [
            [
                'label' => '商店',
                'icon' => 'heroicon-o-shopping-bag',
                'url' => $productUrl,
                'active' => false,
                'expanded' => $isCommerceChildActive,
                'children' => $commerceChildren,
            ],
            [
                'label' => '活动',
                'icon' => 'heroicon-o-calendar-days',
                'url' => $eventUrl,
                'active' => $isEventChildActive,
                'expanded' => $isEventChildActive || $isEventRegistrationChildActive,
                'children' => $eventChildren,
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
                        <x-filament-panels::logo />
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

            <div class="ecms-sidebar-section ecms-sidebar-section-commerce">
                @foreach ($commerceItems as $item)
                    @if (filled($item['children'] ?? []))
                        <div
                            x-data="{ open: @js($item['expanded'] ?? $item['active']), hover: false }"
                            @mouseenter="hover = true"
                            @mouseleave="hover = false"
                            @class([
                                'ecms-sidebar-node ecms-sidebar-node-commerce',
                                'is-active' => $item['expanded'] ?? $item['active'],
                                'has-flyout' => filled($item['children'] ?? []),
                            ])
                        >
                            <div class="ecms-sidebar-node-head {{ ($item['active'] ?? false) ? 'is-active' : '' }}">
                                <a href="{{ $item['url'] }}" class="ecms-sidebar-link {{ ($item['active'] ?? false) ? 'is-active' : '' }}">
                                    <x-dynamic-component :component="$item['icon']" class="ecms-sidebar-link-icon" />
                                    <span x-show="$store.sidebar.isOpen" x-cloak class="ecms-sidebar-link-label">{{ $item['label'] }}</span>
                                </a>

                                <button
                                    type="button"
                                    class="ecms-sidebar-toggle-btn"
                                    title="展开{{ $item['label'] }}"
                                    x-show="$store.sidebar.isOpen"
                                    x-cloak
                                    @click="open = ! open"
                                >
                                    <x-heroicon-o-chevron-down class="ecms-sidebar-toggle-icon" x-bind:class="{ 'is-open': open }" />
                                </button>
                            </div>

                            <div
                                class="ecms-sidebar-flyout"
                                x-cloak
                                x-show="$store.sidebar.isOpen && (open || hover)"
                                x-transition.opacity.duration.150ms
                            >
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="ecms-sidebar-child-link {{ $child['active'] ? 'is-active' : '' }}">
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" class="ecms-sidebar-link {{ $item['active'] ? 'is-active' : '' }}">
                            <x-dynamic-component :component="$item['icon']" class="ecms-sidebar-link-icon" />
                            <span x-show="$store.sidebar.isOpen" x-cloak class="ecms-sidebar-link-label">{{ $item['label'] }}</span>
                        </a>
                    @endif
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
                <button type="button" class="ecms-sidebar-link w-full {{ request()->routeIs('filament.admin.pages.settings-center') ? 'is-active' : '' }}" @click="settingsOpen = true">
                    <x-heroicon-o-cog-6-tooth class="ecms-sidebar-link-icon" />
                    <span class="ecms-sidebar-link-label">设置</span>
                </button>

                <a href="{{ $helpUrl }}" class="ecms-sidebar-link {{ request()->routeIs('filament.admin.pages.help-center') ? 'is-active' : '' }}">
                    <x-heroicon-o-question-mark-circle class="ecms-sidebar-link-icon" />
                    <span class="ecms-sidebar-link-label">帮助</span>
                </a>
            </div>

            @if ($currentUser)
                <div x-data="{ open: false }" class="ecms-sidebar-user">
                    <button type="button" class="ecms-sidebar-user-trigger" @click="open = ! open">
                        <span class="ecms-sidebar-user-avatar">
                            @if ($currentUser->avatarUrl('small'))
                                <img src="{{ $currentUser->avatarUrl('small') }}" alt="{{ $currentUser->public_display_name }}" style="width:100%;height:100%;border-radius:9999px;object-fit:cover;" />
                            @else
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser->public_display_name ?: $currentUser->username ?: $currentUser->email, 0, 1)) }}
                            @endif
                        </span>
                        <span class="ecms-sidebar-user-copy">
                            <strong>{{ $currentUser->public_display_name ?: $currentUser->username }}</strong>
                            <small>{{ $currentUser->email }}</small>
                        </span>
                        <x-heroicon-o-chevron-up-down class="ecms-sidebar-user-trigger-icon" />
                    </button>

                    <div x-cloak x-show="open" x-transition.origin.bottom.left @click.outside="open = false" class="ecms-sidebar-user-menu">
                        @if ($currentUser?->is_staff_account)
                            <button type="button" class="ecms-sidebar-user-menu-link" @click="open = false; staffProfileOpen = true">
                                个人资料
                            </button>
                        @endif

                        <a href="{{ $profileUrl }}" class="ecms-sidebar-user-menu-link">资料设置</a>
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

    @if ($currentUser?->is_staff_account)
        <div
            class="ecms-staff-profile-overlay"
            x-cloak
            x-show="staffProfileOpen"
            x-transition.opacity
            @click.self="staffProfileOpen = false"
        >
            <div class="ecms-staff-profile-panel" x-transition @click.stop>
                <div class="ecms-staff-profile-cover"></div>

                <div class="ecms-staff-profile-head">
                    <div class="ecms-staff-profile-avatar">
                        @if (filled($staffProfileAvatar))
                            <img src="{{ $staffProfileAvatar }}" alt="{{ $staffProfileName }}">
                        @else
                            <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser->public_display_name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="ecms-staff-profile-copy">
                        <h3>{{ $staffProfileName ?: $currentUser->public_display_name }}</h3>
                        <p>{{ $currentUser->memberGroup?->name ?? '未分组' }} · {{ $staffRoleLabel }}</p>
                    </div>
                    <button type="button" class="ecms-staff-profile-close" @click="staffProfileOpen = false">×</button>
                </div>

                <form method="POST" action="{{ $staffProfileUrl }}" class="ecms-staff-profile-form">
                    @csrf
                    @method('PUT')

                    <div class="ecms-staff-profile-grid">
                        <div class="ecms-staff-profile-field">
                            <label for="staff-profile-email">邮箱</label>
                            <input id="staff-profile-email" name="email" type="email" value="{{ $staffProfileEmail }}" class="@if($errors->getBag('staffProfile')->has('email')) is-error @endif">
                            @error('email', 'staffProfile')
                                <div class="ecms-staff-profile-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ecms-staff-profile-field">
                            <label for="staff-profile-role">角色</label>
                            <input id="staff-profile-role" type="text" value="{{ $staffRoleLabel }}" readonly>
                        </div>
                    </div>

                    <div class="ecms-staff-profile-field">
                        <label for="staff-profile-display-name">全名</label>
                        <input id="staff-profile-display-name" name="display_name" type="text" value="{{ $staffProfileName }}" class="@if($errors->getBag('staffProfile')->has('display_name')) is-error @endif">
                        @error('display_name', 'staffProfile')
                            <div class="ecms-staff-profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="ecms-staff-profile-field">
                        <label for="staff-profile-username">用户名</label>
                        <input id="staff-profile-username" type="text" value="{{ $currentUser->username }}" readonly>
                    </div>

                    <div class="ecms-staff-profile-field">
                        <label for="staff-profile-nickname">公开昵称</label>
                        <input id="staff-profile-nickname" name="nickname" type="text" value="{{ $staffProfileNickname }}" class="@if($errors->getBag('staffProfile')->has('nickname')) is-error @endif">
                        @error('nickname', 'staffProfile')
                            <div class="ecms-staff-profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="ecms-staff-profile-field">
                        <label for="staff-profile-bio">自我简介</label>
                        <textarea id="staff-profile-bio" name="bio" rows="4" class="@if($errors->getBag('staffProfile')->has('bio')) is-error @endif">{{ $staffProfileBio }}</textarea>
                        @error('bio', 'staffProfile')
                            <div class="ecms-staff-profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="ecms-staff-profile-grid">
                        <div class="ecms-staff-profile-field">
                            <label for="staff-profile-password">新密码</label>
                            <input id="staff-profile-password" name="password" type="password" class="@if($errors->getBag('staffProfile')->has('password')) is-error @endif">
                            @error('password', 'staffProfile')
                                <div class="ecms-staff-profile-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ecms-staff-profile-field">
                            <label for="staff-profile-password-confirmation">确认密码</label>
                            <input id="staff-profile-password-confirmation" name="password_confirmation" type="password">
                        </div>
                    </div>

                    <div class="ecms-staff-profile-actions">
                        <button type="button" class="ecms-staff-profile-secondary" @click="staffProfileOpen = false">关闭</button>
                        <button type="submit" class="ecms-staff-profile-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div
        class="ecms-settings-modal-overlay"
        x-cloak
        x-show="settingsOpen"
        x-transition.opacity
        @click.self="settingsOpen = false"
    >
        <div class="ecms-settings-modal-panel" x-transition @click.stop>
            <div class="ecms-settings-modal-head">
                <div>
                    <h2>设置中心</h2>
                </div>
                <button type="button" class="ecms-settings-modal-close" @click="settingsOpen = false">×</button>
            </div>

            <div class="ecms-settings-modal-body">
                @livewire('admin.settings-workbench')
            </div>
        </div>
    </div>

    <x-filament-actions::modals />

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ecmsAdminQuickSearch', ({ endpoint, staffProfileOpen = false, settingsOpen = false }) => ({
                endpoint,
                isOpen: false,
                staffProfileOpen,
                settingsOpen,
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
