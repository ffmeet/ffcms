@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '注册 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    <section class="mx-auto min-h-[calc(100vh-16rem)] max-w-xl">
        <div class="rounded-[36px] border border-white/70 bg-white/88 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950">创建会员账号</h1>
                </div>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-900 hover:bg-white hover:text-slate-950">
                    返回登录
                </a>
            </div>

            <form method="POST" action="{{ route('auth.register') }}" class="mt-8 grid gap-5">
                @csrf

                <div>
                    <label for="username" class="text-sm font-medium text-slate-800">用户名</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="例如：ffmeet-user">
                </div>

                <div>
                    <label for="email" class="text-sm font-medium text-slate-800">邮箱</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="name@example.com">
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="password" class="text-sm font-medium text-slate-800">密码</label>
                        <input id="password" name="password" type="password" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="至少 6 位">
                    </div>
                    <div>
                        <label for="password_confirmation" class="text-sm font-medium text-slate-800">确认密码</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="再次输入密码">
                    </div>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-950 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-600">
                    注册并进入会员中心
                </button>
            </form>
        </div>
    </section>
@endsection
