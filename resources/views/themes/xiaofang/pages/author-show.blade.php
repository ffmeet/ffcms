@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => $author->public_display_name . ' - 作者'])

@section('content')
    <article class="pb-12">
        <section class="border-b border-[#e6dfd6] pb-12 text-center">
            <div class="flex flex-col items-center gap-6">
                @if ($author->avatarUrl('large') || $author->avatarUrl('medium'))
                    <img src="{{ $author->avatarUrl('large') ?: $author->avatarUrl('medium') }}" alt="{{ $author->public_display_name }}" class="h-[150px] w-[150px] rounded-full object-cover">
                @else
                    <div class="flex h-[150px] w-[150px] items-center justify-center rounded-full bg-[#ebe7e0] font-serif text-5xl text-[#151515]">
                        {{ \Illuminate\Support\Str::substr($author->public_display_name, 0, 1) }}
                    </div>
                @endif

                <div>
                    <h1 class="font-serif text-5xl font-semibold leading-tight text-[#151515]">{{ $author->public_display_name }}</h1>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-[#4b443c]">{{ $author->author_bio }}</p>
                </div>

                <div class="flex items-center gap-3 text-[#151515]">
                    @foreach (collect($author->social_links ?? [])->take(2) as $social)
                        <a href="{{ $social['url'] ?? '#' }}" target="_blank" rel="noreferrer" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#d8d1c8] text-xs font-semibold uppercase tracking-[0.18em] transition hover:border-[#151515]">
                            {{ \Illuminate\Support\Str::substr($social['label'] ?? 'Web', 0, 1) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="author-archive" class="mt-12">
            @php($featuredPosts = $featuredPosts ?? collect())

            @if ($featuredPosts->isEmpty() && $authorPosts->isEmpty())
                <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-8 text-sm text-[#6b6256]">这位作者暂时还没有已发布文章。</div>
            @else
                @if ($featuredPosts->isNotEmpty())
                    <section class="mb-12 border-b border-[#e6dfd6] pb-12">
                        <div class="mb-6 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">Featured Stories</p>
                                <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-[#151515]">作者精选文章</h2>
                            </div>
                        </div>

                        <div class="grid gap-8 lg:grid-cols-2">
                            @foreach ($featuredPosts as $featuredPost)
                                <article class="space-y-5">
                                    <a href="{{ route('posts.show', $featuredPost->slug) }}" class="block overflow-hidden bg-[#ece8e1]">
                                        @if ($featuredPost->cover_image_url)
                                            <img src="{{ $featuredPost->cover_image_url }}" alt="{{ $featuredPost->title }}" class="h-[280px] w-full object-cover">
                                        @else
                                            <div class="h-[280px] w-full bg-[#ebe7e0]"></div>
                                        @endif
                                    </a>

                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">{{ $featuredPost->category?->name ?? 'Feature' }}</p>
                                        <h3 class="mt-4 font-serif text-3xl font-semibold leading-[1.12] tracking-tight text-[#151515]">
                                            <a href="{{ route('posts.show', $featuredPost->slug) }}">{{ $featuredPost->title }}</a>
                                        </h3>
                                        <p class="mt-4 text-base leading-8 text-[#5f574f]">{{ $featuredPost->summary ?: '作者当前重点文章。' }}</p>
                                        <div class="mt-6 text-sm text-[#8b8175]">
                                            By {{ $author->public_display_name }} · {{ optional($featuredPost->published_at)->format('Y.m.d') }}
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($authorPosts as $post)
                        <article class="space-y-4">
                            <a href="{{ route('posts.show', $post->slug) }}" class="block overflow-hidden bg-[#ece8e1]">
                                @if ($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-[190px] w-full object-cover">
                                @else
                                    <div class="h-[190px] w-full bg-[#ebe7e0]"></div>
                                @endif
                            </a>
                            <div>
                                <div class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">{{ $post->category?->name ?? 'Feature' }}</div>
                                <h3 class="mt-2 font-serif text-2xl font-semibold leading-snug text-[#151515]">
                                    <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p class="mt-3 text-sm leading-6 text-[#5f574f]">{{ $post->summary ?: '继续阅读作者更多文章。' }}</p>
                                <div class="mt-3 text-xs text-[#8b8175]">By {{ $author->public_display_name }} · {{ optional($post->published_at)->format('Y.m.d') }}</div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="site-pagination mt-10">
                    {{ $authorPosts->links() }}
                </div>
            @endif
        </section>
    </article>
@endsection
