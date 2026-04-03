<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? '帝国 CMS' }}</title>
    <link rel="stylesheet" href="{{ asset('site/site.css') }}">
</head>
@php($isMemberCenter = request()->routeIs('member.*'))
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,#dbeafe,transparent_28%),radial-gradient(circle_at_bottom_right,#d1fae5,transparent_22%),linear-gradient(180deg,#f7fbff_0%,#eef5fb_100%)]">
        <div class="mx-auto {{ $isMemberCenter ? 'max-w-[88rem]' : 'max-w-6xl' }} px-4 {{ $isMemberCenter ? 'py-0' : 'py-6' }} sm:px-6 lg:px-8">
            @unless ($isMemberCenter)
                <header class="mb-8 flex flex-col gap-4 rounded-[24px] border border-sky-100/80 bg-white/82 px-5 py-4 shadow-[0_22px_60px_rgba(15,23,42,0.08)] backdrop-blur md:flex-row md:items-center md:justify-between">
                    <a href="{{ route('site.home') }}" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-blue-600 to-slate-900 text-lg font-bold text-white shadow-[0_14px_30px_rgba(37,99,235,0.28)]">帝</span>
                        <span class="flex flex-col">
                            <span class="text-base font-semibold tracking-wide">帝国 CMS</span>
                            <span class="text-xs uppercase tracking-[0.24em] text-slate-500">Frontend Portal</span>
                        </span>
                    </a>

                    <nav class="flex flex-wrap items-center gap-2 text-sm">
                        <form method="GET" action="{{ route('search') }}" class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 shadow-sm">
                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="搜索文章 / 标签"
                                class="w-36 bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400 sm:w-44"
                            >
                            <button type="submit" class="rounded-full bg-slate-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-blue-600">搜索</button>
                        </form>
                        @auth
                            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700" href="{{ route('member.dashboard') }}">会员中心</a>
                            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700" href="{{ url('/admin') }}">后台</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition hover:border-rose-300 hover:text-rose-700" type="submit">退出登录</button>
                            </form>
                        @else
                            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700" href="{{ route('login') }}">登录</a>
                            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700" href="{{ route('register') }}">注册</a>
                            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700" href="{{ url('/admin') }}">后台</a>
                        @endauth
                    </nav>
                </header>
            @endunless

            @if (session('status'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
