@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => $title])

@section('content')
    <section class="rounded-[32px] border border-white/70 bg-white/92 p-8 shadow-[0_24px_80px_rgba(15,23,42,0.08)] lg:p-12">
        <div class="max-w-4xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">{{ $eyebrow }}</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $heading }}</h1>
            <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $description }}</p>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($highlights as $highlight)
                <article class="rounded-[28px] border border-slate-200/80 bg-slate-50 px-5 py-6">
                    <div class="text-sm leading-7 text-slate-700">{{ $highlight }}</div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
