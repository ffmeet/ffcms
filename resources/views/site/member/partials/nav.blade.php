@php
    $memberUser = auth()->user();
    $menuItems = [
        ['label' => '我的稿件', 'route' => 'member.posts.index', 'count' => $memberUser?->posts()->count() ?? 0],
        ['label' => '我的评论', 'route' => 'member.comments.index', 'count' => $memberUser?->comments()->count() ?? 0],
        ['label' => '发布新稿件', 'route' => 'member.posts.create', 'count' => null],
        ['label' => '修改资料', 'route' => 'member.profile.edit', 'count' => null],
        ['label' => '活动中心', 'route' => 'member.activity.center', 'count' => null],
        ['label' => '我的活动', 'route' => 'member.activities.index', 'count' => null],
    ];
@endphp

<aside class="space-y-4">
    <section class="rounded-[18px] border border-slate-200/80 bg-white/96 p-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)]">
        <div class="flex flex-col items-center text-center">
            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[radial-gradient(circle_at_30%_30%,#60a5fa,#1d4ed8_65%,#0f172a)] text-3xl font-semibold text-white shadow-[0_18px_44px_rgba(37,99,235,0.24)]">
                {{ str($memberUser?->username ?? 'M')->substr(0, 1)->upper() }}
            </div>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">{{ $memberUser?->username }}</h2>
            <span class="mt-2 inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                {{ $memberUser?->memberGroup?->name ?? '会员用户' }}
            </span>
        </div>

        <div class="mt-6 grid grid-cols-3 gap-3 text-center">
            <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3">
                <div class="text-2xl font-semibold text-slate-900">{{ $memberUser?->posts()->count() ?? 0 }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">稿件</div>
            </div>
            <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3">
                <div class="text-2xl font-semibold text-emerald-600">{{ $memberUser?->posts()->where('status', 'published')->count() ?? 0 }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">发布</div>
            </div>
            <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3">
                <div class="text-2xl font-semibold text-sky-600">{{ $memberUser?->comments()->count() ?? 0 }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">评论</div>
            </div>
        </div>
    </section>

    <section class="rounded-[18px] border border-slate-200/80 bg-white/96 p-4 shadow-[0_20px_50px_rgba(15,23,42,0.08)]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-600">Navigation</p>
                <h3 class="mt-1.5 text-lg font-semibold text-slate-900">基本信息导航</h3>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                你好，{{ $memberUser?->username }}
            </span>
        </div>

        <nav class="mt-4 space-y-2">
            @foreach ($menuItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center justify-between rounded-[12px] px-4 py-3 text-sm font-medium transition {{ request()->routeIs($item['route']) || (str_ends_with($item['route'], 'index') && request()->routeIs(str_replace('.index', '.*', $item['route']))) ? 'bg-sky-50 text-sky-700 ring-1 ring-sky-100 shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}"
                >
                    <span>{{ $item['label'] }}</span>
                    @if (! is_null($item['count']))
                        <span class="rounded-full px-2.5 py-1 text-xs {{ request()->routeIs($item['route']) || (str_ends_with($item['route'], 'index') && request()->routeIs(str_replace('.index', '.*', $item['route']))) ? 'bg-white text-sky-700 ring-1 ring-sky-100' : 'bg-slate-100 text-slate-500' }}">
                            {{ $item['count'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </section>

    <section class="rounded-[18px] border border-slate-200/80 bg-[linear-gradient(180deg,rgba(255,255,255,.98),rgba(240,249,255,.96))] p-4 shadow-[0_20px_50px_rgba(15,23,42,0.08)]">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-600">Details</p>
        <dl class="mt-4 space-y-4 text-sm">
            <div>
                <dt class="text-slate-400">账户 ID</dt>
                <dd class="mt-1 font-semibold text-slate-900">ID-{{ str_pad((string) ($memberUser?->id ?? 0), 8, '0', STR_PAD_LEFT) }}</dd>
            </div>
            <div>
                <dt class="text-slate-400">邮箱</dt>
                <dd class="mt-1 font-medium text-slate-700">{{ $memberUser?->email }}</dd>
            </div>
            <div>
                <dt class="text-slate-400">语言</dt>
                <dd class="mt-1 font-medium text-slate-700">简体中文</dd>
            </div>
            <div>
                <dt class="text-slate-400">登录状态</dt>
                <dd class="mt-1 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $memberUser?->status === 'active' ? '在线' : '非活跃' }}
                </dd>
            </div>
        </dl>
    </section>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex w-full items-center justify-center rounded-[12px] border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 shadow-sm transition hover:bg-rose-50">
            退出登录
        </button>
    </form>
</aside>
