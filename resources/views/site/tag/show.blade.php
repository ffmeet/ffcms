@extends('site.layout', ['title' => '#' . $tag->name . ' - 帝国 CMS'])

@section('content')
    <section class="mb-6 rounded-[30px] border border-stone-200/80 bg-white/85 p-8 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">Tag Archive</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight text-stone-900"># {{ $tag->name }}</h1>
        <p class="mt-4 max-w-3xl text-sm leading-7 text-stone-600">
            这里汇总同一标签下的内容，用来承接文章阅读后的继续发现路径。
        </p>
        <div class="mt-5 inline-flex rounded-full bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800">
            当前共 {{ $posts->total() }} 篇已发布内容
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($posts as $post)
            <article class="overflow-hidden rounded-[24px] border border-stone-200/80 bg-white/85 shadow-sm">
                @if ($post->isFlashModel())
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">快讯</div>
                            <div class="text-xs text-stone-400">{{ optional($post->published_at)->format('m-d H:i') }}</div>
                        </div>
                        <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">{{ $post->category?->name ?? '未分类' }}</div>
                        <h2 class="mt-3 text-xl font-semibold text-stone-900">
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
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-stone-500">
                            <span>作者 {{ $post->display_author }}</span>
                            <span>浏览 {{ $post->statistics?->views ?? 0 }}</span>
                        </div>
                    </div>
                @else
                    @if ($post->cover_image_url)
                        <a href="{{ route('posts.show', $post->slug) }}" class="block border-b border-stone-200 bg-stone-100">
                            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-52 w-full object-cover">
                        </a>
                    @endif
                    <div class="p-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">{{ $post->category?->name ?? '未分类' }}</div>
                        <h2 class="mt-3 text-xl font-semibold text-stone-900">
                            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        @if ($post->summary)
                            <p class="mt-3 text-sm leading-7 text-stone-600">{{ $post->summary }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-stone-500">
                            <span>作者 {{ $post->display_author }}</span>
                            <span>浏览 {{ $post->statistics?->views ?? 0 }}</span>
                        </div>
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-[24px] border border-dashed border-stone-300 bg-white/70 p-8 text-sm text-stone-500 md:col-span-2 xl:col-span-3">
                当前标签下暂时还没有已发布内容。
            </div>
        @endforelse
    </section>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection
