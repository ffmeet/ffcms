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

    public function getTitle(): string
    {
        return '会员组';
    }

    public function getSubheading(): ?string
    {
        return '集中维护会员等级、成长值区间和核心访问权限，确保前台、活动与后台权限一致。';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新建会员组'),
        ];
    }
}
