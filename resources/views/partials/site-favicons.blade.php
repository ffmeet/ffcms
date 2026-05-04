@php
    $faviconUrl = $settings?->faviconUrl();
    $appleTouchIconUrl = $settings?->appleTouchIconUrl();
@endphp

@if ($faviconUrl)
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">
@endif

@if ($appleTouchIconUrl)
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $appleTouchIconUrl }}">
@endif
