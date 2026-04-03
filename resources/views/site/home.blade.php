@extends('site.layout', ['title' => '帝国 CMS 首页'])

@section('content')
    <section class="mb-6 grid gap-6 lg:grid-cols-[1.4fr_0.9fr]">
        <div class="rounded-[30px] border border-stone-200/80 bg-white/85 p-8 shadow-sm">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">Empire CMS Rebuild</p>
            <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl">前台内容骨架已经切入，后续将继续扩展为完整的 CMS 门户。</h1>
            <p class="mt-5 max-w-2xl text-sm leading-7 text-stone-600">
                当前阶段先打通首页、栏目页、文章详情页、注册登录和会员中心，让整个站点从后台到前台形成闭环，再继续补搜索、上传、互动和 SEO。
            </p>
            <form method="GET" action="{{ route('search') }}" class="mt-6 rounded-[26px] border border-stone-200 bg-stone-50 p-3 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        type="search"
                        name="q"
                        placeholder="输入文章标题、正文关键词或标签"
                        class="min-w-0 flex-1 rounded-[20px] border border-stone-200 bg-white px-5 py-4 text-sm text-stone-700 outline-none transition placeholder:text-stone-400 focus:border-amber-400"
                    >
                    <button class="rounded-[20px] bg-stone-900 px-6 py-4 text-sm font-semibold text-white transition hover:bg-amber-600" type="submit">搜索内容</button>
                </div>
            </form>
            <div class="mt-6 flex flex-wrap gap-3">
                <a class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-700" href="{{ route('register') }}">前台注册</a>
                <a class="rounded-full border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:border-amber-400 hover:text-amber-700" href="{{ url('/admin') }}">进入后台</a>
            </div>
        </div>

        <div class="rounded-[30px] border border-stone-200/80 bg-white/85 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-stone-900">栏目导航</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4 transition hover:border-amber-300 hover:bg-amber-50">
                        <div class="font-medium text-stone-900">{{ $category->name }}</div>
                        <div class="mt-1 text-sm text-stone-500">{{ $category->posts_count }} 篇内容</div>
                    </a>
                @empty
                    <p class="text-sm text-stone-500">当前还没有可展示栏目。</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mb-6 rounded-[30px] border border-stone-200/80 bg-white/85 p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Topics</p>
                <h2 class="mt-2 text-2xl font-semibold text-stone-900">热门标签</h2>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            @forelse ($tags as $tag)
                <a href="{{ route('tags.show', $tag->slug) }}" class="rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800">
                    # {{ $tag->name }}
                    <span class="ml-1 text-xs text-stone-400">{{ $tag->count }}</span>
                </a>
            @empty
                <p class="text-sm text-stone-500">当前还没有可展示标签。</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-[30px] border border-stone-200/80 bg-white/85 p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Latest Content</p>
                <h2 class="mt-2 text-2xl font-semibold text-stone-900">最新内容</h2>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($posts as $post)
                @if ($post->isFlashModel())
                    <article class="rounded-[24px] border border-stone-200 bg-[linear-gradient(180deg,#fffdf8_0%,#fafaf9_100%)] p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">快讯</div>
                            <div class="text-xs text-stone-400">{{ optional($post->published_at)->format('m-d H:i') }}</div>
                        </div>
                        <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">{{ $post->category?->name ?? '未分类' }}</div>
                        <h3 class="mt-3 text-lg font-semibold leading-7 text-stone-900">
                            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        @if ($post->summary)
                            <p class="mt-3 text-sm leading-7 text-stone-600">{{ $post->summary }}</p>
                        @endif
                        @if ($post->cover_image_url)
                            <a href="{{ route('posts.show', $post->slug) }}" class="mt-4 block overflow-hidden rounded-2xl border border-stone-200 bg-stone-100">
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-40 w-full object-cover">
                            </a>
                        @endif
                        <div class="mt-4 flex gap-3 text-xs text-stone-500">
                            <span>作者 {{ $post->display_author }}</span>
                            <span>浏览 {{ $post->statistics?->views ?? 0 }}</span>
                        </div>
                    </article>
                @else
                    <article class="rounded-[24px] border border-stone-200 bg-stone-50/80 p-5">
                        @if ($post->cover_image_url)
                            <a href="{{ route('posts.show', $post->slug) }}" class="mb-4 block overflow-hidden rounded-2xl border border-stone-200 bg-stone-100">
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-48 w-full object-cover">
                            </a>
                        @endif
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">{{ $post->category?->name ?? '未分类' }}</div>
                        <h3 class="mt-3 text-lg font-semibold leading-7 text-stone-900">
                            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        @if ($post->summary)
                            <p class="mt-3 text-sm leading-7 text-stone-600">{{ $post->summary }}</p>
                        @endif
                        <div class="mt-3 text-sm text-stone-500">
                            作者 {{ $post->display_author }} · {{ optional($post->published_at)->format('Y-m-d H:i') }}
                        </div>
                        <div class="mt-4 flex gap-3 text-xs text-stone-500">
                            <span>浏览 {{ $post->statistics?->views ?? 0 }}</span>
                            <span>评论 {{ $post->statistics?->comments_count ?? 0 }}</span>
                        </div>
                    </article>
                @endif
            @empty
                <p class="text-sm text-stone-500">当前还没有已发布文章。</p>
            @endforelse
        </div>
    </section>
@endsection
