<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\ContentModels\ContentModelResource;
use App\Filament\Resources\MemberGroups\MemberGroupResource;
use App\Models\ContentModel;
use App\Models\MemberGroup;
use App\Models\SiteSetting;
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

    public function getOverviewCards(): array
    {
        $settings = SiteSetting::current();
        $activeTheme = data_get($settings->business_settings, 'active_theme', 'default');

        return [
            [
                'label' => '当前主题',
                'value' => str($activeTheme)->headline()->toString(),
                'description' => '前台布局、认证页和内容页会优先从当前主题目录读取。',
            ],
            [
                'label' => '内容模型',
                'value' => (string) ContentModel::query()->count(),
                'description' => '文章、快讯等模型及其动态字段都从这里继续扩展。',
            ],
            [
                'label' => '会员组',
                'value' => (string) MemberGroup::query()->count(),
                'description' => '会员中心、活动和后台访问能力由会员组权限统一承接。',
            ],
        ];
    }
}
