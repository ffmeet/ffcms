@extends('site.layout', ['title' => '我的评论'])

@section('content')
    @include('site.member.partials.topbar')

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include('site.member.partials.nav')

        <div class="space-y-6">
            @include('site.member.partials.page-header', [
                'eyebrow' => 'My Comments',
                'title' => '我的评论',
                'description' => '这里统一查看你在前台提交过的评论和回复，包括审核状态以及对应文章入口。',
                'actions' => [
                    ['label' => '返回会员中心', 'url' => route('member.dashboard')],
                ],
            ])

            <section class="rounded-[30px] border border-sky-100/70 bg-white/92 p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                <form method="GET" action="{{ route('member.comments.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px_160px_auto]">
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="搜索评论内容或文章标题"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white"
                    >
                    <select
                        name="status"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white"
                    >
                        <option value="">全部状态</option>
                        <option value="approved" @selected(($filters['status'] ?? null) === 'approved')>已审</option>
                        <option value="pending" @selected(($filters['status'] ?? null) === 'pending')>待审</option>
                    </select>
                    <select
                        name="sort"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white"
                    >
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>最新优先</option>
                        <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>最早优先</option>
                        <option value="status" @selected(($filters['sort'] ?? null) === 'status')>按状态</option>
                    </select>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:from-blue-600 hover:to-slate-900">筛选</button>
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            <a href="{{ route('member.comments.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700">清除</a>
                        @endif
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span class="rounded-full bg-slate-100 px-3 py-1">共 {{ $comments->total() }} 条评论</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1">当前显示 {{ $comments->firstItem() ?? 0 }} - {{ $comments->lastItem() ?? 0 }}</span>
                    @if (filled($filters['status'] ?? null))
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">状态：{{ $filters['status'] === 'approved' ? '已审' : '待审' }}</span>
                    @endif
                    @if (filled($filters['q'] ?? null))
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-sky-700">关键词：{{ $filters['q'] }}</span>
                    @endif
                    <span class="rounded-full bg-slate-100 px-3 py-1">排序：{{ ($filters['sort'] ?? 'latest') === 'oldest' ? '最早优先' : (($filters['sort'] ?? 'latest') === 'status' ? '按状态' : '最新优先') }}</span>
                </div>
            </section>

            <section class="space-y-4">
                @forelse ($comments as $comment)
                    <article class="rounded-[30px] border border-sky-100/70 bg-white/92 p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($comment->status !== 'approved')
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $comment->status === 'pending' ? '待审' : '未审' }}
                                        </span>
                                    @endif
                                    @if ($comment->parent)
                                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs text-sky-700">
                                            回复 {{ $comment->parent->user?->username ?? '楼主' }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">主评论</span>
                                    @endif
                                </div>

                                <h2 class="mt-3 text-base font-semibold text-slate-900">
                                    {{ $comment->post?->title ?? '关联文章已不可用' }}
                                </h2>
                                <p class="mt-2 text-sm leading-7 text-slate-600">{{ $comment->content }}</p>
                                <div class="mt-3 text-xs text-slate-500">
                                    发表在
                                    <span class="font-medium text-slate-700">{{ $comment->post?->title ?? '关联文章已不可用' }}</span>
                                    @if ($comment->created_at)
                                        <span class="text-slate-400"> · {{ $comment->created_at->format('n月j日') }}</span>
                                    @endif
                                </div>
                                <div class="mt-3 text-xs text-slate-400">
                                    提交时间 {{ optional($comment->created_at)->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                @if ($comment->post?->slug)
                                    <a href="{{ route('posts.show', $comment->post->slug) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-sky-300 hover:text-sky-700">查看文章</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[30px] border border-dashed border-slate-300 bg-white/82 p-8 text-sm text-slate-500">
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            当前筛选条件下没有找到评论，可以调整关键词或审核状态后再试。
                        @else
                            你还没有评论记录，去文章页参与一次讨论试试看。
                        @endif
                    </div>
                @endforelse
            </section>

            <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">
                    第 {{ $comments->currentPage() }} / {{ $comments->lastPage() }} 页
                </p>
                {{ $comments->links() }}
            </div>
        </div>
    </div>
@endsection
