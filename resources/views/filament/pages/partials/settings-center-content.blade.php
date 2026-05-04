@php($sections = $sections ?? [])
@php($overviewCards = $overviewCards ?? [])
@php($modal = $modal ?? false)

@if (! $modal)
    <header class="ecms-settings-header">
        <div>
            <p class="ecms-settings-eyebrow">网站设置</p>
            <h1>设置中心</h1>
            <p>把低频配置统一收进一个独立设置空间。能在当页完成的尽量在当页完成，复杂模块再进入专属设置页。</p>
        </div>
    </header>

    <section class="ecms-settings-overview" aria-label="设置概览">
        @foreach ($overviewCards as $card)
            <article class="ecms-settings-overview-card">
                <p class="ecms-settings-overview-label">{{ $card['label'] }}</p>
                <strong class="ecms-settings-overview-value">{{ $card['value'] }}</strong>
                <p class="ecms-settings-overview-copy">{{ $card['description'] }}</p>
            </article>
        @endforeach
    </section>

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
@else
    <div class="ecms-settings-workbench">
        <aside class="ecms-settings-workbench-sidebar">
            <div class="ecms-settings-workbench-search">
                <input type="search" placeholder="搜索设置" aria-label="搜索设置">
            </div>

            <nav class="ecms-settings-workbench-nav" aria-label="设置导航">
                @foreach ($sections as $section)
                    <div class="ecms-settings-workbench-group">
                        <h3>{{ $section['heading'] }}</h3>
                        <div class="ecms-settings-workbench-links">
                            @foreach ($section['items'] as $item)
                                @php($itemAnchor = \Illuminate\Support\Str::slug($section['heading'] . '-' . $item['label']))
                                <a href="#{{ $itemAnchor }}" class="ecms-settings-workbench-link">
                                    @if (filled($item['icon'] ?? null))
                                        <x-dynamic-component :component="$item['icon']" class="ecms-settings-workbench-link-icon" />
                                    @endif
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        <div class="ecms-settings-workbench-main">
            <header class="ecms-settings-header">
                <div>
                    <p class="ecms-settings-eyebrow">网站设置</p>
                    <h1>设置工作台</h1>
                    <p>低频静态设置优先在当前弹层内完成；涉及会员等级、内容模型等复杂配置时，再进入独立页面。</p>
                </div>
            </header>

            <section class="ecms-settings-overview" aria-label="设置概览">
                @foreach ($overviewCards as $card)
                    <article class="ecms-settings-overview-card">
                        <p class="ecms-settings-overview-label">{{ $card['label'] }}</p>
                        <strong class="ecms-settings-overview-value">{{ $card['value'] }}</strong>
                        <p class="ecms-settings-overview-copy">{{ $card['description'] }}</p>
                    </article>
                @endforeach
            </section>

            <div class="ecms-settings-sections">
                @foreach ($sections as $section)
                    <section class="ecms-settings-section">
                        <div class="ecms-settings-section-head">
                            <h2>{{ $section['heading'] }}</h2>
                        </div>

                        <div class="ecms-settings-grid ecms-settings-grid-single">
                            @foreach ($section['items'] as $item)
                                @php($itemAnchor = \Illuminate\Support\Str::slug($section['heading'] . '-' . $item['label']))
                                <article id="{{ $itemAnchor }}" class="ecms-settings-card ecms-settings-card-expanded">
                                    <div class="ecms-settings-card-main">
                                        @if (filled($item['icon'] ?? null))
                                            <div class="ecms-settings-card-icon">
                                                <x-dynamic-component :component="$item['icon']" class="ecms-settings-card-icon-svg" />
                                            </div>
                                        @endif

                                        <h3>{{ $item['label'] }}</h3>
                                        <p>{{ $item['description'] }}</p>
                                    </div>

                                    <div class="ecms-settings-card-actions">
                                        @if (filled($item['url'] ?? null))
                                            <a href="{{ $item['url'] }}" class="ecms-settings-card-link">
                                                进入
                                            </a>
                                        @else
                                            <span class="ecms-settings-card-badge">规划中</span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endif
