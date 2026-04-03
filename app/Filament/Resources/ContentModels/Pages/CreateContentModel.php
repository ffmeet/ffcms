<?php

namespace App\Filament\Resources\ContentModels\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\ContentModels\ContentModelResource;
use App\Support\ContentModelFieldManager;
use Filament\Resources\Pages\CreateRecord;

class CreateContentModel extends CreateRecord
{
    use UsesSettingsShell;

    protected static string $resource = ContentModelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['field_config'] = ContentModelFieldManager::normalizeFieldConfig($data['field_config'] ?? []);

        return $data;
    }
}
