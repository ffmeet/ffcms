@extends('site.layout', ['title' => $category->name . ' - 帝国 CMS'])

@section('content')
    <section class="mb-6 rounded-[30px] border border-stone-200/80 bg-white/85 p-8 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">Category</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight text-stone-900">{{ $category->name }}</h1>
        <p class="mt-4 max-w-3xl text-sm leading-7 text-stone-600">{{ $category->description ?: '当前栏目还没有栏目描述。' }}</p>
    </section>

    <section class="grid gap-4">
        @forelse ($posts as $post)
            @if ($post->isFlashModel())
                <article class="rounded-[24px] border border-stone-200/80 bg-[linear-gradient(180deg,#fffdf8_0%,#ffffff_100%)] p-5 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700">快讯</span>
                        <span>{{ optional($post->published_at)->format('m-d H:i') }}</span>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-stone-900">
                        <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    @if ($post->summary)
                        <p class="mt-3 text-sm leading-7 text-stone-600">{{ $post->summary }}</p>
                    @endif
                    @if ($post->cover_image_url)
                        <a href="{{ route('posts.show', $post->slug) }}" class="mt-4 block overflow-hidden rounded-2xl border border-stone-200 bg-stone-100">
                            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-40 w-full object-cover">
                        </a>
                    @endif
                    <div class="mt-4 text-sm text-stone-500">
                        作者 {{ $post->display_author }} · 浏览 {{ $post->statistics?->views ?? 0 }}
                    </div>
                </article>
            @else
                <article class="rounded-[24px] border border-stone-200/80 bg-white/85 p-5 shadow-sm">
                    @if ($post->cover_image_url)
                        <a href="{{ route('posts.show', $post->slug) }}" class="mb-4 block overflow-hidden rounded-2xl border border-stone-200 bg-stone-100">
                            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-52 w-full object-cover">
                        </a>
                    @endif
                    <h2 class="text-xl font-semibold text-stone-900">
                        <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    @if ($post->summary)
                        <p class="mt-3 text-sm leading-7 text-stone-600">{{ $post->summary }}</p>
                    @endif
                    <div class="mt-2 text-sm text-stone-500">
                        作者 {{ $post->display_author }} · {{ optional($post->published_at)->format('Y-m-d H:i') }}
                    </div>
                    <div class="mt-4 text-sm text-stone-500">
                        浏览 {{ $post->statistics?->views ?? 0 }} / 评论 {{ $post->statistics?->comments_count ?? 0 }}
                    </div>
                </article>
            @endif
        @empty
            <div class="rounded-[24px] border border-dashed border-stone-300 bg-white/70 p-8 text-sm text-stone-500">
                该栏目下暂时还没有已发布内容。
            </div>
        @endforelse
    </section>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection
