@php
    $record = $getRecord();
    $statusLabel = match ($record->status) {
        'draft' => '草稿',
        'pending' => '待审核',
        'published' => '已发布',
        default => (string) $record->status,
    };
    $statusClass = match ($record->status) {
        'draft' => 'is-draft',
        'pending' => 'is-pending',
        'published' => 'is-published',
        default => 'is-draft',
    };
    $time = $record->published_at ?? $record->created_at;
    $editUrl = \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $record]);
    $viewUrl = \App\Filament\Resources\Posts\PostResource::getUrl('view', ['record' => $record]);
    $previewUrl = filled($record->slug) ? $record->public_url : null;
@endphp

<div class="ecms-post-main-cell">
    <h3 class="ecms-post-main-title">{{ $record->title }}</h3>

    <p class="ecms-post-main-meta">
        <span class="ecms-post-main-author">By {{ $record->display_author }}</span>

        @if ($record->category?->name)
            <span class="ecms-post-main-sep">in</span>
            <span class="ecms-post-main-category-inline">{{ $record->category->name }}</span>
            <span class="ecms-post-main-sep">-</span>
        @endif

        @if ($time)
            <span class="ecms-post-main-time">{{ $time->format('Y-m-d H:i') }}</span>
        @endif
    </p>

    <div class="ecms-post-main-status-line">
        <span class="ecms-post-main-status {{ $statusClass }}">{{ $statusLabel }}</span>

        @if ($record->category?->name)
            <span class="ecms-post-main-category">{{ $record->category->name }}</span>
        @endif

        <div class="ecms-post-main-actions">
            @if ($previewUrl)
                <a href="{{ $previewUrl }}" target="_blank" rel="noreferrer" class="ecms-post-main-action">
                    查看前台
                </a>
            @endif

            <a href="{{ $viewUrl }}" class="ecms-post-main-action">
                查看详情
            </a>

            <a href="{{ $editUrl }}" class="ecms-post-main-action">
                编辑文章
            </a>
        </div>
    </div>
</div>
