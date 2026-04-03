<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\ContentModels\ContentModelResource;
use App\Filament\Resources\MemberGroups\MemberGroupResource;
use App\Support\SettingsNavigation;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SettingsCenter extends Page
{
    use UsesSettingsShell;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = '设置';

    protected static ?string $title = '设置中心';

    protected static string|\UnitEnum|null $navigationGroup = '网站设置';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings-center';

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return [
            static::getRouteName(),
            ContentModelResource::getNavigationItemActiveRoutePattern(),
            MemberGroupResource::getNavigationItemActiveRoutePattern(),
        ];
    }

    public function getSettingSections(): array
    {
        return SettingsNavigation::sections();
    }
}
