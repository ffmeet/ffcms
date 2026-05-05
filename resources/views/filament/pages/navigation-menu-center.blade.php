<x-filament-panels::page class="ecms-settings-form-page">
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="ecms-settings-page-actions">
            <x-filament::button type="submit">
                保存
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
