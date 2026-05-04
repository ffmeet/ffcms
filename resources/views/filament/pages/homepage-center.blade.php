<x-filament-panels::page class="ecms-settings-form-page ecms-homepage-page">
    <form wire:submit="save" class="space-y-6">
        <div class="ecms-settings-page-intro">
            <p class="ecms-settings-eyebrow">Homepage</p>
            <h2>首页设置</h2>
            <p>这里按主题维护首页位置编号与分类来源。配置围绕 01、02、03 这类固定内容位展开，不依赖会变化的运营文案命名。</p>
        </div>

        {{ $this->form }}

        <div class="ecms-settings-page-actions">
            <x-filament::button type="submit">
                保存首页设置
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
