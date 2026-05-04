@foreach ($comments as $comment)
    @php
        $commentDepth = $comment->depth_level ?? $depth ?? 0;
        $isReplyPanelOpen = (string) $activeReplyId === (string) $comment->id || (string) $requestedReplyId === (string) $comment->id;
        $isFocusedComment = (string) $focusedCommentId === (string) $comment->id;
        $isRepliesOpen = $comment->children->isNotEmpty() && (($comment->reply_count ?? 0) <= 2 || ($comment->has_active_path ?? false));
        $canReply = $commentDepth < ($maxReplyDepth - 1);
    @endphp

    <article
        id="comment-{{ $comment->id }}"
        x-data="{ repliesOpen: {{ $isRepliesOpen ? 'true' : 'false' }} }"
        class="scroll-mt-24 rounded-[26px] border {{ $isFocusedComment ? 'border-stone-900 bg-amber-50/80 shadow-[0_0_0_4px_rgba(251,191,36,0.18)]' : ($isReplyPanelOpen ? 'border-amber-300 bg-amber-50/60' : 'border-stone-200 bg-stone-50/90') }} p-5 shadow-sm"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                @if ($comment->user?->avatarUrl('small'))
                    <img src="{{ $comment->user->avatarUrl('small') }}" alt="{{ $comment->user?->public_display_name ?? '匿名用户' }}" class="h-11 w-11 rounded-2xl object-cover">
                @else
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-stone-800 to-stone-600 text-sm font-semibold text-white">
                        {{ mb_substr($comment->user?->public_display_name ?? '匿', 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="font-medium text-stone-900">{{ $comment->user?->public_display_name ?? '匿名用户' }}</div>
                        @if ($comment->user_id === $post->user_id)
                            <span class="inline-flex rounded-full bg-stone-900 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">楼主</span>
                        @endif
                        @if (($comment->reply_count ?? 0) > 0)
                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800">{{ $comment->reply_count }} 条回复</span>
                        @endif
                        @if ($commentDepth > 0)
                            <span class="inline-flex rounded-full bg-stone-200 px-2.5 py-0.5 text-[11px] font-semibold text-stone-600">第 {{ $commentDepth + 1 }} 层</span>
                        @endif
                    </div>
                    <div class="text-xs text-stone-500">{{ optional($comment->created_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if ($comment->children->isNotEmpty())
                    <button
                        type="button"
                        @click="repliesOpen = ! repliesOpen"
                        class="flex items-center rounded-md bg-white px-3 py-1 text-[11px] font-semibold tracking-[0.08em] text-stone-600 transition hover:bg-stone-900 hover:text-white"
                    >
                        {{ $comment->reply_count }} 条回复
                    </button>
                @endif
                @if ($isFocusedComment)
                    <span class="rounded-full bg-stone-900 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">已定位</span>
                @endif
                <a href="#comment-{{ $comment->id }}" class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-500 transition hover:bg-stone-900 hover:text-white">Thread</a>
            </div>
        </div>

        <div class="mt-4 rounded-[22px] bg-white px-4 py-4 text-sm leading-7 text-stone-700">
            @if ($comment->parent?->user)
                <span class="mr-2 inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">回复 {{ $comment->parent->user->public_display_name }}</span>
            @endif
            {{ $comment->content }}
        </div>

        @auth
            @if ($canReply)
                <details class="mt-4 group" @if($isReplyPanelOpen) open @endif>
                    <summary class="inline-flex cursor-pointer list-none items-center justify-center rounded-md border {{ $isReplyPanelOpen ? 'border-stone-900 bg-stone-900 text-white' : 'border-stone-200 bg-white text-stone-700' }} px-3 py-1.5 text-xs font-medium transition marker:hidden hover:border-amber-400 hover:text-amber-700">
                        {{ $isReplyPanelOpen ? '收起回复' : '回复' }}
                    </summary>

                    <form method="POST" action="{{ route('posts.comments.store', $post->slug) }}" class="mt-4 rounded-[22px] border {{ $isReplyPanelOpen ? 'border-amber-300 bg-white shadow-sm' : 'border-stone-200/80 bg-white' }} px-4 py-4">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                        <div class="rounded-[18px] bg-stone-50 px-4 py-3 text-xs leading-6 text-stone-500">
                            你将回复 <span class="font-semibold text-stone-700">{{ $comment->user?->public_display_name ?? '匿名用户' }}</span>，提交后会先进入审核。
                        </div>
                        <textarea
                            id="reply-{{ $comment->id }}"
                            name="body"
                            rows="3"
                            class="mt-3 w-full rounded-[18px] border border-stone-200 bg-stone-50 px-4 py-3 text-sm leading-7 text-stone-700 outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100"
                            placeholder="补充观点、追问细节，或继续往下回复。"
                        >{{ old('parent_id') == $comment->id ? old('body') : '' }}</textarea>
                        @if ((string) $activeReplyId === (string) $comment->id)
                            @error('body')
                                <div class="mt-3 rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</div>
                            @enderror
                        @endif
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <p class="text-xs leading-6 text-stone-500">当前最多支持 {{ $maxReplyDepth }} 层回复，超过层级后请直接回复当前楼层。</p>
                            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-stone-900 px-5 py-2.5 text-sm font-medium text-white transition hover:border-amber-400 hover:bg-amber-600">提交回复</button>
                        </div>
                    </form>
                </details>
            @endif
        @endauth

        @if ($comment->children->isNotEmpty())
            <div x-cloak x-show="repliesOpen" class="mt-4 space-y-3 border-l-2 border-amber-200 pl-4 sm:pl-5">
                @include(\App\Support\SiteTheme::view('partials.comment-thread', 'themes.default.partials.comment-thread'), [
                    'comments' => $comment->children,
                    'post' => $post,
                    'activeReplyId' => $activeReplyId,
                    'requestedReplyId' => $requestedReplyId,
                    'focusedCommentId' => $focusedCommentId,
                    'maxReplyDepth' => $maxReplyDepth,
                    'depth' => $commentDepth + 1,
                ])
            </div>
        @endif
    </article>
@endforeach
