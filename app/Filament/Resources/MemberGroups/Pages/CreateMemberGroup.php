<?php

namespace App\Filament\Resources\MemberGroups\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\MemberGroups\MemberGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberGroup extends CreateRecord
{
    use UsesSettingsShell;

    protected static string $resource = MemberGroupResource::class;
}
