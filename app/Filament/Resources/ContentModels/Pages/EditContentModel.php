<?php

namespace App\Filament\Resources\ContentModels\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\ContentModels\ContentModelResource;
use App\Support\ContentModelFieldManager;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContentModel extends EditRecord
{
    use UsesSettingsShell;

    protected static string $resource = ContentModelResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['field_config'] = ContentModelFieldManager::normalizeFieldConfig($data['field_config'] ?? []);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['field_config'] = ContentModelFieldManager::normalizeFieldConfig($data['field_config'] ?? []);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
