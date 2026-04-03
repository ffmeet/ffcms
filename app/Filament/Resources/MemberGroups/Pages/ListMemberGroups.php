<?php

namespace App\Filament\Resources\MemberGroups\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\MemberGroups\MemberGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberGroups extends ListRecords
{
    use UsesSettingsShell;

    protected static string $resource = MemberGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
