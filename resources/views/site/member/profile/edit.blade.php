@extends('site.layout', ['title' => '修改资料'])

@section('content')
    @include('site.member.partials.topbar')

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include('site.member.partials.nav')

        <div class="space-y-6">
            @include('site.member.partials.page-header', [
                'eyebrow' => 'Profile',
                'title' => '修改资料',
                'description' => '这里集中管理用户名、邮箱等基础资料，后面可以继续扩展头像、偏好设置和安全信息。',
            ])

            <form method="POST" action="{{ route('member.profile.update') }}" class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="username" class="text-sm font-medium text-slate-700">用户名</label>
                        <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" class="mt-3 w-full rounded-[20px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white">
                    </div>
                    <div>
                        <label for="email" class="text-sm font-medium text-slate-700">邮箱</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-3 w-full rounded-[20px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white">
                    </div>
                </div>

                <div class="mt-5 rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                    <p class="text-sm font-semibold text-slate-900">账户摘要</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-400">账户 ID</div>
                            <div class="mt-2 font-semibold text-slate-900">ID-{{ str_pad((string) $user->id, 8, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-400">会员组</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $user->memberGroup?->name ?? '未分组' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-400">状态</div>
                            <div class="mt-2 font-semibold text-emerald-700">{{ $user->status === 'active' ? '在线' : '非活跃' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-[18px] bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-blue-600 hover:to-slate-900">保存资料</button>
                </div>
            </form>
        </div>
    </div>
@endsection
