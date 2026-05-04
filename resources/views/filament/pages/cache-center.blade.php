<x-filament-panels::page class="ecms-settings-form-page ecms-cache-page">
    <div class="space-y-6">
        <div class="ecms-settings-page-intro">
            <p class="ecms-settings-eyebrow">Cache</p>
            <h2>缓存中心</h2>
            <p>这里集中处理前台首页、主题数据流和 Laravel 运行缓存。平时不需要频繁操作，只有在你刚调整设置、模板或数据联调时再手动清理即可。</p>
        </div>

        <section class="fi-section">
            <div class="fi-section-header">
                <div>
                    <h3 class="fi-section-header-heading">当前缓存状态</h3>
                </div>
            </div>

            <div class="fi-section-content">
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">前台缓存版本</div>
                            <div class="mt-1 text-sm text-slate-500">每次点击清理缓存或前台核心内容变更时，这个版本号都会刷新。</div>
                        </div>
                        <div class="text-2xl font-black text-slate-900">{{ $this->frontendCacheVersion() }}</div>
                    </div>

                    @foreach ($this->getEntries() as $entry)
                        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4 last:border-b-0 last:pb-0">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $entry['label'] }}</div>
                                <div class="mt-1 text-sm leading-6 text-slate-500">{{ $entry['description'] }}</div>
                            </div>
                            <div class="shrink-0 text-sm font-medium text-slate-700">{{ $entry['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <form wire:submit="clearCaches" class="space-y-4">
            <section class="fi-section">
                <div class="fi-section-header">
                    <div>
                        <h3 class="fi-section-header-heading">手动清理</h3>
                    </div>
                </div>

                <div class="fi-section-content">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <p class="text-sm leading-6 text-slate-500">点击后会清理前台缓存版本、站点设置缓存，以及 Laravel 的运行缓存。适合在首页设置、主题模板或配置调整后立即执行。</p>

                        <x-filament::button type="submit">
                            一键清理缓存
                        </x-filament::button>
                    </div>
                </div>
            </section>
        </form>
    </div>
</x-filament-panels::page>
