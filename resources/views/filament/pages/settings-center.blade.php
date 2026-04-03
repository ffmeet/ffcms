<x-filament-panels::page class="ecms-settings-home-page">
    @php($sections = $this->getSettingSections())

    <header class="ecms-settings-header">
        <div>
            <p class="ecms-settings-eyebrow">网站设置</p>
            <h1>设置中心</h1>
            <p>把低频配置统一收进一个独立设置空间。能在当页完成的尽量在当页完成，复杂模块再进入专属设置页。</p>
        </div>
    </header>

    <div class="ecms-settings-sections">
        @foreach ($sections as $section)
            <section class="ecms-settings-section">
                <div class="ecms-settings-section-head">
                    <h2>{{ $section['heading'] }}</h2>
                </div>

                <div class="ecms-settings-grid">
                    @foreach ($section['items'] as $item)
                        <article
                            id="{{ \Illuminate\Support\Str::slug($section['heading'] . '-' . $item['label']) }}"
                            class="ecms-settings-card"
                        >
                            <div class="ecms-settings-card-main">
                                @if (filled($item['icon'] ?? null))
                                    <div class="ecms-settings-card-icon">
                                        <x-dynamic-component :component="$item['icon']" class="ecms-settings-card-icon-svg" />
                                    </div>
                                @endif

                                <h3>{{ $item['label'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </div>

                            @if (filled($item['url'] ?? null))
                                <a href="{{ $item['url'] }}" class="ecms-settings-card-link">
                                    进入
                                </a>
                            @else
                                <span class="ecms-settings-card-badge">规划中</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
