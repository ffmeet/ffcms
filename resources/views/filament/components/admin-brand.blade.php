<div
    x-data="{}"
    x-bind:style="$store.sidebar.isOpen ? '--ecms-brand-width: calc(var(--sidebar-width) - 5.45rem)' : '--ecms-brand-width: calc(var(--collapsed-sidebar-width) - 2.55rem)'"
    class="ecms-brand-shell"
>
    <span
        x-show="$store.sidebar.isOpen"
        x-cloak
        class="ecms-brand-full"
    >
        @if (filled($brandIcon))
            <img src="{{ $brandIcon }}" alt="{{ $brandName }}" class="ecms-brand-image">
        @else
            <span class="ecms-brand-mark">{{ $brandMark }}</span>
        @endif
        <span class="ecms-brand-copy">
            <span class="ecms-brand-title">{{ $brandName }}</span>
        </span>
    </span>

    <span
        x-show="! $store.sidebar.isOpen"
        x-cloak
        class="ecms-brand-compact"
    >
        @if (filled($brandIcon))
            <img src="{{ $brandIcon }}" alt="{{ $brandName }}" class="ecms-brand-image ecms-brand-image-compact">
        @else
            {{ $brandMark }}
        @endif
    </span>
</div>
