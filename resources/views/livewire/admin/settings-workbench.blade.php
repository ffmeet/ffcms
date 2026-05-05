<div class="ecms-settings-workbench">
    <aside class="ecms-settings-workbench-sidebar">
        <div class="ecms-settings-workbench-search">
            <input type="search" placeholder="搜索设置" aria-label="搜索设置">
        </div>

        <nav class="ecms-settings-workbench-nav" aria-label="设置导航">
            @foreach (($workbenchSections ?? []) as $section)
                <div class="ecms-settings-workbench-group">
                    <h3>{{ $section['heading'] }}</h3>
                    <div class="ecms-settings-workbench-links">
                        @foreach (($section['items'] ?? []) as $item)
                            <a href="#{{ $item['anchor'] }}" class="ecms-settings-workbench-link">
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
        <header class="ecms-settings-home-header">
            <h1>设置中心</h1>
        </header>

        <div class="ecms-settings-simple-grid">
            @foreach ($workbenchCards as $card)
                <section
                    class="ecms-settings-simple-section ecms-settings-simple-card"
                    id="{{ $card['id'] }}"
                >
                    <div class="ecms-settings-simple-head">
                        <div class="ecms-settings-simple-title">
                            <h2>{{ $card['title'] }}</h2>
                        </div>

                        @if (($card['action']['type'] ?? null) === 'link')
                            <a href="{{ $card['action']['url'] }}" class="ecms-settings-simple-link">{{ $card['action']['label'] ?? '进入' }}</a>
                        @else
                            <span class="ecms-settings-simple-badge">当前只读</span>
                        @endif
                    </div>

                    @if (! empty($card['meta'] ?? []))
                        <dl class="ecms-settings-simple-meta">
                            @foreach (($card['meta'] ?? []) as $meta)
                                <div class="ecms-settings-simple-meta-item">
                                    @if (filled($meta['label'] ?? null))
                                        <dt>{{ $meta['label'] }}</dt>
                                    @endif
                                    <dd title="{{ $meta['value'] }}">{{ $meta['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</div>
