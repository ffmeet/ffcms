@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => $author->public_display_name . ' - 作者'])

@section('content')
    <section class="rounded-[34px] border border-white/70 bg-white/92 p-8 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
        <div class="grid gap-8 lg:grid-cols-[140px_minmax(0,1fr)]">
            <div>
                @if ($author->avatarUrl('medium'))
                    <img src="{{ $author->avatarUrl('medium') }}" alt="{{ $author->public_display_name }}" class="h-[140px] w-[140px] rounded-full object-cover">
                @else
                    <div class="flex h-[140px] w-[140px] items-center justify-center rounded-full bg-slate-100 text-5xl font-semibold text-slate-800">
                        {{ \Illuminate\Support\Str::substr($author->public_display_name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-slate-500">Author</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">{{ $author->public_display_name }}</h1>
                <p class="mt-4 max-w-3xl text-base leading-8 text-slate-600">{{ $author->author_bio }}</p>
                <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-500">
                    <span>{{ $author->author_headline }}</span>
                    <span>{{ $publishedPostsCount }} 篇文章</span>
                </div>
            </div>
        </div>

        <div class="mt-10 grid gap-6 xl:grid-cols-[1fr_1fr]">
            <div>
                @if ($featuredPost)
                    <article class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-slate-50">
                        @if ($featuredPost->cover_image_url)
                            <img src="{{ $featuredPost->cover_image_url }}" alt="{{ $featuredPost->title }}" class="h-[320px] w-full object-cover">
                        @endif
                        <div class="p-6">
                            <h2 class="text-3xl font-semibold tracking-tight text-slate-950">
                                <a href="{{ route('posts.show', $featuredPost->slug) }}">{{ $featuredPost->title }}</a>
                            </h2>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $featuredPost->summary ?: '作者精选文章。' }}</p>
                        </div>
                    </article>
                @endif
            </div>

            <div class="space-y-4">
                @forelse ($authorPosts as $post)
                    <article class="rounded-[24px] border border-slate-200/80 bg-white px-5 py-5">
                        <h3 class="text-2xl font-semibold tracking-tight text-slate-950">
                            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $post->summary ?: '继续阅读作者更多文章。' }}</p>
                    </article>
                @empty
                    <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-sm text-slate-500">暂无更多文章。</div>
                @endforelse

                <div class="site-pagination pt-4">
                    {{ $authorPosts->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
