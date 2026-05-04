<?php

namespace App\Livewire\Admin;

use App\Models\SiteSetting;
use App\Support\SettingsNavigation;
use App\Support\SiteTheme;
use App\Support\UploadLogReader;
use Livewire\Component;

class SettingsWorkbench extends Component
{
    public SiteSetting $record;

    public function mount(): void
    {
        $this->record = SiteSetting::current();
    }

    public function getWorkbenchCards(): array
    {
        $settings = $this->record;
        $businessSettings = $settings->business_settings ?? [];
        $themeCard = SiteTheme::themeCard((string) data_get($businessSettings, 'active_theme', 'default'));
        $uploadSummary = UploadLogReader::summary();

        return [
            [
                'id' => 'help-center',
                'title' => '帮助中心',
                'meta' => [
                    ['label' => '开发文档', 'value' => '支持在线浏览 Markdown 文档'],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\HelpCenter::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'site-brand',
                'title' => '站点品牌',
                'meta' => [
                    ['label' => '站点名称', 'value' => $settings->site_name ?: '未填写'],
                    ['label' => 'SEO', 'value' => $settings->seo_title ?: '未填写'],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\SiteBrandCenter::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'homepage-center',
                'title' => '首页设置',
                'meta' => [
                    ['label' => '当前主题', 'value' => $themeCard['label']],
                    ['label' => '内容位', 'value' => '支持主题独立首页分类位'],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\HomepageCenter::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'site-navigation',
                'title' => '导航菜单',
                'meta' => [
                    ['label' => '顶部', 'value' => $this->linkSummary($settings->primary_navigation ?? [])],
                    ['label' => '页脚', 'value' => $this->linkSummary($settings->footer_navigation ?? [])],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\NavigationMenuCenter::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'theme-workbench',
                'title' => '主题工作台',
                'meta' => [
                    ['label' => '当前选择', 'value' => $themeCard['label']],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\ThemeWorkbench::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'payment-center',
                'title' => '支付中心',
                'meta' => [
                    ['label' => '渠道', 'value' => $this->enabledChannelsSummary($businessSettings)],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\PaymentCenter::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'rewrite-rules',
                'title' => '路由',
                'meta' => [],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\RouteRuleCenter::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'cache-center',
                'title' => '缓存中心',
                'meta' => [
                    ['label' => '前台缓存版本', 'value' => (string) \App\Support\FrontendCache::version()],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\CacheCenter::getUrl(), 'label' => '进入'],
            ],
            [
                'id' => 'upload-diagnostics',
                'title' => '上传诊断',
                'meta' => [
                    ['label' => '最近失败', 'value' => $uploadSummary['latest_failed_at'] ?? '暂无'],
                ],
                'action' => ['type' => 'link', 'url' => \App\Filament\Pages\UploadDiagnosticsCenter::getUrl(), 'label' => '进入'],
            ],
        ];
    }

    public function getWorkbenchSections(): array
    {
        $cardsByTitle = collect($this->getWorkbenchCards())->keyBy('title');

        return collect(SettingsNavigation::sections())
            ->map(function (array $section) use ($cardsByTitle): array {
                $items = collect($section['items'] ?? [])
                    ->map(function (array $item) use ($cardsByTitle): ?array {
                        $card = $cardsByTitle->get($item['label'] ?? '');

                        if (! $card) {
                            return null;
                        }

                        return [
                            'label' => $item['label'],
                            'icon' => $item['icon'] ?? null,
                            'anchor' => $card['id'],
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'heading' => $section['heading'],
                    'items' => $items,
                ];
            })
            ->filter(fn (array $section): bool => $section['items'] !== [])
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.settings-workbench', [
            'workbenchCards' => $this->getWorkbenchCards(),
            'workbenchSections' => $this->getWorkbenchSections(),
        ]);
    }

    protected function linkSummary(array $items): string
    {
        $labels = collect($items)
            ->pluck('label')
            ->filter(fn ($value) => filled($value))
            ->take(2)
            ->implode(' / ');

        return $labels !== '' ? $labels : '暂未配置';
    }

    protected function enabledChannelsSummary(array $businessSettings): string
    {
        return collect([
            '微信' => data_get($businessSettings, 'wechat_enabled', true),
            '支付宝' => data_get($businessSettings, 'alipay_enabled', true),
            'PayPal' => data_get($businessSettings, 'paypal_enabled', true),
            'Stripe' => data_get($businessSettings, 'stripe_enabled', true),
        ])->filter()->keys()->implode(' / ') ?: '未启用';
    }
}
