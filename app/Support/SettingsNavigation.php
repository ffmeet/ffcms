<?php

namespace App\Support;

use App\Filament\Pages\PaymentCenter;
use App\Filament\Pages\HelpCenter;
use App\Filament\Pages\HomepageCenter;
use App\Filament\Pages\NavigationMenuCenter;
use App\Filament\Pages\RouteRuleCenter;
use App\Filament\Pages\CacheCenter;
use App\Filament\Pages\SiteBrandCenter;
use App\Filament\Pages\SettingsCenter;
use App\Filament\Pages\ThemeWorkbench;
use App\Filament\Pages\UploadDiagnosticsCenter;
use App\Filament\Resources\ContentModels\ContentModelResource;
use App\Filament\Resources\MemberGroups\MemberGroupResource;
use App\Filament\Resources\Payments\PaymentResource;

class SettingsNavigation
{
    public static function sections(): array
    {
        return [
            [
                'heading' => '常规',
                'items' => [
                    ['label' => '设置总览', 'icon' => 'heroicon-o-cog-6-tooth', 'description' => null, 'url' => SettingsCenter::getUrl()],
                    ['label' => '帮助中心', 'icon' => 'heroicon-o-question-mark-circle', 'description' => null, 'url' => HelpCenter::getUrl()],
                    ['label' => '站点品牌', 'icon' => 'heroicon-o-identification', 'description' => null, 'url' => SiteBrandCenter::getUrl()],
                    ['label' => '路由', 'icon' => 'heroicon-o-link', 'description' => null, 'url' => RouteRuleCenter::getUrl()],
                    ['label' => '缓存中心', 'icon' => 'heroicon-o-bolt', 'description' => null, 'url' => CacheCenter::getUrl()],
                    ['label' => '上传诊断', 'icon' => 'heroicon-o-cloud-arrow-up', 'description' => null, 'url' => UploadDiagnosticsCenter::getUrl()],
                ],
            ],
            [
                'heading' => '外观与主题',
                'items' => [
                    ['label' => '首页设置', 'icon' => 'heroicon-o-home', 'description' => null, 'url' => HomepageCenter::getUrl()],
                    ['label' => '导航菜单', 'icon' => 'heroicon-o-bars-3-bottom-left', 'description' => null, 'url' => NavigationMenuCenter::getUrl()],
                    ['label' => '主题工作台', 'icon' => 'heroicon-o-swatch', 'description' => null, 'url' => ThemeWorkbench::getUrl()],
                ],
            ],
            [
                'heading' => '交易与支付',
                'items' => [
                    ['label' => '支付中心', 'icon' => 'heroicon-o-credit-card', 'description' => null, 'url' => PaymentCenter::getUrl()],
                    ['label' => '支付记录', 'icon' => 'heroicon-o-receipt-percent', 'description' => null, 'url' => PaymentResource::getUrl()],
                ],
            ],
            [
                'heading' => '结构与会员',
                'items' => [
                    ['label' => '内容模型', 'icon' => 'heroicon-o-circle-stack', 'description' => null, 'url' => ContentModelResource::getUrl()],
                    ['label' => '会员组', 'icon' => 'heroicon-o-user-group', 'description' => null, 'url' => MemberGroupResource::getUrl()],
                ],
            ],
        ];
    }
}
