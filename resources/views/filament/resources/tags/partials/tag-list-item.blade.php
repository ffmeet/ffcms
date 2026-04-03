@php
    $editUrl = \App\Filament\Resources\Tags\TagResource::getUrl('edit', ['record' => $record]);
@endphp

<div class="ecms-tag-card">
    <div class="ecms-tag-card-main">
        <div class="ecms-tag-card-title-row">
            <span class="ecms-tag-card-hash">#</span>
            <h3 class="ecms-tag-card-title">{{ $record->name }}</h3>

            <label class="ecms-tag-card-select" aria-label="选择标签">
                <input type="checkbox" wire:model.live="selectedTagIds" value="{{ $record->id }}">
            </label>
        </div>
    </div>

    <div class="ecms-tag-card-block">
        <strong class="ecms-tag-card-block-value">{{ $record->slug }}</strong>
    </div>

    <div class="ecms-tag-card-block">
        <span class="ecms-tag-card-block-value">{{ $record->created_at?->format('Y-m-d H:i') }}</span>
    </div>

    <div class="ecms-tag-card-stat">
        <strong>{{ number_format((int) $record->count) }}</strong>
    </div>

    <div class="ecms-tag-card-actions">
        <details class="ecms-tag-card-menu">
            <summary class="ecms-tag-card-menu-trigger" aria-label="更多操作">
                <span></span>
                <span></span>
                <span></span>
            </summary>

            <div class="ecms-tag-card-menu-dropdown">
                <a href="{{ $editUrl }}" class="ecms-tag-card-menu-item">
                    编辑标签
                </a>
            </div>
        </details>
    </div>
</div>
