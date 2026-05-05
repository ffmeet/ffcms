@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '我的稿件'])

@section('content')
    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @include(\App\Support\SiteTheme::view('member.partials.page-header', 'themes.xiaofang.member.partials.page-header'), [
                'eyebrow' => 'My Posts',
                'title' => '我的稿件',
                'description' => '这里集中看自己提交过的内容。当前先支持查看状态和继续编辑草稿/待审核稿件，后面再补更完整的筛选和批量管理。',
                'actions' => [
                    ['label' => '返回会员中心', 'url' => route('member.dashboard')],
                    ['label' => '新建稿件', 'url' => route('member.posts.create'), 'variant' => 'primary'],
                ],
            ])

            <section class="rounded-[30px] border border-[#efe5db] bg-white/92 p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                <form method="GET" action="{{ route('member.posts.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px_160px_auto]">
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="搜索标题或别名"
                        class="w-full rounded-2xl border border-[#e7d8c9] bg-white px-4 py-3 text-sm text-[#181512] outline-none transition focus:border-[#fb923c]"
                    >
                    <select
                        name="status"
                        class="w-full rounded-2xl border border-[#e7d8c9] bg-white px-4 py-3 text-sm text-[#181512] outline-none transition focus:border-[#fb923c]"
                    >
                        <option value="">全部状态</option>
                        <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>草稿</option>
                        <option value="pending" @selected(($filters['status'] ?? null) === 'pending')>待审核</option>
                        <option value="published" @selected(($filters['status'] ?? null) === 'published')>已发布</option>
                    </select>
                    <select
                        name="sort"
                        class="w-full rounded-2xl border border-[#e7d8c9] bg-white px-4 py-3 text-sm text-[#181512] outline-none transition focus:border-[#fb923c]"
                    >
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>最新优先</option>
                        <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>最早优先</option>
                        <option value="title" @selected(($filters['sort'] ?? null) === 'title')>按标题</option>
                    </select>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#1d4ed8] px-5 py-3 text-sm font-semibold text-white shadow-[0_16px_30px_rgba(29,78,216,0.24)] transition hover:bg-[#1e40af]">筛选</button>
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            <a href="{{ route('member.posts.index') }}" class="inline-flex items-center justify-center rounded-full border border-[#e7d8c9] bg-white px-5 py-3 text-sm font-medium text-[#6b6256] transition hover:border-[#fdba74] hover:text-[#9a3412]">清除</a>
                        @endif
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-[#6b6256]">
                    <span class="rounded-full bg-[#fff7ed] px-3 py-1">共 {{ $posts->total() }} 篇稿件</span>
                    <span class="rounded-full bg-[#f5f5f4] px-3 py-1">当前显示 {{ $posts->firstItem() ?? 0 }} - {{ $posts->lastItem() ?? 0 }}</span>
                    @if (filled($filters['status'] ?? null))
                        <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-[#1d4ed8]">状态：{{ $filters['status'] === 'draft' ? '草稿' : ($filters['status'] === 'pending' ? '待审核' : '已发布') }}</span>
                    @endif
                    @if (filled($filters['q'] ?? null))
                        <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-[#1d4ed8]">关键词：{{ $filters['q'] }}</span>
                    @endif
                    <span class="rounded-full bg-[#f5f5f4] px-3 py-1">排序：{{ ($filters['sort'] ?? 'latest') === 'oldest' ? '最早优先' : (($filters['sort'] ?? 'latest') === 'title' ? '按标题' : '最新优先') }}</span>
                </div>
            </section>

            <section class="space-y-4">
                @forelse ($posts as $post)
                    @php($postPrimaryUrl = $post->slug && $post->status === 'published' ? route('posts.show', $post->slug) : route('member.posts.edit', $post))
                    <article class="rounded-[30px] border border-[#efe5db] bg-[linear-gradient(180deg,rgba(255,250,245,.98),rgba(255,255,255,.94),rgba(239,246,255,.86))] p-5 shadow-[0_22px_60px_rgba(15,23,42,0.08)]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $post->status === 'published' ? 'bg-[#ecfdf5] text-[#047857]' : ($post->status === 'pending' ? 'bg-[#eff6ff] text-[#1d4ed8]' : 'bg-[#f5f5f4] text-[#6b6256]') }}">
                                        {{ $post->status === 'published' ? '已发布' : ($post->status === 'pending' ? '待审核' : '草稿') }}
                                    </span>
                                    <span class="rounded-full bg-[#fff7ed] px-3 py-1 text-xs text-[#9a3412]">{{ $post->category?->name ?? '未分类' }}</span>
                                    <span class="rounded-full bg-[#f5f5f4] px-3 py-1 text-xs text-[#6b6256]">{{ $post->contentModel?->name ?? '未指定模型' }}</span>
                                </div>
                                <h2 class="mt-3 text-xl font-semibold text-[#181512]">
                                    <a href="{{ $postPrimaryUrl }}" class="transition hover:text-[#1d4ed8]">{{ $post->title }}</a>
                                </h2>
                                <p class="mt-2 text-sm leading-6 text-[#6b6256]">{{ $post->summary ?: '当前还没有摘要。' }}</p>
                                <div class="mt-3 text-xs text-[#78716c]">更新时间 {{ optional($post->updated_at)->format('Y-m-d H:i') }}</div>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                @if ($post->slug && $post->status === 'published')
                                    <a href="{{ route('posts.show', $post->slug) }}" class="inline-flex items-center justify-center rounded-full border border-[#e7d8c9] bg-white px-4 py-2 text-sm font-medium text-[#6b6256] transition hover:border-[#fdba74] hover:text-[#9a3412]">查看前台</a>
                                @endif
                                <a href="{{ route('member.posts.edit', $post) }}" class="inline-flex items-center justify-center rounded-full bg-[#1d4ed8] px-4 py-2 text-sm font-medium text-white shadow-[0_16px_30px_rgba(29,78,216,0.24)] transition hover:bg-[#1e40af]">继续编辑</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[30px] border border-dashed border-[#d6d3d1] bg-[#fffaf5] p-8 text-sm text-[#78716c]">
                        @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || (($filters['sort'] ?? 'latest') !== 'latest'))
                            当前筛选条件下没有找到稿件，可以调整关键词或状态后再试。
                        @else
                            你还没有任何稿件，先去发布第一篇内容。
                        @endif
                    </div>
                @endforelse
            </section>

            <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-[#78716c]">
                    第 {{ $posts->currentPage() }} / {{ $posts->lastPage() }} 页
                </p>
                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection
