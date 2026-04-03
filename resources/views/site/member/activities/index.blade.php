@extends('site.layout', ['title' => '我的活动'])

@section('content')
    @include('site.member.partials.topbar')

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include('site.member.partials.nav')

        <div class="space-y-6">
            @include('site.member.partials.page-header', [
                'eyebrow' => 'My Activities',
                'title' => '我的活动',
                'description' => '这里汇总最近的稿件和评论动态，让右侧内容区域真正承载不同列表页。',
            ])

            <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="grid gap-4 md:grid-cols-4">
                    <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-sm text-slate-500">已发布</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $activitySummary['published_posts'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-sky-100 bg-sky-50/80 p-4">
                        <p class="text-sm text-sky-700">待审核</p>
                        <p class="mt-2 text-2xl font-semibold text-sky-700">{{ $activitySummary['pending_posts'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-sm text-slate-500">草稿</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $activitySummary['draft_posts'] }}</p>
                    </article>
                    <article class="rounded-[24px] border border-emerald-100 bg-emerald-50/80 p-4">
                        <p class="text-sm text-emerald-700">评论通过</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $activitySummary['approved_comments'] }}</p>
                    </article>
                </div>
            </section>

            <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Timeline</h2>
                        <p class="mt-1 text-sm text-slate-500">稿件和评论混合时间线，方便查看最近发生了什么。</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($timeline as $item)
                        <article class="grid gap-4 rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,250,252,.92))] px-5 py-4 md:grid-cols-[110px_minmax(0,1fr)_auto] md:items-center">
                            <div class="text-sm font-semibold text-sky-700">{{ $item['type'] }}</div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $item['title'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $item['description'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $item['badge'] }}</span>
                                <div class="mt-2 text-xs text-slate-400">{{ $item['time'] }}</div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50/80 p-8 text-sm text-slate-500">当前还没有活动记录。</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
