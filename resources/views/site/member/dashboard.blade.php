@extends('site.layout', ['title' => '会员中心'])

@section('content')
    @include('site.member.partials.topbar')

    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        @include('site.member.partials.nav')

        <div class="space-y-6">
            <section class="rounded-[18px] border border-slate-200/80 bg-white/96 p-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)]">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-600">Member Center</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">会员中心</h1>
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                            <div class="text-sm text-slate-500">草稿</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $draftCount }}</div>
                        </div>
                        <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                            <div class="text-sm text-slate-500">待审</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $pendingCount }}</div>
                        </div>
                        <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                            <div class="text-sm text-slate-500">已发布</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $user->posts()->where('status', 'published')->count() }}</div>
                        </div>
                        <div class="rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                            <div class="text-sm text-slate-500">评论</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $user->comments()->count() }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 md:grid-cols-2">
                <section class="rounded-[16px] border border-slate-200 bg-white p-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-slate-900">我的稿件</h2>
                        <a href="{{ route('member.posts.index') }}" class="text-sm font-medium text-sky-700 transition hover:text-sky-900">全部稿件</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentPosts as $post)
                            <article class="rounded-[12px] border border-slate-200 bg-slate-50/70 px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-semibold text-slate-900">{{ $post->title }}</h3>
                                        <p class="mt-1 text-xs text-slate-400">{{ optional($post->updated_at)->format('Y-m-d H:i') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $post->status === 'pending' ? 'bg-slate-100 text-slate-600' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $post->status === 'published' ? '已发布' : ($post->status === 'pending' ? '待审' : '草稿') }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[12px] border border-dashed border-slate-300 bg-slate-50/80 p-6 text-sm text-slate-500">你还没有稿件。</div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[16px] border border-slate-200 bg-white p-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-slate-900">我的评论</h2>
                        <a href="{{ route('member.comments.index') }}" class="text-sm font-medium text-sky-700 transition hover:text-sky-900">全部评论</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentComments as $comment)
                            <article class="rounded-[12px] border border-slate-200 bg-slate-50/70 px-4 py-3">
                                <p class="text-sm leading-6 text-slate-600">“{{ $comment->content }}”</p>
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <p class="text-xs text-slate-500">
                                        发表在
                                        <span class="font-medium text-slate-700">{{ $comment->post?->title ?? '关联文章已不可用' }}</span>
                                        @if ($comment->created_at)
                                            <span class="text-slate-400"> · {{ $comment->created_at->format('n月j日') }}</span>
                                        @endif
                                    </p>
                                    @if ($comment->status !== 'approved')
                                        <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $comment->status === 'pending' ? '待审' : '未审' }}
                                        </span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[12px] border border-dashed border-slate-300 bg-slate-50/80 p-6 text-sm text-slate-500">你还没有评论记录。</div>
                        @endforelse
                    </div>
                </section>
            </section>
        </div>
    </div>
@endsection
