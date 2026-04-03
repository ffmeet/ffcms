@php
    $record = $getRecord();
@endphp

<div class="ecms-post-cover-cell">
    @if ($record->cover_image_url)
        <img src="{{ $record->cover_image_url }}" alt="{{ $record->title }}" class="ecms-post-cover-image">
    @else
        <div class="ecms-post-cover-image is-empty"></div>
    @endif
</div>
