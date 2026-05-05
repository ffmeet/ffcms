@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '我的评论'])

@section('content')
    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'themes.xiaofang.member.partials.page-header'), [
                'eyebrow' => 'My Comments',
                'title' => '我的评论',
                'description' => '这里统一查看你在前台提交过的评论和回复，包括审核状态以及对应文章入口。',
                'actions' => [
                    ['label' => '返回会员中心', 'url' => route('member.dashboard')],
                ],
            ])

            <section class="rounded-[30px] border border-[#efe5db] bg-white/92 p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                <form method="GET" action="{{ route('member.comments.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px_160px_auto]">
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="搜索评论内容或文章标题"
                        class="w-full rounded-2xl border border-[#e7d8c9] bg-white px-4 py-3 text-sm text-[#181512] outline-none transition focus:border-[#fb923c]"
                    >
                    <select
                        name="status"
                        class="w-full rounded-2xl border border-[#e7d8c9] bg-white px-4 py-3 text-sm text-[#181512] outline-none transition focus:border-[#fb923c]"
                    >
                        <option value="">全部状态</option>
                        <option value="approved" @selected(($filters['status'] ?? null) === 'approved')>已审</option>
                        <option value="pending" @selected(($filters['status'] ?? null) === 'pending')>待审</option>
                    </select>
                    <select
                        name="sort"
                        class="w-full rounded-2xl border border-[#e7d8c9] bg-white px-4 py-3 text-sm text-[#181512] outline-none transition focus:border-[#fb923c]"
                    >
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>最新优先</option>
                        <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>最早优先</option>
                        <option value="status" @selected(($filters['sort'] ?? null) === 'status')>按状态</option>
                    </select>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#1d4ed8] px-5 py-3 text-sm font-semibold text-white shadow-[0_16px_30px_rgba(29,78,216,0.24)] transition hover:bg-[#1e40af]">筛选</button>
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            <a href="{{ route('member.comments.index') }}" class="inline-flex items-center justify-center rounded-full border border-[#e7d8c9] bg-white px-5 py-3 text-sm font-medium text-[#6b6256] transition hover:border-[#fdba74] hover:text-[#9a3412]">清除</a>
                        @endif
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-[#6b6256]">
                    <span class="rounded-full bg-[#fff7ed] px-3 py-1">共 {{ $comments->total() }} 条评论</span>
                    <span class="rounded-full bg-[#f5f5f4] px-3 py-1">当前显示 {{ $comments->firstItem() ?? 0 }} - {{ $comments->lastItem() ?? 0 }}</span>
                    @if (filled($filters['status'] ?? null))
                        <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-[#1d4ed8]">状态：{{ $filters['status'] === 'approved' ? '已审' : '待审' }}</span>
                    @endif
                    @if (filled($filters['q'] ?? null))
                        <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-[#1d4ed8]">关键词：{{ $filters['q'] }}</span>
                    @endif
                    <span class="rounded-full bg-[#f5f5f4] px-3 py-1">排序：{{ ($filters['sort'] ?? 'latest') === 'oldest' ? '最早优先' : (($filters['sort'] ?? 'latest') === 'status' ? '按状态' : '最新优先') }}</span>
                </div>
            </section>

            <section class="space-y-4">
                @forelse ($comments as $comment)
                    @php($commentSummaryUrl = route('member.comments.index').'#comment-'.$comment->id)
                    @php($commentPostUrl = $comment->post?->slug ? route('posts.show', ['slug' => $comment->post->slug, 'focus' => $comment->id]).'#comment-'.$comment->id : null)
                    <article id="comment-{{ $comment->id }}" class="rounded-[30px] border border-[#efe5db] bg-[linear-gradient(180deg,rgba(255,250,245,.98),rgba(255,255,255,.94),rgba(239,246,255,.86))] p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($comment->status !== 'approved')
                                        <span class="rounded-full bg-[#f5f5f4] px-3 py-1 text-xs font-semibold text-[#6b6256]">
                                            {{ $comment->status === 'pending' ? '待审' : '未审' }}
                                        </span>
                                    @endif
                                    @if ($comment->parent)
                                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs text-sky-700">
                                            回复 {{ $comment->parent->user?->public_display_name ?? '楼主' }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs text-[#9a3412]">主评论</span>
                                    @endif
                                </div>

                                <h2 class="mt-3 text-base font-semibold text-[#181512]">
                                    @if ($commentPostUrl)
                                        <a href="{{ $commentPostUrl }}" class="transition hover:text-[#1d4ed8]">{{ $comment->post?->title ?? '关联文章已不可用' }}</a>
                                    @else
                                        {{ $comment->post?->title ?? '关联文章已不可用' }}
                                    @endif
                                </h2>
                                <p class="mt-2 text-sm leading-7 text-[#6b6256]">
                                    <a href="{{ $commentSummaryUrl }}" class="transition hover:text-[#151515]">{{ $comment->content }}</a>
                                </p>
                                <div class="mt-3 text-xs text-[#78716c]">
                                    发表在
                                    @if ($commentPostUrl)
                                        <a href="{{ $commentPostUrl }}" class="font-medium text-[#181512] transition hover:text-[#1d4ed8]">{{ $comment->post?->title ?? '关联文章已不可用' }}</a>
                                    @else
                                        <span class="font-medium text-[#181512]">{{ $comment->post?->title ?? '关联文章已不可用' }}</span>
                                    @endif
                                    @if ($comment->created_at)
                                        <span class="text-[#d6d3d1]"> · {{ $comment->created_at->format('n月j日') }}</span>
                                    @endif
                                </div>
                                <div class="mt-3 text-xs text-[#78716c]">
                                    提交时间 {{ optional($comment->created_at)->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                <a href="{{ $commentSummaryUrl }}" class="inline-flex items-center justify-center rounded-full border border-[#e7d8c9] bg-white px-4 py-2 text-sm font-medium text-[#6b6256] transition hover:border-[#fdba74] hover:text-[#9a3412]">查看摘要</a>
                                @if ($commentPostUrl)
                                    <a href="{{ $commentPostUrl }}" class="inline-flex items-center justify-center rounded-full border border-[#e7d8c9] bg-white px-4 py-2 text-sm font-medium text-[#6b6256] transition hover:border-[#fdba74] hover:text-[#9a3412]">查看文章</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[30px] border border-dashed border-[#d6d3d1] bg-[#fffaf5] p-8 text-sm text-[#78716c]">
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            当前筛选条件下没有找到评论，可以调整关键词或审核状态后再试。
                        @else
                            你还没有评论记录，去文章页参与一次讨论试试看。
                        @endif
                    </div>
                @endforelse
            </section>

            <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-[#78716c]">
                    第 {{ $comments->currentPage() }} / {{ $comments->lastPage() }} 页
                </p>
                {{ $comments->links() }}
            </div>
        </div>
    </div>
@endsection
