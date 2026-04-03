<div
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    class="ecms-topbar-publish hidden lg:block"
>
    <button
        type="button"
        class="ecms-topbar-publish-trigger"
        x-on:click="open = ! open"
        aria-label="打开发布菜单"
    >
        <span>发布</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="ecms-topbar-publish-overlay"
        x-on:click="open = false"
    ></div>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        class="ecms-topbar-publish-menu"
    >
        <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create', ['kind' => 'article']) }}" class="ecms-topbar-publish-item">
            <span class="ecms-topbar-publish-item-icon">文</span>
            <span>
                <strong>发布文章</strong>
                <small>进入标准文章编辑页</small>
            </span>
        </a>

        <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create', ['kind' => 'flash']) }}" class="ecms-topbar-publish-item">
            <span class="ecms-topbar-publish-item-icon">讯</span>
            <span>
                <strong>发布快讯</strong>
                <small>进入轻量快讯录入页</small>
            </span>
        </a>
    </div>
</div>
