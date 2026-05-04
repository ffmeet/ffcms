@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '登录 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    <section class="py-8 sm:py-10 lg:py-14">
        <div class="relative overflow-hidden border border-[#e7e5e4] bg-white">
            <div class="grid lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]">
                <div class="relative isolate overflow-hidden bg-[#111111] px-7 py-8 text-white sm:px-10 sm:py-10 lg:min-h-[760px] lg:px-12 lg:py-12">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.2),transparent_24%),radial-gradient(circle_at_70%_10%,rgba(220,38,38,0.36),transparent_28%),radial-gradient(circle_at_88%_78%,rgba(217,119,6,0.28),transparent_25%),linear-gradient(145deg,#080808_14%,#181512_52%,#2b211d_100%)]"></div>
                    <div class="absolute -left-12 top-12 h-52 w-[120%] rotate-[-14deg] rounded-full border border-white/10 bg-[linear-gradient(90deg,rgba(255,255,255,0.02),rgba(255,255,255,0.16),rgba(255,255,255,0.02))] blur-[1px]"></div>
                    <div class="absolute -right-16 bottom-20 h-56 w-[120%] rotate-[12deg] rounded-full border border-white/10 bg-[linear-gradient(90deg,rgba(255,255,255,0.02),rgba(251,191,36,0.22),rgba(255,255,255,0.02))] blur-[1px]"></div>
                    <div class="absolute inset-x-8 bottom-8 top-8 border border-white/15"></div>

                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.34em] text-white/70">Member Access</div>
                            <a href="{{ url('/') }}" class="text-sm text-white/72 transition hover:text-white">返回首页</a>
                        </div>

                        <div class="mt-16 max-w-md lg:mt-auto">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-white/68">Editorial Journal</p>
                            <h1 class="mt-6 font-serif text-5xl leading-[0.95] tracking-[-0.05em] text-[#fff8f0] sm:text-6xl lg:text-[5.3rem]">欢迎回来</h1>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white px-7 py-8 sm:px-10 sm:py-10 lg:px-16 lg:py-14">
                    <div class="mx-auto flex min-h-full max-w-[34rem] flex-col">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Sign In</p>
                                <h2 class="mt-4 font-serif text-4xl leading-none tracking-[-0.04em] text-[#151515] sm:text-5xl">回到你的席位</h2>
                            </div>
                            <a href="{{ route('register') }}" class="hidden border border-[#d8d1c8] px-4 py-2 text-sm font-medium text-[#151515] transition hover:border-[#151515] sm:inline-flex">创建账号</a>
                        </div>

                        <form method="POST" action="{{ route('auth.login') }}" class="mt-12 space-y-6">
                            @csrf

                            <div>
                                <label for="login" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">用户名或邮箱</label>
                                <input
                                    id="login"
                                    name="login"
                                    type="text"
                                    value="{{ old('login') }}"
                                    required
                                    autofocus
                                    class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white"
                                    placeholder="member01 或 name@example.com"
                                >
                            </div>

                            <div>
                                <label for="password" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">密码</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white"
                                    placeholder="输入你的登录密码"
                                >
                            </div>

                            <div class="flex flex-col gap-3 text-sm text-[#6b6256] sm:flex-row sm:items-center sm:justify-between">
                                <label class="inline-flex items-center gap-3">
                                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-[#d6d3d1] text-[#151515] focus:ring-[#d6d3d1]">
                                    <span>记住登录状态</span>
                                </label>
                                <a href="{{ route('password.request') }}" class="text-[#8b8175] transition hover:text-[#151515]">忘记密码？</a>
                            </div>

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-[1.15rem] bg-[#151515] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">
                                登录并进入会员中心
                            </button>
                        </form>

                        <div class="mt-8 border-t border-[#ece7e0] pt-6 text-sm text-[#6b6256]">
                            还没有账号？
                            <a href="{{ route('register') }}" class="font-semibold text-[#151515] transition hover:text-[#8b8175]">创建会员账号</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
