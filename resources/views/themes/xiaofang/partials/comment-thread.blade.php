@foreach ($comments as $comment)
    @php
        $commentDepth = $comment->depth_level ?? $depth ?? 0;
        $isReplyPanelOpen = (string) $activeReplyId === (string) $comment->id || (string) $requestedReplyId === (string) $comment->id;
        $isRepliesOpen = $comment->children->isNotEmpty() && (($comment->reply_count ?? 0) <= 2 || ($comment->has_active_path ?? false));
        $canReply = $commentDepth < ($maxReplyDepth - 1);
    @endphp

    <article
        id="comment-{{ $comment->id }}"
        x-data="{ repliesOpen: {{ $isRepliesOpen ? 'true' : 'false' }} }"
        class="scroll-mt-24 border-b border-[#e7e5e4] pb-6"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                @if ($comment->user?->avatarUrl('small'))
                    <img src="{{ $comment->user->avatarUrl('small') }}" alt="{{ $comment->user?->public_display_name ?? '匿名用户' }}" class="h-9 w-9 rounded-full object-cover">
                @else
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#f3f4f6] text-xs font-semibold text-[#78716c]">
                        {{ mb_substr($comment->user?->public_display_name ?? '匿', 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <div class="font-medium text-[#1c1917]">{{ $comment->user?->public_display_name ?? '匿名用户' }}</div>
                        @if ($comment->user_id === $post->user_id)
                            <span class="text-xs text-[#a8a29e]">作者</span>
                        @endif
                        <span class="text-xs text-[#a8a29e]">· {{ optional($comment->created_at)->format('M j') }}</span>
                    </div>
                    <div class="mt-2 text-sm leading-7 text-[#44403c]">
                        @if ($comment->parent?->user)
                            <span class="mr-2 text-xs text-[#a8a29e]">回复 {{ $comment->parent->user->public_display_name }}</span>
                        @endif
                        {{ $comment->content }}
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-[#a8a29e]">
                        @if ($comment->children->isNotEmpty())
                            <button
                                type="button"
                                @click="repliesOpen = ! repliesOpen"
                                class="transition hover:text-[#57534e]"
                            >
                                {{ $comment->reply_count }} 条回复
                            </button>
                        @endif
                        @if ($canReply)
                            <details class="w-full">
                                <summary class="inline-flex cursor-pointer list-none items-center gap-1 text-xs text-[#a8a29e] transition hover:text-[#57534e]">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-3.5 w-3.5 fill-none stroke-current">
                                        <path d="M9 7H6a3 3 0 0 0-3 3v4a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M13 7l-3-3m3 3-3 3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    回复
                                </summary>
                                <form method="POST" action="{{ route('posts.comments.store', $post->slug) }}" class="mt-3 rounded-[12px] border border-[#e5e7eb] bg-[#fcfcfb] px-4 py-4">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea
                                        id="reply-{{ $comment->id }}"
                                        name="body"
                                        rows="3"
                                        class="w-full rounded-[10px] border border-[#e5e7eb] bg-white px-3 py-2 text-sm leading-7 text-[#44403c] outline-none transition focus:border-[#a8a29e]"
                                        placeholder="回复评论"
                                    >{{ old('parent_id') == $comment->id ? old('body') : '' }}</textarea>
                                    @if ((string) $activeReplyId === (string) $comment->id)
                                        @error('body')
                                            <div class="mt-3 rounded-[10px] border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</div>
                                        @enderror
                                    @endif
                                    <div class="mt-2 flex items-center justify-end gap-2 text-xs text-[#a8a29e]">
                                        <button type="submit" class="text-sm font-medium text-[#57534e] transition hover:text-[#1c1917]">回复</button>
                                        <button type="button" class="text-xs text-[#a8a29e]" @click="$el.closest('details')?.removeAttribute('open')">取消</button>
                                    </div>
                                </form>
                            </details>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($comment->children->isNotEmpty())
            <div x-cloak x-show="repliesOpen" class="mt-4 space-y-5 border-l border-[#e5e7eb] pl-4 sm:pl-5">
                @include(\App\Support\SiteTheme::view('partials.comment-thread', 'themes.xiaofang.partials.comment-thread'), [
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
