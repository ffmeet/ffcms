<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['model_id'] = static::resolveModelId($data['parent_id'] ?? null, $data['model_id'] ?? null);
        $data['level'] = static::resolveLevel($data['parent_id'] ?? null);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected static function resolveLevel(?int $parentId): int
    {
        if (! $parentId) {
            return 0;
        }

        return (int) Category::query()->whereKey($parentId)->value('level') + 1;
    }

    protected static function resolveModelId(?int $parentId, ?int $modelId): ?int
    {
        if (! $parentId) {
            return $modelId;
        }

        return Category::query()->whereKey($parentId)->value('model_id') ?: $modelId;
    }
}
