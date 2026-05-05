<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSiteSettings extends ListRecords
{
    protected static string $resource = SiteSettingResource::class;

    public function mount(): void
    {
        parent::mount();

        $record = SiteSetting::current();

        $this->redirect(SiteSettingResource::getUrl('edit', ['record' => $record]));
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
