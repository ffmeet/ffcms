@php
    $record = $getRecord();
@endphp

<div class="ecms-post-metrics-cell">
    <span><x-heroicon-o-eye class="ecms-post-metrics-icon" />{{ number_format((int) ($record->statistics?->views ?? 0)) }}</span>
    <span><x-heroicon-o-chat-bubble-left-right class="ecms-post-metrics-icon" />{{ number_format((int) ($record->statistics?->comments_count ?? 0)) }}</span>
    <span><x-heroicon-o-heart class="ecms-post-metrics-icon" />{{ number_format((int) ($record->statistics?->likes ?? 0)) }}</span>
</div>
