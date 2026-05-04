<?php

namespace App\Filament\Resources\ContentModels\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\ContentModels\ContentModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentModels extends ListRecords
{
    use UsesSettingsShell;

    protected static string $resource = ContentModelResource::class;

    public function getTitle(): string
    {
        return '内容模型';
    }

    public function getSubheading(): ?string
    {
        return '在这里管理文章、快讯等内容模型，以及它们的扩展字段结构。';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新建内容模型'),
        ];
    }
}
