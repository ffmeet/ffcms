<x-filament-panels::page class="ecms-settings-form-page">
    <form wire:submit="save" class="space-y-6 ecms-route-rule-page">
        {{ $this->form }}

        <div class="ecms-settings-page-actions">
            <x-filament::button type="submit">
                保存入口规则
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
