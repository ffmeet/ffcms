@extends('site.layout', ['title' => ($query ? '搜索：' . $query : '搜索') . ' - 帝国 CMS'])

@section('content')
    <section class="mb-6 rounded-[30px] border border-stone-200/80 bg-white/85 p-8 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">Search</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight text-stone-900">站内搜索</h1>
        <p class="mt-4 max-w-3xl text-sm leading-7 text-stone-600">
            支持按文章标题、正文内容和标签关键词搜索，先把内容发现入口搭起来，后面再逐步细化搜索体验。
        </p>

        <form method="GET" action="{{ route('search') }}" class="mt-6 rounded-[26px] border border-stone-200 bg-stone-50 p-3">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="search"
                    name="q"
                    value="{{ $query }}"
                    placeholder="搜索文章标题、正文关键词或标签"
                    class="min-w-0 flex-1 rounded-[20px] border border-stone-200 bg-white px-5 py-4 text-sm text-stone-700 outline-none transition placeholder:text-stone-400 focus:border-amber-400"
                >
                <button type="submit" class="rounded-[20px] bg-stone-900 px-6 py-4 text-sm font-semibold text-white transition hover:bg-amber-600">开始搜索</button>
            </div>
        </form>
    </section>

    <section class="rounded-[30px] border border-stone-200/80 bg-white/85 p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Results</p>
                <h2 class="mt-2 text-2xl font-semibold text-stone-900">
                    @if ($query)
                        “{{ $query }}” 的搜索结果
                    @else
                        输入关键词开始搜索
                    @endif
                </h2>
            </div>
            <div class="rounded-full bg-stone-100 px-4 py-2 text-sm text-stone-600">
                {{ $posts->total() }} 条结果
            </div>
        </div>

        <div class="grid gap-4">
            @forelse ($posts as $post)
                <article class="rounded-[24px] border border-stone-200 bg-stone-50/80 p-5">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">
                        @if ($post->isFlashModel())
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px]">快讯</span>
                        @endif
                        <span>{{ $post->category?->name ?? '未分类' }}</span>
                        <span class="text-stone-300">/</span>
                        <span>{{ optional($post->published_at)->format($post->isFlashModel() ? 'm-d H:i' : 'Y-m-d') }}</span>
                    </div>
                    <h3 class="mt-3 text-xl font-semibold text-stone-900">
                        <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                    </h3>
                    @if ($post->summary)
                        <p class="mt-3 text-sm leading-7 text-stone-600">{{ $post->summary }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-stone-500">
                        <span>作者 {{ $post->display_author }}</span>
                        <span>浏览 {{ $post->statistics?->views ?? 0 }}</span>
                        @unless ($post->isFlashModel())
                            <span>评论 {{ $post->statistics?->comments_count ?? 0 }}</span>
                        @endunless
                    </div>
                </article>
            @empty
                <div class="rounded-[24px] border border-dashed border-stone-300 bg-white/70 p-8 text-sm text-stone-500">
                    @if ($query)
                        没有找到和 “{{ $query }}” 相关的已发布内容，可以换个关键词再试。
                    @else
                        请输入关键词后开始搜索。
                    @endif
                </div>
            @endforelse
        </div>
    </section>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection
