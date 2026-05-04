<x-filament-panels::page class="ecms-settings-form-page">
    <form wire:submit="save" class="space-y-6">
        <div class="ecms-settings-page-intro">
            <p class="ecms-settings-eyebrow">Theme</p>
            <h2>主题工作台</h2>
            <p>默认主题和其他前台主题的 Hero、首页节奏、展示文案与区块开关，都从这里统一维护。</p>
        </div>

        {{ $this->form }}

        <div class="ecms-settings-page-actions">
            <x-filament::button type="submit">
                保存主题设置
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
