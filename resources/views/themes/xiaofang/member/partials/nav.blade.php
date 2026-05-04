@php
    $memberUser = auth()->user();
    $memberAvatarMedium = $memberUser?->avatarUrl('medium');
    $memberEntries = $memberRouteEntries ?? \App\Support\RouteRuleManager::memberEntries(($siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults()))->toArray());
    $menuItems = [
        ['label' => $memberEntries['posts']['label'], 'url' => $memberEntries['posts']['url'], 'route' => 'member.posts.index', 'count' => $memberUser?->posts()->count() ?? 0],
        ['label' => $memberEntries['comments']['label'], 'url' => $memberEntries['comments']['url'], 'route' => 'member.comments.index', 'count' => $memberUser?->comments()->count() ?? 0],
        ['label' => $memberEntries['orders']['label'], 'url' => $memberEntries['orders']['url'], 'route' => 'member.orders.index', 'count' => $memberUser?->orders()->count() ?? 0],
        ['label' => $memberEntries['subscriptions']['label'], 'url' => $memberEntries['subscriptions']['url'], 'route' => 'member.subscriptions.index', 'count' => $memberUser?->subscriptions()->count() ?? 0],
        ['label' => $memberEntries['create_post']['label'], 'url' => $memberEntries['create_post']['url'], 'route' => 'member.posts.create', 'count' => null],
        ['label' => $memberEntries['profile']['label'], 'url' => $memberEntries['profile']['url'], 'route' => 'member.profile.edit', 'count' => null],
        ['label' => $memberEntries['activity_center']['label'], 'url' => $memberEntries['activity_center']['url'], 'route' => 'member.activity.center', 'count' => null],
        ['label' => $memberEntries['activities']['label'], 'url' => $memberEntries['activities']['url'], 'route' => 'member.activities.index', 'count' => $memberUser?->eventRegistrations()->count() ?? 0],
    ];
@endphp

<aside class="space-y-4">
    <section class="bg-white p-2">
        <div class="flex flex-col items-center text-center">
            @if ($memberAvatarMedium)
                <img src="{{ $memberAvatarMedium }}" alt="{{ $memberUser?->public_display_name }}" class="h-24 w-24 rounded-full object-cover">
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[#111111] text-3xl font-semibold text-white">
                    {{ str($memberUser?->public_display_name ?? 'M')->substr(0, 1)->upper() }}
                </div>
            @endif
            <h2 class="mt-4 text-2xl font-black tracking-tight text-[#181512]">{{ $memberUser?->public_display_name }}</h2>
            <span class="mt-2 inline-flex px-1 py-0 text-xs font-semibold text-[#6b6256]">
                {{ $memberUser?->memberGroup?->name ?? '会员用户' }}
            </span>
        </div>

        <div class="mt-6 grid grid-cols-3 gap-3 text-center">
            <div class="border border-[#e5e7eb] bg-white px-3 py-3">
                <div class="text-2xl font-black text-[#181512]">{{ $memberUser?->posts()->count() ?? 0 }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-[#a8a29e]">稿件</div>
            </div>
            <div class="border border-[#e5e7eb] bg-white px-3 py-3">
                <div class="text-2xl font-black text-[#181512]">{{ $memberUser?->posts()->where('status', 'published')->count() ?? 0 }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-[#a8a29e]">发布</div>
            </div>
            <div class="border border-[#e5e7eb] bg-white px-3 py-3">
                <div class="text-2xl font-black text-[#181512]">{{ $memberUser?->comments()->count() ?? 0 }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-[#a8a29e]">评论</div>
            </div>
        </div>
    </section>

    <section class="rounded-[18px] border border-[#e5dfd7] bg-white p-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#8b8175]">Navigation</p>
            <h3 class="mt-1.5 text-lg font-black text-[#181512]">基本信息导航</h3>
        </div>

        <nav class="mt-4 space-y-2">
            @foreach ($menuItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex items-center justify-between rounded-[12px] border-b border-[#f1f5f9] px-4 py-3 text-sm font-medium transition {{ request()->routeIs($item['route']) || (str_ends_with($item['route'], 'index') && request()->routeIs(str_replace('.index', '.*', $item['route']))) ? 'bg-[#f5f5f4] text-[#151515]' : 'text-[#5f574f] hover:bg-[#fafaf9] hover:text-[#151515]' }}"
                >
                    <span>{{ $item['label'] }}</span>
                    @if (! is_null($item['count']))
                        <span class="inline-flex min-w-[2.25rem] items-center justify-center rounded-full border px-2.5 py-1 text-xs {{ request()->routeIs($item['route']) || (str_ends_with($item['route'], 'index') && request()->routeIs(str_replace('.index', '.*', $item['route']))) ? 'border-[#d6d3d1] bg-white text-[#151515]' : 'border-[#e7e5e4] bg-[#fafaf9] text-[#78716c]' }}">
                            {{ $item['count'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </section>

    <section class="rounded-[18px] border border-[#e5e7eb] bg-white p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#8b8175]">Details</p>
        <dl class="mt-4 space-y-4 text-sm">
            <div>
                <dt class="text-[#a8a29e]">账户 ID</dt>
                <dd class="mt-1 font-semibold text-[#181512]">ID-{{ str_pad((string) ($memberUser?->id ?? 0), 8, '0', STR_PAD_LEFT) }}</dd>
            </div>
            <div>
                <dt class="text-[#a8a29e]">邮箱</dt>
                <dd class="mt-1 font-medium text-[#5f574f]">{{ $memberUser?->email }}</dd>
            </div>
            <div>
                <dt class="text-[#a8a29e]">语言</dt>
                <dd class="mt-1 font-medium text-[#5f574f]">简体中文</dd>
            </div>
            <div>
                <dt class="text-[#a8a29e]">登录状态</dt>
                <dd class="mt-1 inline-flex border border-[#d8d1c8] bg-white px-3 py-1 text-xs font-semibold text-[#151515]">
                    {{ $memberUser?->status === 'active' ? '在线' : '非活跃' }}
                </dd>
            </div>
        </dl>
    </section>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex w-full items-center justify-center rounded-[18px] border border-[#e5e7eb] bg-white px-4 py-3 text-sm font-semibold text-[#151515] transition hover:bg-[#fafaf9] hover:text-[#000000]">
            退出登录
        </button>
    </form>
</aside>
