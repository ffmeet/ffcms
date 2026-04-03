@extends('site.layout', ['title' => '我的稿件'])

@section('content')
    @include('site.member.partials.topbar')

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include('site.member.partials.nav')

        <div class="space-y-6">
            @include('site.member.partials.page-header', [
                'eyebrow' => 'My Posts',
                'title' => '我的稿件',
                'description' => '这里集中看自己提交过的内容。当前先支持查看状态和继续编辑草稿/待审核稿件，后面再补更完整的筛选和批量管理。',
                'actions' => [
                    ['label' => '返回会员中心', 'url' => route('member.dashboard')],
                    ['label' => '新建稿件', 'url' => route('member.posts.create'), 'variant' => 'primary'],
                ],
            ])

            <section class="rounded-[30px] border border-sky-100/70 bg-white/92 p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                <form method="GET" action="{{ route('member.posts.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px_160px_auto]">
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="搜索标题或别名"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white"
                    >
                    <select
                        name="status"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white"
                    >
                        <option value="">全部状态</option>
                        <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>草稿</option>
                        <option value="pending" @selected(($filters['status'] ?? null) === 'pending')>待审核</option>
                        <option value="published" @selected(($filters['status'] ?? null) === 'published')>已发布</option>
                    </select>
                    <select
                        name="sort"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white"
                    >
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>最新优先</option>
                        <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>最早优先</option>
                        <option value="title" @selected(($filters['sort'] ?? null) === 'title')>按标题</option>
                    </select>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-blue-600 hover:to-slate-900">筛选</button>
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            <a href="{{ route('member.posts.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700">清除</a>
                        @endif
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span class="rounded-full bg-slate-100 px-3 py-1">共 {{ $posts->total() }} 篇稿件</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1">当前显示 {{ $posts->firstItem() ?? 0 }} - {{ $posts->lastItem() ?? 0 }}</span>
                    @if (filled($filters['status'] ?? null))
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-sky-700">状态：{{ $filters['status'] === 'draft' ? '草稿' : ($filters['status'] === 'pending' ? '待审核' : '已发布') }}</span>
                    @endif
                    @if (filled($filters['q'] ?? null))
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-sky-700">关键词：{{ $filters['q'] }}</span>
                    @endif
                    <span class="rounded-full bg-slate-100 px-3 py-1">排序：{{ ($filters['sort'] ?? 'latest') === 'oldest' ? '最早优先' : (($filters['sort'] ?? 'latest') === 'title' ? '按标题' : '最新优先') }}</span>
                </div>
            </section>

            <section class="space-y-4">
                @forelse ($posts as $post)
                    <article class="rounded-[30px] border border-sky-100/70 bg-white/92 p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($post->status === 'pending' ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700') }}">
                                        {{ $post->status === 'published' ? '已发布' : ($post->status === 'pending' ? '待审核' : '草稿') }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">{{ $post->category?->name ?? '未分类' }}</span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">{{ $post->contentModel?->name ?? '未指定模型' }}</span>
                                </div>
                                <h2 class="mt-3 text-xl font-semibold text-slate-900">{{ $post->title }}</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $post->summary ?: '当前还没有摘要。' }}</p>
                                <div class="mt-3 text-xs text-slate-400">更新时间 {{ optional($post->updated_at)->format('Y-m-d H:i') }}</div>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                @if ($post->slug && $post->status === 'published')
                                    <a href="{{ route('posts.show', $post->slug) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700">查看前台</a>
                                @endif
                                <a href="{{ route('member.posts.edit', $post) }}" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 transition hover:from-blue-600 hover:to-slate-900">继续编辑</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[30px] border border-dashed border-slate-300 bg-white/82 p-8 text-sm text-slate-500">
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            当前筛选条件下没有找到稿件，可以调整关键词或状态后再试。
                        @else
                            你还没有任何稿件，先去发布第一篇内容。
                        @endif
                    </div>
                @endforelse
            </section>

            <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">
                    第 {{ $posts->currentPage() }} / {{ $posts->lastPage() }} 页
                </p>
                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection
