@extends('site.layout', ['title' => '活动中心'])

@section('content')
    @include('site.member.partials.topbar')

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include('site.member.partials.nav')

        <div class="space-y-6">
            @include('site.member.partials.page-header', [
                'eyebrow' => 'Activity Center',
                'title' => '活动中心',
                'description' => '这里承接前台会员的运营与互动概览，包括稿件推进、评论反馈和最近站内动作。',
                'actions' => [
                    ['label' => '查看我的活动', 'url' => route('member.activities.index')],
                ],
            ])

            <div class="grid gap-5 xl:grid-cols-3">
                <article class="rounded-[30px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-slate-500">已发布稿件</p>
                    <p class="mt-3 text-4xl font-semibold text-slate-900">{{ $activitySummary['published_posts'] }}</p>
                </article>
                <article class="rounded-[30px] border border-sky-100/80 bg-sky-50/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-sky-700">待审核稿件</p>
                    <p class="mt-3 text-4xl font-semibold text-sky-700">{{ $activitySummary['pending_posts'] }}</p>
                </article>
                <article class="rounded-[30px] border border-emerald-100/80 bg-emerald-50/80 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                    <p class="text-sm text-emerald-700">已通过评论</p>
                    <p class="mt-3 text-4xl font-semibold text-emerald-700">{{ $activitySummary['approved_comments'] }}</p>
                </article>
            </div>

            <section class="rounded-[32px] border border-slate-200/80 bg-white/96 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">活动面板</h2>
                        <p class="mt-1 text-sm text-slate-500">这里将逐步扩展站内通知、任务提醒、活动报名和互动反馈。</p>
                    </div>
                    <a href="{{ route('member.posts.create') }}" class="inline-flex items-center justify-center rounded-[18px] bg-sky-50 px-4 py-2.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">继续投稿</a>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">稿件推进</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-500">你的前台投稿已经接通草稿、送审和后台审核链路，后续会继续接站内消息与活动通知。</p>
                    </article>
                    <article class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">互动节奏</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-500">评论、回复和审核状态会逐步汇总到这里，形成更完整的个人活动视图。</p>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection
