@php
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
            <label class="ecms-post-card-select" aria-label="选择文章">
                <input type="checkbox" wire:model.live="selectedPostIds" value="{{ $record->id }}">
            </label>

            <span class="ecms-post-card-status {{ $statusClass }}">{{ $statusLabel }}</span>

            @if ($record->category?->name)
                <span class="ecms-post-card-category">{{ $record->category->name }}</span>
            @endif

            <a href="{{ $editUrl }}" class="ecms-post-card-edit-link" aria-label="编辑文章">
                <x-heroicon-o-pencil-square class="ecms-post-card-edit-icon" />
            </a>
        </div>
    </div>

    <div class="ecms-post-card-stats">
        <span><x-heroicon-o-eye class="ecms-post-card-stat-icon" />{{ number_format((int) ($record->statistics?->views ?? 0)) }}</span>
        <span><x-heroicon-o-chat-bubble-left-right class="ecms-post-card-stat-icon" />{{ number_format((int) ($record->statistics?->comments_count ?? 0)) }}</span>
        <span><x-heroicon-o-heart class="ecms-post-card-stat-icon" />{{ number_format((int) ($record->statistics?->likes ?? 0)) }}</span>
    </div>
</div>
