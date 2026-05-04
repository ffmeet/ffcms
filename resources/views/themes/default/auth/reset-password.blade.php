@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '设置新密码 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    <section class="mx-auto min-h-[calc(100vh-16rem)] max-w-xl">
        <div class="rounded-[36px] border border-white/70 bg-white/88 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950">设置新密码</h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">输入邮箱并设置新密码，保存后即可重新登录。</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="mt-8 grid gap-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="text-sm font-medium text-slate-800">邮箱</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="name@example.com">
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="password" class="text-sm font-medium text-slate-800">新密码</label>
                        <input id="password" name="password" type="password" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="至少 6 位">
                    </div>
                    <div>
                        <label for="password_confirmation" class="text-sm font-medium text-slate-800">确认密码</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="再次输入密码">
                    </div>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-950 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-600">
                    保存新密码
                </button>
            </form>
        </div>
    </section>
@endsection
