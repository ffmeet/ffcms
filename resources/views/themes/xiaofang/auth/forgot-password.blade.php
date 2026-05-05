@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '重置密码 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    <section class="py-8 sm:py-10 lg:py-14">
        <div class="relative mx-auto max-w-3xl overflow-hidden border border-[#e7e5e4] bg-white">
            <div class="grid lg:grid-cols-[0.9fr_1.1fr]">
                <div class="bg-[#151515] px-7 py-8 text-white sm:px-10 sm:py-10 lg:px-12 lg:py-14">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-white/68">Password Reset</p>
                    <h1 class="mt-6 font-serif text-5xl leading-[0.95] tracking-[-0.05em] text-[#fff8f0]">找回密码</h1>
                </div>

                <div class="px-7 py-8 sm:px-10 sm:py-10 lg:px-14 lg:py-14">
                    <div class="max-w-md">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">Reset Access</p>
                        <h2 class="mt-4 font-serif text-4xl tracking-[-0.04em] text-[#151515]">发送重置链接</h2>
                        <form method="POST" action="{{ route('password.email') }}" class="mt-12 space-y-6">
                            @csrf
                            <div>
                                <label for="email" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">邮箱</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-3 w-full rounded-[1.15rem] border border-[#ddd3c9] bg-[#fcfaf7] px-5 py-4 text-base text-[#181512] outline-none transition placeholder:text-[#a8a29e] focus:border-[#151515] focus:bg-white" placeholder="name@example.com">
                            </div>
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-[1.15rem] bg-[#151515] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">
                                发送重置邮件
                            </button>
                        </form>
                        <div class="mt-8 border-t border-[#ece7e0] pt-6 text-sm text-[#6b6256]">
                            <a href="{{ route('login') }}" class="font-semibold text-[#151515] transition hover:text-[#8b8175]">返回登录</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
