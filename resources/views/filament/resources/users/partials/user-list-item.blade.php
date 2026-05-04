@php
    $statusLabel = $record->status === 'active' ? '正常' : '停用';
    $statusClass = $record->status === 'active' ? 'is-active' : 'is-inactive';
    $editUrl = \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $record]);
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($record->username, 0, 1));
    $permissionSummary = collect($record->memberGroup?->enabledPermissionLabels() ?? [])->take(2)->implode(' · ');
@endphp

<div class="ecms-user-card">
    <div class="ecms-user-card-primary">
        <span class="ecms-user-card-avatar">{{ $initial }}</span>

        <div class="ecms-user-card-copy">
            <div class="ecms-user-card-title-row">
                <h3 class="ecms-user-card-title">{{ $record->username }}</h3>

                <label class="ecms-user-card-select" aria-label="选择会员">
                    <input type="checkbox" wire:model.live="selectedUserIds" value="{{ $record->id }}">
                </label>
            </div>

            <p class="ecms-user-card-meta">
                <span>{{ $record->email }}</span>
                <span class="ecms-user-card-meta-sep">·</span>
                <span>{{ $record->created_at?->format('Y-m-d H:i') }}</span>
            </p>
        </div>
    </div>

    <div class="ecms-user-card-block ecms-user-card-block-status">
        <span class="ecms-user-card-block-label">状态</span>
        <span class="ecms-user-card-status {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>

    <div class="ecms-user-card-block ecms-user-card-block-group">
        <span class="ecms-user-card-block-label">级别</span>
        <strong class="ecms-user-card-block-value">{{ $record->memberGroup?->name ?? '未分组' }}</strong>
        @if ($permissionSummary)
            <p class="mt-1 text-xs text-slate-400">{{ $permissionSummary }}</p>
        @endif
    </div>

    <div class="ecms-user-card-actions">
        <details class="ecms-user-card-menu">
            <summary class="ecms-user-card-menu-trigger" aria-label="更多操作">
                <span></span>
                <span></span>
                <span></span>
            </summary>

            <div class="ecms-user-card-menu-dropdown">
                <a href="{{ $editUrl }}" class="ecms-user-card-menu-item">
                    编辑会员
                </a>
            </div>
        </details>
    </div>

    <div class="ecms-user-card-stat">
        <span class="ecms-user-card-stat-label">文章</span>
        <strong>{{ number_format((int) $record->posts_count) }}</strong>
    </div>

    <div class="ecms-user-card-stat">
        <span class="ecms-user-card-stat-label">评论</span>
        <strong>{{ number_format((int) $record->comments_count) }}</strong>
    </div>

    <div class="ecms-user-card-stat">
        <span class="ecms-user-card-stat-label">订阅</span>
        <strong>{{ number_format((int) $record->active_subscriptions_count) }}</strong>
    </div>
</div>
