@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => $post->seo_title . ' - ' . ($siteSettings->site_name ?? '小芳侠')])

@php
    $shareUrl = urlencode(request()->fullUrl());
    $shareTitle = urlencode($post->title);
    $author = $post->user;
@endphp

@section('content')
    <article class="mx-auto max-w-[1060px] space-y-12 bg-white pb-12">
        <section class="border-b border-[#e7e5e4] pb-10">
            <div class="mx-auto max-w-[860px] text-center">
                <div class="flex flex-wrap items-center justify-center gap-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
                    @if ($post->category)
                        <a href="{{ route('categories.show', $post->category->slug) }}" class="transition hover:text-[#151515]">{{ $post->category->name }}</a>
                    @endif
                    @if ($post->isFlashModel())
                        <span class="border border-[#e5e7eb] bg-white px-3 py-1 text-[#151515]">Flash</span>
                    @endif
                </div>
                <h1 class="mt-5 font-serif text-4xl font-semibold leading-[1.08] text-[#151515] sm:text-5xl lg:text-[4.1rem]">
                    {{ $post->title }}
                </h1>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm text-[#8b8175]">
                    <span>By <a href="{{ route('authors.show', $author->username) }}" class="text-[#151515]">{{ $author->public_display_name }}</a></span>
                    <span>{{ optional($post->published_at)->format('Y.m.d') }}</span>
                    <span>{{ max(1, (int) ceil(str_word_count(strip_tags($post->content ?? $post->summary ?? '')) / 250)) }} min read</span>
                </div>
                @if ($post->summary)
                    <p class="mx-auto mt-6 max-w-[760px] text-lg leading-9 text-[#52525b]">{{ $post->summary }}</p>
                @endif
            </div>

            @if ($post->cover_image_url)
                <div class="mt-8 overflow-hidden bg-[#f5f5f4]">
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-[280px] w-full object-cover sm:h-[380px] lg:h-[520px]">
                </div>
            @endif
        </section>

        <div class="mx-auto max-w-[860px]">
            <div class="site-prose max-w-none text-[1.07rem] leading-[2.05]">
                {!! $post->renderContentForFrontend() !!}
            </div>

            @if ($post->tags->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2 border-t border-[#e7e5e4] pt-6">
                    @foreach ($post->tags as $tag)
                        <a href="{{ route('tags.show', $tag->slug) }}" class="border border-[#e5e7eb] bg-white px-3 py-1.5 text-xs font-medium uppercase tracking-[0.16em] text-[#5f574f] transition hover:border-[#151515] hover:text-[#151515]">
                            # {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($attachments->isNotEmpty())
            <section class="mx-auto max-w-[860px] border-t border-[#e7e5e4] pt-8">
                <div class="flex items-end justify-between gap-3">
                    <h2 class="font-serif text-3xl font-semibold text-[#151515]">附件资料</h2>
                    <span class="text-xs uppercase tracking-[0.22em] text-[#8b8175]">Attachments</span>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($attachments as $attachment)
                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noreferrer" class="block border border-[#e5e7eb] bg-white px-5 py-4 transition hover:-translate-y-0.5 hover:border-[#151515]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-serif text-xl font-semibold text-[#151515]">{{ $attachment['name'] }}</div>
                                    <div class="mt-2 text-xs uppercase tracking-[0.2em] text-[#8b8175]">{{ strtoupper($attachment['extension'] ?: pathinfo($attachment['name'], PATHINFO_EXTENSION)) }}</div>
                                </div>
                                <span class="text-sm text-[#151515]">Open</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mx-auto max-w-[860px] border-t border-[#e7e5e4] pt-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-serif text-3xl font-semibold text-[#151515]">Share this post</h2>
                    <p class="mt-2 text-sm leading-7 text-[#6b6256]">将这篇文章分享给更多正在关注同一主题的人。</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noreferrer" class="border border-[#e5e7eb] bg-white px-4 py-2 text-[#151515] transition hover:border-[#151515]">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noreferrer" class="border border-[#e5e7eb] bg-white px-4 py-2 text-[#151515] transition hover:border-[#151515]">X</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noreferrer" class="border border-[#e5e7eb] bg-white px-4 py-2 text-[#151515] transition hover:border-[#151515]">LinkedIn</a>
                    <a href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noreferrer" class="border border-[#e5e7eb] bg-white px-4 py-2 text-[#151515] transition hover:border-[#151515]">Telegram</a>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[860px] border-t border-[#e7e5e4] pt-8">
            <div class="grid gap-5 sm:grid-cols-[120px_minmax(0,1fr)]">
                <div>
                    @if ($author->avatarUrl('medium'))
                        <img src="{{ $author->avatarUrl('medium') }}" alt="{{ $author->public_display_name }}" class="h-[112px] w-[112px] rounded-full object-cover">
                    @else
                        <div class="flex h-[112px] w-[112px] items-center justify-center rounded-full bg-[#f3f4f6] font-serif text-4xl text-[#151515]">
                            {{ \Illuminate\Support\Str::substr($author->public_display_name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#8b8175]">Written by</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <h2 class="font-serif text-3xl font-semibold text-[#151515]">{{ $author->public_display_name }}</h2>
                        <span class="text-sm text-[#8b8175]">{{ $author->author_headline }}</span>
                    </div>
                    <p class="mt-3 max-w-2xl text-base leading-8 text-[#5f574f]">{{ $author->author_bio }}</p>
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                        <a href="{{ route('authors.show', $author->username) }}" class="font-medium text-[#151515] transition hover:text-[#5f574f]">进入作者页</a>
                        @if ($authorMorePosts->isNotEmpty())
                            <span class="text-[#8b8175]">更多文章：{{ $authorMorePosts->pluck('title')->take(2)->implode(' / ') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[860px] border-t border-[#e7e5e4] pt-8">
            @php
                $activeReplyId = old('parent_id');
                $requestedReplyId = request()->integer('reply');
                $focusedCommentId = request()->integer('focus');
                $discussionCount = $commentThreads->count();
                $replyCount = $commentThreads->sum(fn ($comment) => (int) ($comment->reply_count ?? $comment->children->count()));
            @endphp

            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#a8a29e]">Comments</p>
                    <h2 class="mt-2 font-serif text-2xl font-semibold text-[#1c1917]">会员讨论</h2>
                </div>
                <div class="flex flex-wrap gap-3 text-sm text-[#a8a29e]">
                    <span>{{ $discussionCount }} 条评论</span>
                    <span>{{ $replyCount }} 条回复</span>
                </div>
            </div>

            <div class="mt-6">
                @auth
                    <form method="POST" action="{{ route('posts.comments.store', $post->slug) }}" class="space-y-4">
                        @csrf
                        <div class="flex items-start gap-3">
                            @if (auth()->user()->avatarUrl('small'))
                                <img src="{{ auth()->user()->avatarUrl('small') }}" alt="{{ auth()->user()->public_display_name }}" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f3f4f6] text-sm font-semibold text-[#78716c]">
                                    {{ mb_substr(auth()->user()->public_display_name, 0, 1) }}
                                </div>
                            @endif
                            <div class="relative flex-1">
                                <textarea
                                    id="body"
                                    name="body"
                                    rows="4"
                                    class="w-full rounded-[14px] border border-[#e5e7eb] bg-white px-4 py-3 pb-10 text-sm leading-7 text-[#44403c] outline-none transition focus:border-[#a8a29e]"
                                    placeholder="加入讨论"
                                >{{ old('body') }}</textarea>
                                <button type="submit" class="absolute bottom-3 right-3 inline-flex items-center justify-center rounded-md border border-[#e5e7eb] bg-white px-3 py-1.5 text-xs font-medium text-[#57534e] transition hover:text-[#1c1917]">添加评论</button>
                            </div>
                        </div>
                        @if (blank($activeReplyId))
                            @error('body')
                                <div class="rounded-[10px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</div>
                            @enderror
                        @endif
                    </form>
                @else
                    <div class="flex flex-col gap-4 border border-dashed border-[#d6d3d1] bg-white px-5 py-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="font-serif text-2xl font-semibold text-[#151515]">登录后参与讨论</div>
                            <p class="mt-2 text-sm text-[#78716c]">登录后即可发表评论和回复。</p>
                        </div>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-[10px] bg-[#111111] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#232323]">登录后评论</a>
                    </div>
                @endauth
            </div>

            <div class="mt-8 space-y-6">
                @forelse ($commentThreads as $comment)
                    @include(\App\Support\SiteTheme::view('partials.comment-thread', 'themes.xiaofang.partials.comment-thread'), [
                        'comments' => collect([$comment]),
                        'post' => $post,
                        'activeReplyId' => $activeReplyId,
                        'requestedReplyId' => $requestedReplyId,
                        'focusedCommentId' => $focusedCommentId,
                        'maxReplyDepth' => $maxReplyDepth,
                        'depth' => 0,
                    ])
                @empty
                    <div class="rounded-[12px] border border-dashed border-[#d6d3d1] bg-white px-5 py-10 text-center text-sm text-[#78716c]">
                        当前还没有公开评论，欢迎成为第一位参与讨论的人。
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mx-auto max-w-[1060px] border-t border-[#e7e5e4] pt-8">
            <div class="mb-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#8b8175]">More Stories</p>
                <h2 class="mt-2 font-serif text-4xl font-semibold text-[#151515]">同栏目继续阅读</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($relatedPosts as $relatedPost)
                    <article class="border border-[#e5e7eb] bg-white">
                        @if ($relatedPost->cover_image_url)
                            <a href="{{ route('posts.show', $relatedPost->slug) }}" class="block overflow-hidden bg-[#f5f5f4]">
                                <img src="{{ $relatedPost->cover_image_url }}" alt="{{ $relatedPost->title }}" class="h-44 w-full object-cover">
                            </a>
                        @endif
                        <div class="px-5 py-5">
                            <h3 class="font-serif text-2xl font-semibold leading-8 text-[#151515]">
                                <a href="{{ route('posts.show', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>
                            </h3>
                            <p class="mt-3 text-sm leading-7 text-[#5f574f]">{{ $relatedPost->summary ?: '继续阅读同栏目内容。' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="border border-dashed border-[#d6d3d1] bg-white px-5 py-10 text-sm text-[#78716c] md:col-span-2 xl:col-span-4">
                        当前栏目暂时还没有更多文章。
                    </div>
                @endforelse
            </div>
        </section>
    </article>
@endsection
