@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => $title])

@section('content')
    <section class="rounded-[32px] border border-white/70 bg-[linear-gradient(160deg,rgba(255,255,255,.96),rgba(255,247,237,.95),rgba(219,234,254,.90))] p-8 shadow-[0_24px_80px_rgba(15,23,42,0.08)] lg:p-12">
        <div class="max-w-4xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#c2410c]">{{ $eyebrow }}</p>
            <h1 class="mt-4 text-4xl font-black tracking-tight text-[#181512] sm:text-5xl">{{ $heading }}</h1>
            <p class="mt-5 max-w-3xl text-base leading-8 text-[#5f574f]">{{ $description }}</p>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($highlights as $highlight)
                <article class="rounded-[28px] border border-[#efe5db] bg-white/88 px-5 py-6">
                    <div class="text-sm leading-7 text-[#6b6256]">{{ $highlight }}</div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
