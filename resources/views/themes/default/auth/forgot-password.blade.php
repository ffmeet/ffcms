@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '重置密码 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    <section class="mx-auto min-h-[calc(100vh-16rem)] max-w-xl">
        <div class="rounded-[36px] border border-white/70 bg-white/88 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950">重置密码</h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">输入你的注册邮箱，我们会把重置密码链接发送到邮箱。</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="text-sm font-medium text-slate-800">邮箱</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-3 w-full rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-3.5 text-base text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="name@example.com">
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-950 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-600">
                    发送重置邮件
                </button>
            </form>
        </div>
    </section>
@endsection
