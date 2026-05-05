@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '设置新密码 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    <section class="py-8 sm:py-10 lg:py-14">
        <div class="relative mx-auto max-w-4xl overflow-hidden border border-[#e7e5e4] bg-white">
            <div class="grid lg:grid-cols-[0.94fr_1.06fr]">
                <div class="bg-[#f3ede4] px-7 py-8 text-[#151515] sm:px-10 sm:py-10 lg:px-12 lg:py-14">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Reset Password</p>
                    <h1 class="mt-6 font-serif text-5xl leading-[0.95] tracking-[-0.05em]">设置新密码</h1>
                </div>

                <div class="px-7 py-8 sm:px-10 sm:py-10 lg:px-14 lg:py-14">
                    <div class="max-w-md">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">New Credentials</p>
                        <h2 class="mt-4 font-serif text-4xl tracking-[-0.04em] text-[#151515]">完成密码重置</h2>
                        <form method="POST" action="{{ route('password.update') }}" class="mt-12 space-y-6">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div>
                                <label for="email" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">邮箱</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="name@example.com">
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label for="password" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">新密码</label>
                                    <input id="password" name="password" type="password" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="至少 6 位">
                                </div>
                                <div>
                                    <label for="password_confirmation" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">确认密码</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="再次输入密码">
                                </div>
                            </div>

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-[1.15rem] bg-[#151515] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">
                                保存新密码
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
