@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => $post->seo_title . ' - FFMeet'])

@section('content')
    <article class="space-y-8">
        <section class="overflow-hidden rounded-[32px] border border-stone-200/80 bg-white/90 shadow-sm">
            @if ($post->isFlashModel())
                <div class="p-6 sm:p-8 lg:p-10">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">快讯</div>
                        @if ($post->category)
                            <a href="{{ route('categories.show', $post->category->slug) }}" class="inline-flex rounded-full bg-stone-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-600 transition hover:bg-stone-200 hover:text-stone-900">
                                {{ $post->category->name }}
                            </a>
                        @else
                            <div class="inline-flex rounded-full bg-stone-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-600">
                                未分类
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
                        <div>
                            <h1 class="max-w-4xl text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl lg:text-[3.2rem]">
                                {{ $post->title }}
                            </h1>

                            <div class="mt-5 flex flex-wrap gap-3 text-sm text-stone-500">
                                <span class="rounded-full bg-stone-100 px-3 py-1">作者 {{ $post->display_author }}</span>
                                <span class="rounded-full bg-stone-100 px-3 py-1">发布时间 {{ optional($post->published_at)->format('m-d H:i') }}</span>
                                <span class="rounded-full bg-stone-100 px-3 py-1">浏览 {{ $post->statistics?->views ?? 0 }}</span>
                            </div>

                            @if ($post->summary)
                                <div class="mt-6 rounded-[28px] border border-stone-200 bg-[linear-gradient(135deg,rgba(255,247,237,.95),rgba(255,255,255,.95))] px-6 py-5 text-base leading-8 text-stone-700">
                                    {{ $post->summary }}
                                </div>
                            @endif
                        </div>

                        @if ($post->cover_image_url)
                            <div class="overflow-hidden rounded-[28px] border border-stone-200 bg-stone-100">
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-[240px] w-full object-cover sm:h-[320px]">
                            </div>
                        @endif
                    </div>

                    @if ($post->tags->isNotEmpty())
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('tags.show', $tag->slug) }}" class="rounded-full border border-stone-200 bg-white px-3 py-1 text-xs font-medium text-stone-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800"># {{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif

                    @if (filled($post->content))
                        <div class="site-prose mt-10 max-w-none">
                            {!! $post->renderContentForFrontend() !!}
                        </div>
                    @endif
                </div>
            @else
                @if ($post->cover_image_url)
                    <div class="border-b border-stone-200 bg-stone-100">
                        <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-[240px] w-full object-cover sm:h-[320px] lg:h-[420px]">
                    </div>
                @endif

                <div class="p-6 sm:p-8 lg:p-10">
                    @if ($post->category)
                        <a href="{{ route('categories.show', $post->category->slug) }}" class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100 hover:text-amber-800">
                            {{ $post->category->name }}
                        </a>
                    @else
                        <div class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">
                            未分类
                        </div>
                    @endif

                    <h1 class="mt-4 max-w-4xl text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl lg:text-5xl">
                        {{ $post->title }}
                    </h1>

                    <div class="mt-5 flex flex-wrap gap-3 text-sm text-stone-500">
                        <span class="rounded-full bg-stone-100 px-3 py-1">作者 {{ $post->display_author }}</span>
                        <span class="rounded-full bg-stone-100 px-3 py-1">发布时间 {{ optional($post->published_at)->format('Y-m-d H:i') }}</span>
                        <span class="rounded-full bg-stone-100 px-3 py-1">浏览 {{ $post->statistics?->views ?? 0 }}</span>
                    </div>

                    @if ($post->summary)
                        <div class="mt-6 rounded-[28px] border border-amber-100 bg-[linear-gradient(135deg,rgba(251,191,36,.16),rgba(255,255,255,.9))] px-6 py-5 text-sm leading-7 text-stone-700">
                            {{ $post->summary }}
                        </div>
                    @endif

                    @if ($post->tags->isNotEmpty())
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('tags.show', $tag->slug) }}" class="rounded-full border border-stone-200 bg-white px-3 py-1 text-xs font-medium text-stone-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800"># {{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif

                    <div class="site-prose mt-10 max-w-none">
                        {!! $post->renderContentForFrontend() !!}
                    </div>
                </div>
            @endif
        </section>

        <section class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <div class="rounded-[28px] border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">内容表现</h2>
                        <p class="mt-1 text-sm text-stone-500">把文章的基础热度放在正文后，阅读和运营都更顺手。</p>
                    </div>
                    <div class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Stats</div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[24px] border border-stone-200 bg-stone-50 px-5 py-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">浏览量</div>
                        <div class="mt-3 text-3xl font-semibold text-stone-900">{{ $post->statistics?->views ?? 0 }}</div>
                    </div>
                    <div class="rounded-[24px] border border-stone-200 bg-stone-50 px-5 py-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">点赞数</div>
                        <div class="mt-3 text-3xl font-semibold text-stone-900">{{ $post->statistics?->likes ?? 0 }}</div>
                    </div>
                    <div class="rounded-[24px] border border-stone-200 bg-stone-50 px-5 py-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">评论数</div>
                        <div class="mt-3 text-3xl font-semibold text-stone-900">{{ $post->statistics?->comments_count ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-[28px] border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">关联附件</h2>
                        <p class="mt-1 text-sm text-stone-500">资料下载、PDF 方案、补充文件统一放在文章内容之后。</p>
                    </div>
                    <div class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-stone-600">Files</div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @forelse ($attachments as $attachment)
                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noreferrer" class="group rounded-[24px] border border-stone-200 bg-[linear-gradient(180deg,#fafaf9_0%,#f5f5f4_100%)] px-5 py-4 transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-base font-semibold text-stone-900">{{ $attachment['name'] }}</div>
                                    <div class="mt-1 text-sm text-stone-500">{{ strtoupper($attachment['extension'] ?: pathinfo($attachment['name'], PATHINFO_EXTENSION)) }}</div>
                                </div>
                                <div class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700 transition group-hover:bg-amber-50">打开</div>
                            </div>
                            <div class="mt-4 text-xs text-stone-500">点击查看或下载附件内容</div>
                        </a>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-stone-200 bg-stone-50 px-5 py-8 text-sm text-stone-500 sm:col-span-2">
                            当前文章还没有关联附件。
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-[30px] border border-stone-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
            @php
                $activeReplyId = old('parent_id');
                $requestedReplyId = request()->integer('reply');
                $focusedCommentId = request()->integer('focus');
                $discussionCount = $commentThreads->count();
                $replyCount = $commentThreads->sum(fn ($comment) => (int) ($comment->reply_count ?? $comment->children->count()));
            @endphp

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Discussion</div>
                    <h2 class="mt-2 text-2xl font-semibold text-stone-900">评论区</h2>
                    <p class="mt-1 text-sm text-stone-500">把互动区放回文章正文之后，阅读、评论、继续浏览会自然很多。</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm text-stone-600">
                    <div class="rounded-full bg-stone-100 px-4 py-2">
                        已公开 {{ $discussionCount }} 个讨论主题
                    </div>
                    <div class="rounded-full bg-amber-50 px-4 py-2 text-amber-800">
                        已展示 {{ $replyCount }} 条回复
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-[28px] border border-stone-200 bg-[linear-gradient(180deg,#fafaf9_0%,#fff7ed_100%)] p-5 sm:p-6">
                @auth
                    <form method="POST" action="{{ route('posts.comments.store', $post->slug) }}" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-between gap-3">
                            <label for="body" class="text-sm font-medium text-stone-800">发表评论</label>
                            <div class="rounded-full bg-white px-3 py-1 text-xs text-stone-500">当前登录：{{ auth()->user()->public_display_name }}</div>
                        </div>
                        <textarea
                            id="body"
                            name="body"
                            rows="5"
                            class="w-full rounded-[24px] border border-stone-200 bg-white px-5 py-4 text-sm leading-7 text-stone-700 outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100"
                            placeholder="写下你的看法、补充或问题。提交后会进入审核。"
                        >{{ old('body') }}</textarea>
                        @if (blank($activeReplyId))
                            @error('body')
                                <div class="rounded-[20px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</div>
                            @enderror
                        @endif
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs leading-6 text-stone-500">评论提交后默认进入审核，公开展示前不会直接出现在列表里。</p>
                            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-stone-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-amber-600">提交评论</button>
                        </div>
                    </form>
                @else
                    <div class="flex flex-col gap-4 rounded-[24px] border border-dashed border-stone-200 bg-white px-5 py-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-base font-semibold text-stone-900">登录后参与讨论</div>
                            <p class="mt-1 text-sm text-stone-500">当前支持登录会员发表评论，后续我们会继续补回复和更完整的互动体验。</p>
                        </div>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-stone-900 px-5 py-3 text-sm font-medium text-white transition hover:border-amber-400 hover:bg-amber-600">登录后评论</a>
                    </div>
                @endauth
            </div>

            <div class="mt-8 space-y-4">
                @forelse ($commentThreads as $comment)
                    @include(\App\Support\SiteTheme::view('partials.comment-thread', 'themes.default.partials.comment-thread'), [
                        'comments' => collect([$comment]),
                        'post' => $post,
                        'activeReplyId' => $activeReplyId,
                        'requestedReplyId' => $requestedReplyId,
                        'focusedCommentId' => $focusedCommentId,
                        'maxReplyDepth' => $maxReplyDepth,
                        'depth' => 0,
                    ])
                @empty
                    <div class="rounded-[26px] border border-dashed border-stone-200 bg-stone-50 px-5 py-10 text-center text-sm text-stone-500">
                        当前还没有公开评论，欢迎成为第一位参与讨论的人。
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-[30px] border border-stone-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">More Stories</div>
                    <h2 class="mt-2 text-2xl font-semibold text-stone-900">同栏目内容</h2>
                    <p class="mt-1 text-sm text-stone-500">读完正文后，继续看同栏目内容会更自然，不需要把推荐内容一直挤在侧栏里。</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($relatedPosts as $relatedPost)
                    <a href="{{ route('posts.show', $relatedPost->slug) }}" class="group overflow-hidden rounded-[26px] border border-stone-200 bg-stone-50 transition hover:-translate-y-1 hover:border-amber-300 hover:bg-white hover:shadow-sm">
                        @if ($relatedPost->cover_image_url)
                            <div class="overflow-hidden border-b border-stone-200 bg-stone-100">
                                <img src="{{ $relatedPost->cover_image_url }}" alt="{{ $relatedPost->title }}" class="h-40 w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                            </div>
                        @endif
                        <div class="p-5">
                            <div class="text-base font-semibold text-stone-900">{{ $relatedPost->title }}</div>
                            @if ($relatedPost->summary)
                                <div class="mt-2 text-sm leading-6 text-stone-500">{{ $relatedPost->summary }}</div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="rounded-[26px] border border-dashed border-stone-200 bg-stone-50 px-5 py-10 text-sm text-stone-500 md:col-span-2 xl:col-span-4">
                        当前栏目暂时还没有更多文章。
                    </div>
                @endforelse
            </div>
        </section>
    </article>
@endsection
