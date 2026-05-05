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
<body class="min-h-screen bg-[#f4efe6] text-[#231f1a] antialiased">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,rgba(255,250,245,.95),transparent_24%),linear-gradient(180deg,#f4efe6_0%,#efe6d9_100%)]">
        <div class="mx-auto {{ $isMemberCenter ? 'max-w-[92rem]' : 'max-w-7xl' }} px-4 {{ $isMemberCenter ? 'py-0' : 'py-6 sm:py-8' }} sm:px-6 lg:px-8">
            @unless ($isMemberCenter)
                @include('themes.editorial.partials.header')
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
                @include('themes.editorial.partials.footer')
            @endunless
        </div>
    </div>
</body>
</html>
