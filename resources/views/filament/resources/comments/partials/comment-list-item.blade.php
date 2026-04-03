@php
    $userName = $record->user?->username ?? '匿名用户';
    $postTitle = $record->post?->title ?? '未关联文章';
    $statusLabel = match ($record->status) {
        'pending' => '待审核',
        'approved' => '已通过',
        'rejected' => '已驳回',
        default => (string) $record->status,
    };
    $statusClass = match ($record->status) {
        'pending' => 'is-pending',
        'approved' => 'is-approved',
        'rejected' => 'is-rejected',
        default => 'is-pending',
    };
    $avatar = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($userName, 0, 1));
    $editUrl = \App\Filament\Resources\Comments\CommentResource::getUrl('edit', ['record' => $record]);
    $postUrl = filled($record->post?->slug) ? $record->post->public_url : null;
    $userUrl = $record->user ? \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $record->user]) : null;
@endphp

<div class="ecms-comment-card">
    <span class="ecms-comment-card-avatar">{{ $avatar }}</span>

    <div class="ecms-comment-card-copy">
        <div class="ecms-comment-card-meta">
            <strong>{{ $userName }}</strong>
            <span class="ecms-comment-card-meta-dot">•</span>
            <span>{{ $record->created_at?->format('Y-m-d H:i') }}</span>
            <span class="ecms-comment-card-meta-dot">on</span>
            <span class="ecms-comment-card-meta-title">{{ $postTitle }}</span>
        </div>

        <div class="ecms-comment-card-body">
            {{ $record->content ?: $record->body }}
        </div>

        <div class="ecms-comment-card-footer">
            <label class="ecms-comment-card-select" aria-label="选择评论">
                <input type="checkbox" wire:model.live="selectedCommentIds" value="{{ $record->id }}">
            </label>

            <span class="ecms-comment-card-status {{ $statusClass }}">{{ $statusLabel }}</span>

            <div x-data="{ open: false }" class="ecms-comment-card-menu">
                <button type="button" class="ecms-comment-card-menu-trigger" @click.stop.prevent="open = ! open">
                    <x-heroicon-o-ellipsis-horizontal class="ecms-comment-card-menu-icon" />
                </button>

                <div x-cloak x-show="open" x-transition.origin.bottom.left @click.stop @click.outside="open = false" class="ecms-comment-card-menu-panel">
                    @if ($postUrl)
                        <a href="{{ $postUrl }}" target="_blank" rel="noreferrer" class="ecms-comment-card-menu-link">
                            查看前台文章
                        </a>
                    @endif

                    <a href="{{ $editUrl }}" class="ecms-comment-card-menu-link">
                        编辑评论
                    </a>

                    @if ($userUrl)
                        <a href="{{ $userUrl }}" class="ecms-comment-card-menu-link">
                            查看会员
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($record->post?->cover_image_url)
        <div class="ecms-comment-card-cover">
            <img src="{{ $record->post->cover_image_url }}" alt="{{ $postTitle }}">
        </div>
    @endif
</div>
