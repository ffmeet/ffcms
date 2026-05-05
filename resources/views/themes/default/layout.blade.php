<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $settings = $siteSettings ?? \App\Models\SiteSetting::make(\App\Models\SiteSetting::defaults());
        $pageTitle = $title ?? $settings->seo_title ?? $settings->site_name ?? '年度科技先生';
        $pageDescription = $description ?? $settings->seo_description ?? $settings->site_description;
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @include('partials.site-favicons', ['settings' => $settings])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php($isMemberCenter = request()->routeIs('member.*'))
<body class="min-h-screen bg-[#f3ecdf] text-slate-900 antialiased">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,#fff8ef,transparent_24%),radial-gradient(circle_at_right,#dbeafe,transparent_22%),linear-gradient(180deg,#f5efe5_0%,#eef3fb_52%,#f7f5f0_100%)]">
        <div class="mx-auto {{ $isMemberCenter ? 'max-w-[90rem]' : 'max-w-7xl' }} px-4 {{ $isMemberCenter ? 'py-0' : 'py-6 sm:py-8' }} sm:px-6 lg:px-8">
            @unless ($isMemberCenter)
                @include('themes.default.partials.header')
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

            @unless ($isMemberCenter)
                @include('themes.default.partials.footer')
            @endunless
        </div>
    </div>
</body>
</html>
