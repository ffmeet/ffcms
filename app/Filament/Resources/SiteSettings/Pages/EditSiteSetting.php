<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Support\SiteIconManager;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    use UsesSettingsShell;

    protected static string $resource = SiteSettingResource::class;

    protected ?string $heading = '站点品牌';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('返回设置中心')
                ->url(\App\Filament\Pages\SettingsCenter::getUrl())
                ->color('gray'),
        ];
    }

    protected function afterSave(): void
    {
        app(SiteIconManager::class)->regenerate($this->record);
    }
}
