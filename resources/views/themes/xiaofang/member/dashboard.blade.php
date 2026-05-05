@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '会员中心'])

@section('content')
    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'themes.xiaofang.member.partials.topbar'))

    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'themes.xiaofang.member.partials.nav'))

        <div class="space-y-6">
            @php($metrics = $memberMetrics ?? [])
            <section class="border border-[#e5e7eb] bg-white p-6">
                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#6b7280]">Member Center</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-[#181512]">会员中心</h1>
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
                        <div class="border border-[#e5e7eb] bg-white px-4 py-3 text-center">
                            <div class="text-sm text-[#78716c]">草稿</div>
                            <div class="mt-1 text-2xl font-black text-[#181512]">{{ $metrics['drafts'] ?? 0 }}</div>
                        </div>
                        <div class="border border-[#e5e7eb] bg-white px-4 py-3 text-center">
                            <div class="text-sm text-[#78716c]">待审</div>
                            <div class="mt-1 text-2xl font-black text-[#181512]">{{ $metrics['pending_posts'] ?? 0 }}</div>
                        </div>
                        <div class="border border-[#e5e7eb] bg-white px-4 py-3 text-center">
                            <div class="text-sm text-[#78716c]">已发布</div>
                            <div class="mt-1 text-2xl font-black text-[#1d4ed8]">{{ $metrics['published_posts'] ?? 0 }}</div>
                        </div>
                        <div class="border border-[#e5e7eb] bg-white px-4 py-3 text-center">
                            <div class="text-sm text-[#78716c]">评论</div>
                            <div class="mt-1 text-2xl font-black text-[#c2410c]">{{ $metrics['comments'] ?? 0 }}</div>
                        </div>
                        <div class="border border-[#e5e7eb] bg-white px-4 py-3 text-center">
                            <div class="text-sm text-[#78716c]">订单</div>
                            <div class="mt-1 text-2xl font-black text-[#181512]">{{ $metrics['orders'] ?? 0 }}</div>
                        </div>
                        <div class="border border-[#e5e7eb] bg-white px-4 py-3 text-center">
                            <div class="text-sm text-[#78716c]">订阅</div>
                            <div class="mt-1 text-2xl font-black text-[#181512]">{{ $metrics['subscriptions'] ?? 0 }}</div>
                        </div>
                        <div class="border border-[#e5e7eb] bg-white px-4 py-3 text-center">
                            <div class="text-sm text-[#78716c]">报名</div>
                            <div class="mt-1 text-2xl font-black text-[#181512]">{{ $metrics['registrations'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-2">
                @foreach (($attentionCards ?? []) as $card)
                    <article class="border border-[#e5e7eb] bg-white p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-black text-[#181512]">{{ $card['title'] }}</h2>
                            <span class="inline-flex border border-[#e5e7eb] px-3 py-1 text-xs font-semibold {{ ($card['tone'] ?? 'healthy') === 'warning' ? 'text-[#c2410c]' : 'text-[#1d4ed8]' }}">
                                {{ ($card['tone'] ?? 'healthy') === 'warning' ? '待处理' : '正常' }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-[#5f574f]">{{ $card['summary'] }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (($card['actions'] ?? []) as $action)
                                <a href="{{ $action['url'] }}" class="inline-flex border border-[#e5e7eb] bg-white px-4 py-2 text-xs font-semibold text-[#6b6256] transition hover:border-[#151515] hover:text-[#151515]">
                                    {{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-5 md:grid-cols-2">
                <section class="border border-[#e5e7eb] bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black text-[#181512]">我的稿件</h2>
                        <a href="{{ route('member.posts.index') }}" class="text-sm font-medium text-[#1d4ed8] transition hover:text-[#1e40af]">全部稿件</a>
                    </div>

                    <div class="mt-5 border-t border-[#e5e7eb]">
                        @forelse ($recentPosts as $post)
                            @php($postPrimaryUrl = $post->slug && $post->status === 'published' ? route('posts.show', $post->slug) : route('member.posts.edit', $post))
                            <article class="border-b border-[#e5e7eb] py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-bold text-[#181512]">
                                            <a href="{{ $postPrimaryUrl }}" class="transition hover:text-[#1d4ed8]">
                                                {{ $post->title }}
                                            </a>
                                        </h3>
                                        <p class="mt-1 text-xs text-[#a8a29e]">{{ optional($post->updated_at)->format('Y-m-d H:i') }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="text-xs font-semibold {{ $post->status === 'published' ? 'text-[#1d4ed8]' : 'text-[#6b6256]' }}">
                                            {{ $post->status === 'published' ? '已发布' : ($post->status === 'pending' ? '待审' : '草稿') }}
                                        </div>
                                        <a href="{{ $postPrimaryUrl }}" class="mt-2 inline-flex text-xs font-medium text-[#78716c] transition hover:text-[#151515]">
                                            {{ $post->status === 'published' ? '查看文章' : '继续编辑' }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="py-6 text-sm text-[#78716c]">你还没有稿件。</div>
                        @endforelse
                    </div>
                </section>

                <section class="border border-[#e5e7eb] bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black text-[#181512]">我的评论</h2>
                        <a href="{{ route('member.comments.index') }}" class="text-sm font-medium text-[#1d4ed8] transition hover:text-[#1e40af]">全部评论</a>
                    </div>

                    <div class="mt-5 border-t border-[#e5e7eb]">
                        @forelse ($recentComments as $comment)
                            @php($commentSummaryUrl = route('member.comments.index').'#comment-'.$comment->id)
                            @php($commentPostUrl = $comment->post?->slug ? route('posts.show', ['slug' => $comment->post->slug, 'focus' => $comment->id]).'#comment-'.$comment->id : null)
                            <article class="border-b border-[#e5e7eb] py-4">
                                <p class="text-sm leading-6 text-[#5f574f]">
                                    <a href="{{ $commentSummaryUrl }}" class="transition hover:text-[#151515]">
                                        “{{ $comment->content }}”
                                    </a>
                                </p>
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <p class="text-xs text-[#78716c]">
                                        发表在
                                        @if ($commentPostUrl)
                                            <a href="{{ $commentPostUrl }}" class="font-medium text-[#181512] transition hover:text-[#1d4ed8]">
                                                {{ $comment->post?->title ?? '关联文章已不可用' }}
                                            </a>
                                        @else
                                            <span class="font-medium text-[#181512]">{{ $comment->post?->title ?? '关联文章已不可用' }}</span>
                                        @endif
                                        @if ($comment->created_at)
                                            <span class="text-[#a8a29e]"> · {{ $comment->created_at->format('n月j日') }}</span>
                                        @endif
                                    </p>
                                    <div class="shrink-0 text-right">
                                        @if ($comment->status !== 'approved')
                                            <div class="text-xs font-semibold text-[#6b6256]">
                                                {{ $comment->status === 'pending' ? '待审' : '未审' }}
                                            </div>
                                        @endif
                                        <a href="{{ $commentSummaryUrl }}" class="mt-2 inline-flex text-xs font-medium text-[#78716c] transition hover:text-[#151515]">
                                            查看摘要
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="py-6 text-sm text-[#78716c]">你还没有评论记录。</div>
                        @endforelse
                    </div>
                </section>
            </section>
        </div>
    </div>
@endsection
