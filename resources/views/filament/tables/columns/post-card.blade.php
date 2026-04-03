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

<div class="ecms-post-card">
    @if ($record->cover_image_url)
        <div class="ecms-post-card-cover">
            <img src="{{ $record->cover_image_url }}" alt="{{ $record->title }}">
        </div>
    @else
        <div class="ecms-post-card-cover is-empty"></div>
    @endif

    <div class="ecms-post-card-main">
        <div class="ecms-post-card-title-row">
            <h3 class="ecms-post-card-title">{{ $record->title }}</h3>

            <details class="ecms-post-card-menu">
                <summary class="ecms-post-card-menu-trigger" aria-label="更多操作">
                    <span></span>
                    <span></span>
                    <span></span>
                </summary>

                <div class="ecms-post-card-menu-dropdown">
                    @if ($previewUrl)
                        <a href="{{ $previewUrl }}" target="_blank" rel="noreferrer" class="ecms-post-card-menu-item">
                            查看预览
                        </a>
                    @endif

                    <a href="{{ $viewUrl }}" class="ecms-post-card-menu-item">
                        查看详情
                    </a>

                    <a href="{{ $editUrl }}" class="ecms-post-card-menu-item">
                        编辑文章
                    </a>
                </div>
            </details>
        </div>

        <p class="ecms-post-card-meta">
            <span class="ecms-post-card-author">By {{ $record->display_author }}</span>

            @if ($record->category?->name)
                <span class="ecms-post-card-meta-sep">in</span>
                <span class="ecms-post-card-meta-category">{{ $record->category->name }}</span>
                <span class="ecms-post-card-meta-sep">-</span>
            @endif

            @if ($time)
                <span class="ecms-post-card-time">{{ $time->format('Y-m-d H:i') }}</span>
            @endif
        </p>

        <div class="ecms-post-card-status-row">
            <span class="ecms-post-card-status {{ $statusClass }}">{{ $statusLabel }}</span>

            @if ($record->category?->name)
                <span class="ecms-post-card-category">{{ $record->category->name }}</span>
            @endif
        </div>
    </div>

    <div class="ecms-post-card-stats">
        <span><x-heroicon-o-eye class="ecms-post-card-stat-icon" />{{ number_format((int) ($record->statistics?->views ?? 0)) }}</span>
        <span><x-heroicon-o-chat-bubble-left-right class="ecms-post-card-stat-icon" />{{ number_format((int) ($record->statistics?->comments_count ?? 0)) }}</span>
        <span><x-heroicon-o-heart class="ecms-post-card-stat-icon" />{{ number_format((int) ($record->statistics?->likes ?? 0)) }}</span>
    </div>
</div>
