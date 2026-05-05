@php
    $livewire ??= null;
    $sections = \App\Support\SettingsNavigation::sections();
    $closeUrl = filament()->getHomeUrl() ?? url('/admin');
    $heading = method_exists($livewire, 'getHeading') && filled($livewire->getHeading())
        ? $livewire->getHeading()
        : (method_exists($livewire, 'getTitle') ? $livewire->getTitle() : '设置中心');
    $currentUrl = url()->current();
    $showWindowHeading = ! $livewire instanceof \App\Filament\Pages\SettingsCenter;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div class="ecms-settings-overlay">
        <div class="ecms-settings-window">
            <header class="ecms-settings-window-header">
                @if ($showWindowHeading)
                    <div>
                        <h1>{{ $heading }}</h1>
                    </div>
                @else
                    <div aria-hidden="true"></div>
                @endif

                <a href="{{ $closeUrl }}" class="ecms-settings-close" aria-label="关闭设置中心">
                    <x-heroicon-o-x-mark class="ecms-settings-close-icon" />
                </a>
            </header>

            <div class="ecms-settings-shell">
                <aside class="ecms-settings-sidebar">
                    <nav class="ecms-settings-nav">
                        @foreach ($sections as $section)
                            <div class="ecms-settings-nav-group">
                                <h3>{{ $section['heading'] }}</h3>

                                @foreach ($section['items'] as $item)
                                    @php($itemUrl = $item['url'] ?? '#')
                                    <a
                                        href="{{ $itemUrl }}"
                                        @class([
                                            'ecms-settings-nav-item',
                                            'is-linked' => filled($item['url'] ?? null),
                                            'is-active' => filled($item['url'] ?? null) && str_starts_with($currentUrl, rtrim($itemUrl, '/')),
                                        ])
                                    >
                                        @if (filled($item['icon'] ?? null))
                                            <x-dynamic-component :component="$item['icon']" class="ecms-settings-nav-item-icon" />
                                        @endif

                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </nav>
                </aside>

                <section class="ecms-settings-content">
                    {{ $slot }}
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::layout.base>
