<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Support\FrontendCache;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class CacheCenter extends Page
{
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $title = '缓存中心';

    protected string $view = 'filament.pages.cache-center';

    public function getEntries(): array
    {
        return [
            [
                'label' => '站点设置缓存',
                'value' => 'SiteSetting::current()',
                'description' => '站点基础配置会走持久缓存，保存设置后自动失效。',
            ],
            [
                'label' => '首页结果缓存',
                'value' => '10 分钟',
                'description' => '首页位置位分类、数量上限和排序结果会走短时缓存；保存首页设置后也会自动刷新。',
            ],
            [
                'label' => '前台 Feed 缓存',
                'value' => '10 分钟',
                'description' => '小芳侠导航流、热门话题、活动和作者列表会走统一前台缓存版本。',
            ],
        ];
    }

    public function frontendCacheVersion(): int
    {
        return FrontendCache::version();
    }

    public function clearCaches(): void
    {
        FrontendCache::flushAll();
        Artisan::call('optimize:clear');

        Notification::make()
            ->title('缓存已清理')
            ->body('前台结果缓存、站点设置缓存以及 Laravel 运行缓存都已清空。')
            ->success()
            ->send();
    }
}
