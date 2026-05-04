@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '注册 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    <section class="py-8 sm:py-10 lg:py-14">
        <div class="relative overflow-hidden border border-[#e7e5e4] bg-white">
            <div class="grid lg:grid-cols-[minmax(0,0.98fr)_minmax(0,1.02fr)]">
                <div class="relative isolate overflow-hidden bg-[#f3ede4] px-7 py-8 text-[#151515] sm:px-10 sm:py-10 lg:min-h-[820px] lg:px-12 lg:py-12">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(251,191,36,0.16),transparent_24%),radial-gradient(circle_at_85%_15%,rgba(146,64,14,0.12),transparent_28%),linear-gradient(150deg,#f7f2ea_2%,#efe4d3_52%,#e6d8c4_100%)]"></div>
                    <div class="absolute -left-10 top-16 h-52 w-[118%] rotate-[-12deg] rounded-full border border-[#d8c9b6] bg-[linear-gradient(90deg,rgba(255,255,255,0.18),rgba(255,255,255,0.62),rgba(255,255,255,0.18))]"></div>
                    <div class="absolute -right-16 bottom-24 h-64 w-[120%] rotate-[10deg] rounded-full border border-[#d9c6af] bg-[linear-gradient(90deg,rgba(239,228,211,0.15),rgba(174,116,47,0.18),rgba(239,228,211,0.15))]"></div>
                    <div class="absolute inset-x-8 bottom-8 top-8 border border-[#d9cfbf]"></div>

                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Join</div>
                            <a href="{{ url('/') }}" class="text-sm text-[#6b6256] transition hover:text-[#151515]">返回首页</a>
                        </div>

                        <div class="mt-16 max-w-md lg:mt-auto">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Membership</p>
                            <h1 class="mt-6 font-serif text-5xl leading-[0.95] tracking-[-0.05em] text-[#181512] sm:text-6xl lg:text-[5.1rem]">创建账号</h1>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white px-7 py-8 sm:px-10 sm:py-10 lg:px-16 lg:py-14">
                    <div class="mx-auto flex min-h-full max-w-[34rem] flex-col">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Create Account</p>
                                <h2 class="mt-4 font-serif text-4xl leading-none tracking-[-0.04em] text-[#151515] sm:text-5xl">创建会员账号</h2>
                            </div>
                            <a href="{{ route('login') }}" class="hidden border border-[#d8d1c8] px-4 py-2 text-sm font-medium text-[#151515] transition hover:border-[#151515] sm:inline-flex">返回登录</a>
                        </div>

                        <form method="POST" action="{{ route('auth.register') }}" class="mt-12 grid gap-6">
                            @csrf

                            <div>
                                <label for="username" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">用户名</label>
                                <input id="username" name="username" type="text" value="{{ old('username') }}" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="例如：ffmeet-user">
                            </div>

                            <div>
                                <label for="email" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">邮箱</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="name@example.com">
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label for="password" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">密码</label>
                                    <input id="password" name="password" type="password" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="至少 6 位">
                                </div>
                                <div>
                                    <label for="password_confirmation" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">确认密码</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="再次输入密码">
                                </div>
                            </div>

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-[1.15rem] bg-[#151515] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">
                                注册并进入会员中心
                            </button>
                        </form>

                        <div class="mt-8 border-t border-[#ece7e0] pt-6 text-sm text-[#6b6256]">
                            已经有账号？
                            <a href="{{ route('login') }}" class="font-semibold text-[#151515] transition hover:text-[#8b8175]">直接登录</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
