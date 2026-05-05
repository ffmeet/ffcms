<div
    x-data="{ open: false }"
    class="ecms-topbar-notifications hidden lg:block"
>
    <button
        type="button"
        class="ecms-topbar-bell"
        x-on:click="open = true"
        aria-label="打开消息通知"
    >
        <span class="ecms-topbar-bell-dot"></span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0M18 8.75a6 6 0 1 0-12 0c0 6.706-2.684 7.291-2.684 7.291A1 1 0 0 0 4.28 17.5h15.44a1 1 0 0 0 .964-1.459S18 15.456 18 8.75ZM13.73 20a2 2 0 0 1-3.46 0" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="ecms-topbar-notification-overlay"
        x-on:click="open = false"
    ></div>

    <aside
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
        class="ecms-topbar-notification-drawer"
    >
        <div class="ecms-topbar-notification-head">
            <div>
                <p class="ecms-topbar-notification-eyebrow">Notifications</p>
                <h3>个人通知</h3>
            </div>
            <button type="button" x-on:click="open = false" class="ecms-topbar-notification-close" aria-label="关闭通知">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <div class="ecms-topbar-notification-list">
            <article class="ecms-topbar-notification-item is-new">
                <span class="ecms-topbar-notification-item-dot"></span>
                <div>
                    <h4>稿件状态更新</h4>
                    <p>《FFMeet 1.0 发布准备》已有新的审核进展，可以进入稿件中心继续处理。</p>
                    <time>刚刚</time>
                </div>
            </article>

            <article class="ecms-topbar-notification-item">
                <span class="ecms-topbar-notification-item-dot"></span>
                <div>
                    <h4>评论提醒</h4>
                    <p>你的文章收到一条新回复，前台互动区已经可以查看讨论线程。</p>
                    <time>12 分钟前</time>
                </div>
            </article>

            <article class="ecms-topbar-notification-item">
                <span class="ecms-topbar-notification-item-dot"></span>
                <div>
                    <h4>媒体处理完成</h4>
                    <p>最近上传的图片已经生成缩略图，媒体库里可以继续转 WebP。</p>
                    <time>今天 09:25</time>
                </div>
            </article>
        </div>
    </aside>
</div>
