@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '登录 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    <section class="mx-auto min-h-[calc(100vh-16rem)] max-w-xl">
        <div class="rounded-[36px] border border-white/70 bg-white/88 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950">登录站点</h1>
                </div>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-900 hover:bg-white hover:text-slate-950">
                    创建账号
                </a>
            </div>

            <form method="POST" action="{{ route('auth.login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="login" class="text-sm font-medium text-slate-800">用户名或邮箱</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        value="{{ old('login') }}"
                        required
                        autofocus
                        class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        placeholder="member01 或 name@example.com"
                    >
                </div>

                <div>
                    <label for="password" class="text-sm font-medium text-slate-800">密码</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        placeholder="输入你的登录密码"
                    >
                </div>

                <div class="flex flex-col gap-3 text-sm text-slate-700 sm:flex-row sm:items-center sm:justify-between">
                    <label class="flex items-center gap-3 rounded-[20px] border border-slate-200 bg-slate-50 px-4 py-3">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-blue-500">
                        记住登录状态
                    </label>
                    <a href="{{ route('password.request') }}" class="font-medium text-slate-600 transition hover:text-slate-950">忘记密码？</a>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-950 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-600">
                    登录并进入会员中心
                </button>
            </form>
        </div>
    </section>
@endsection
