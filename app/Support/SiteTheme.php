<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class SiteTheme
{
    public const PREVIEW_SESSION_KEY = 'site_theme_preview';

    public const THEMES = [
        'default' => [
            'label' => 'Default / 当前主题',
            'description' => '当前主力前台主题，已覆盖首页、分类、标签、搜索、详情、商店、活动、认证页和会员中心。',
            'preview_url' => '/',
        ],
        'editorial' => [
            'label' => 'Editorial / 编辑刊物风',
            'description' => '偏编辑刊物感的示例主题，适合做二次开发起点，目前重点覆盖整体壳、头部和页脚。',
            'preview_url' => '/',
        ],
        'xiaofang' => [
            'label' => 'Xiaofang / 小芳侠',
            'description' => '面向小芳侠主题的第二套前台骨架，先提供独立外壳与导航风格，内容页继续按默认主题回退。',
            'preview_url' => '/',
        ],
    ];

    public const HOMEPAGE_SETTING_BLUEPRINTS = [
        'default' => [
            [
                'slot' => '01',
                'key' => 'slot_01',
                'label' => '01 号位',
                'description' => '首屏主头条内容位。',
                'default_limit' => 2,
            ],
            [
                'slot' => '02',
                'key' => 'slot_02',
                'label' => '02 号位',
                'description' => '首屏辅栏内容位。',
                'default_limit' => 6,
            ],
            [
                'slot' => '03',
                'key' => 'slot_03',
                'label' => '03 号位',
                'description' => '继续阅读内容位。',
                'default_limit' => 4,
            ],
        ],
        'editorial' => [
            [
                'slot' => '01',
                'key' => 'slot_01',
                'label' => '01 号位',
                'description' => '封面主稿内容位。',
                'default_limit' => 2,
            ],
            [
                'slot' => '02',
                'key' => 'slot_02',
                'label' => '02 号位',
                'description' => '封面条带内容位。',
                'default_limit' => 4,
            ],
            [
                'slot' => '03',
                'key' => 'slot_03',
                'label' => '03 号位',
                'description' => '继续阅读内容位。',
                'default_limit' => 4,
            ],
        ],
        'xiaofang' => [
            [
                'slot' => '01',
                'key' => 'slot_01',
                'label' => '01 号位',
                'description' => '第一屏左栏：头条 + 下方简讯。',
                'default_limit' => 2,
            ],
            [
                'slot' => '02',
                'key' => 'slot_02',
                'label' => '02 号位',
                'description' => '第一屏中栏：两条精选内容。',
                'default_limit' => 2,
            ],
            [
                'slot' => '03',
                'key' => 'slot_03',
                'label' => '03 号位',
                'description' => '第一屏右栏：最新文章列表。',
                'default_limit' => 7,
            ],
            [
                'slot' => '04',
                'key' => 'slot_04',
                'label' => '04 号位',
                'description' => '第二屏左栏：文化卡片主区。',
                'default_limit' => 4,
            ],
            [
                'slot' => '05',
                'key' => 'slot_05',
                'label' => '05 号位',
                'description' => '第二屏右栏：订阅入口 + 活动列表。',
                'default_limit' => 0,
                'configurable' => false,
            ],
            [
                'slot' => '06',
                'key' => 'slot_06',
                'label' => '06 号位',
                'description' => '第三屏：创作灵感区。',
                'default_limit' => 4,
            ],
            [
                'slot' => '07',
                'key' => 'slot_07',
                'label' => '07 号位',
                'description' => '第四屏：继续阅读区。',
                'default_limit' => 4,
            ],
        ],
    ];

    public static function current(): string
    {
        if ($previewTheme = static::previewTheme()) {
            return $previewTheme;
        }

        if (! Schema::hasTable('site_settings')) {
            return 'default';
        }

        $settings = SiteSetting::current();
        $theme = data_get($settings->business_settings, 'active_theme', 'default');

        $theme = filled($theme) ? (string) $theme : 'default';

        return array_key_exists($theme, self::THEMES) ? $theme : 'default';
    }

    public static function options(): array
    {
        return collect(self::THEMES)
            ->mapWithKeys(fn (array $theme, string $key): array => [$key => $theme['label']])
            ->all();
    }

    public static function themeCards(): array
    {
        return collect(self::THEMES)
            ->map(function (array $theme, string $key): array {
                $counts = static::templateCounts($key);

                return [
                    'key' => $key,
                    'label' => $theme['label'],
                    'description' => $theme['description'],
                    'preview_url' => static::previewUrl($key),
                    'coverage' => collect([
                        'layout' => $counts['layout'] ? 'Layout' : null,
                        'pages' => $counts['pages'] ? 'Pages '.$counts['pages'] : null,
                        'member' => $counts['member'] ? 'Member '.$counts['member'] : null,
                        'auth' => $counts['auth'] ? 'Auth '.$counts['auth'] : null,
                        'partials' => $counts['partials'] ? 'Partials '.$counts['partials'] : null,
                        'components' => $counts['components'] ? 'Components '.$counts['components'] : null,
                    ])->filter()->implode(' / '),
                    'missing_groups' => static::missingGroups($key),
                    'fallback_summary' => static::fallbackSummary($key),
                ];
            })
            ->values()
            ->all();
    }

    public static function themeCard(string $theme): array
    {
        return collect(static::themeCards())
            ->firstWhere('key', $theme)
            ?? collect(static::themeCards())->firstWhere('key', 'default')
            ?? [
                'key' => 'default',
                'label' => 'Default / 当前主题',
                'description' => '',
                'preview_url' => static::previewUrl('default'),
                'coverage' => '',
                'missing_groups' => [],
                'fallback_summary' => '',
            ];
    }

    public static function previewTheme(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();
        $user = $request->user();

        if (! $request->hasSession() || ! $user?->hasMemberPermission('admin.access')) {
            return null;
        }

        $theme = $request->session()->get(self::PREVIEW_SESSION_KEY);

        if (! is_string($theme) || ! array_key_exists($theme, self::THEMES)) {
            return null;
        }

        return $theme;
    }

    public static function isPreviewing(): bool
    {
        return filled(static::previewTheme());
    }

    public static function previewUrl(string $theme): string
    {
        return url('/preview/theme/'.$theme);
    }

    public static function clearPreviewUrl(): string
    {
        return url('/preview/theme/reset');
    }

    public static function view(string $view, ?string $fallback = null): string
    {
        $themeView = 'themes.'.static::current().'.'.$view;

        if (View::exists($themeView)) {
            return $themeView;
        }

        return $fallback ?? 'themes.default.'.$view;
    }

    public static function homepageSettingBlueprint(string $theme): array
    {
        return self::HOMEPAGE_SETTING_BLUEPRINTS[$theme]
            ?? self::HOMEPAGE_SETTING_BLUEPRINTS['default'];
    }

    public static function homepageSettingPath(string $theme): string
    {
        return 'business_settings.theme_homepage.'.$theme;
    }

    public static function homepageSettings(string $theme, array $businessSettings = []): array
    {
        return data_get($businessSettings, 'theme_homepage.'.$theme, []);
    }

    public static function homepageSlotCategoryIds(string $theme, string $slotKey, array $businessSettings = []): array
    {
        $settings = static::homepageSettings($theme, $businessSettings);
        $value = data_get($settings, $slotKey.'.category_ids', []);

        if (is_array($value)) {
            return collect($value)
                ->filter(fn ($id): bool => filled($id))
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $legacyMap = [
            'xiaofang' => [
                'slot_01' => 'top_lead_category_id',
                'slot_02' => 'top_secondary_category_id',
                'slot_04' => 'feature_group_category_id',
                'slot_06' => 'inspiration_category_id',
                'slot_07' => 'read_more_category_id',
            ],
            'default' => [
                'slot_01' => 'lead_category_id',
                'slot_02' => 'secondary_category_id',
                'slot_03' => 'read_more_category_id',
            ],
            'editorial' => [
                'slot_01' => 'lead_category_id',
                'slot_02' => 'feature_strip_category_id',
                'slot_03' => 'read_more_category_id',
            ],
        ];

        $legacyKey = $legacyMap[$theme][$slotKey] ?? null;
        $legacyValue = $legacyKey ? data_get($settings, $legacyKey) : null;

        if (filled($legacyValue)) {
            return [(int) $legacyValue];
        }

        if ($theme === 'xiaofang') {
            $compatibilityMap = [
                'slot_04' => 'slot_03.category_ids',
                'slot_06' => 'slot_04.category_ids',
                'slot_07' => 'slot_05.category_ids',
            ];

            $compatibilityValue = $compatibilityMap[$slotKey] ?? null;

            if ($compatibilityValue) {
                $legacyArray = data_get($settings, $compatibilityValue, []);

                if (is_array($legacyArray)) {
                    return collect($legacyArray)
                        ->filter(fn ($id): bool => filled($id))
                        ->map(fn ($id): int => (int) $id)
                        ->unique()
                        ->values()
                        ->all();
                }
            }
        }

        return [];
    }

    public static function homepageSlotLimit(string $theme, string $slotKey, array $businessSettings = []): int
    {
        $settings = static::homepageSettings($theme, $businessSettings);
        $value = (int) data_get($settings, $slotKey.'.limit', 0);

        if ($value > 0) {
            return min(20, $value);
        }

        $blueprint = collect(static::homepageSettingBlueprint($theme))->firstWhere('key', $slotKey);

        return (int) ($blueprint['default_limit'] ?? 4);
    }

    public static function homepageSlotSort(string $theme, string $slotKey, array $businessSettings = []): string
    {
        $settings = static::homepageSettings($theme, $businessSettings);
        $value = (string) data_get($settings, $slotKey.'.sort', 'latest');

        return in_array($value, ['latest', 'oldest', 'featured_first', 'recommended_first'], true)
            ? $value
            : 'latest';
    }

    public static function homepageSlotMapHtml(string $theme): string
    {
        $slots = static::homepageSettingBlueprint($theme);

        if ($theme === 'xiaofang') {
            return static::xiaofangHomepageSlotMapHtml($slots);
        }

        return static::genericHomepageSlotMapHtml($slots);
    }

    protected static function genericHomepageSlotMapHtml(array $slots): string
    {
        $items = collect($slots)->map(function (array $slot): string {
            $code = e($slot['slot'] ?? $slot['label']);
            $desc = e($slot['description'] ?? '');

            return <<<HTML
                <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-top:1px solid rgba(226,232,240,0.9);">
                    <div style="min-width:2.6rem;font-weight:700;color:#0f172a;">{$code}</div>
                    <div style="min-width:0;">
                        <div style="font-size:0.92rem;font-weight:600;color:#0f172a;">{$slot['label']}</div>
                        <div style="margin-top:0.2rem;font-size:0.82rem;line-height:1.65;color:#64748b;">{$desc}</div>
                    </div>
                </div>
            HTML;
        })->implode('');

        return <<<HTML
            <div style="border:1px solid rgba(226,232,240,0.9);padding:0.2rem 1rem 0.2rem;background:#fff;">
                {$items}
            </div>
        HTML;
    }

    protected static function xiaofangHomepageSlotMapHtml(array $slots): string
    {
        $slotByKey = collect($slots)->keyBy('key');
        $slot01 = $slotByKey->get('slot_01', []);
        $slot02 = $slotByKey->get('slot_02', []);
        $slot03 = $slotByKey->get('slot_03', []);
        $slot04 = $slotByKey->get('slot_04', []);
        $slot05 = $slotByKey->get('slot_05', []);
        $slot06 = $slotByKey->get('slot_06', []);
        $slot07 = $slotByKey->get('slot_07', []);

        $box = function (array $slot, string $height = '120px'): string {
            $code = e($slot['slot'] ?? '--');
            $desc = e($slot['description'] ?? '');

            return <<<HTML
                <div style="border:1px solid rgba(203,213,225,0.9);background:#fff;padding:0.9rem;min-height:{$height};display:flex;flex-direction:column;justify-content:space-between;">
                    <div style="font-size:1rem;font-weight:700;letter-spacing:0.08em;color:#0f172a;">{$code}</div>
                    <div style="margin-top:0.5rem;font-size:0.82rem;line-height:1.7;color:#64748b;">{$desc}</div>
                </div>
            HTML;
        };

        $legend = collect($slots)->map(function (array $slot): string {
            $code = e($slot['slot'] ?? '--');
            $desc = e($slot['description'] ?? '');

            return <<<HTML
                <div style="display:flex;gap:0.65rem;align-items:flex-start;padding:0.55rem 0;border-top:1px solid rgba(226,232,240,0.9);">
                    <div style="min-width:2rem;font-size:0.82rem;font-weight:700;color:#0f172a;">{$code}</div>
                    <div style="font-size:0.82rem;line-height:1.65;color:#64748b;">{$desc}</div>
                </div>
            HTML;
        })->implode('');

        $slot01Box = $box($slot01, '150px');
        $slot02Box = $box($slot02, '150px');
        $slot03Box = $box($slot03, '132px');
        $slot04Box = $box($slot04, '140px');
        $slot05Box = $box($slot05, '140px');
        $slot06Box = $box($slot06, '106px');
        $slot07Box = $box($slot07, '106px');

        return <<<HTML
            <div style="border:1px solid rgba(226,232,240,0.9);background:#fff;padding:1rem;">
                <div style="display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,0.82fr) minmax(0,0.74fr);gap:0.8rem;">
                    {$slot01Box}
                    {$slot02Box}
                    {$slot03Box}
                </div>
                <div style="display:grid;grid-template-columns:minmax(0,1.12fr) minmax(0,0.7fr);gap:0.8rem;margin-top:0.8rem;">
                    {$slot04Box}
                    {$slot05Box}
                </div>
                <div style="display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:0.8rem;margin-top:0.8rem;">
                    {$slot06Box}
                    {$slot07Box}
                </div>
                <div style="margin-top:0.95rem;padding-top:0.15rem;">
                    {$legend}
                </div>
            </div>
        HTML;
    }

    protected static function templateCounts(string $theme): array
    {
        $basePath = resource_path('views/themes/'.$theme);

        if (! File::isDirectory($basePath)) {
            return [
                'layout' => 0,
                'pages' => 0,
                'member' => 0,
                'auth' => 0,
                'partials' => 0,
                'components' => 0,
            ];
        }

        return [
            'layout' => File::exists($basePath.'/layout.blade.php') ? 1 : 0,
            'pages' => count(File::glob($basePath.'/pages/*.blade.php') ?: []),
            'member' => count(File::glob($basePath.'/member/*.blade.php') ?: []),
            'auth' => count(File::glob($basePath.'/auth/*.blade.php') ?: []),
            'partials' => count(File::glob($basePath.'/partials/*.blade.php') ?: []),
            'components' => count(File::glob($basePath.'/components/*.blade.php') ?: []),
        ];
    }

    protected static function fallbackSummary(string $theme): string
    {
        if ($theme === 'default') {
            return '默认主题本身就是回退基线，未覆盖页面不会再继续回退到其他主题。';
        }

        $missing = collect(static::missingGroups($theme));

        if ($missing->isEmpty()) {
            return '这套主题已经覆盖默认主题的主要模板族，切换后通常不会触发默认主题回退。';
        }

        return '切换到这套主题后，'.implode('、', $missing->all()).'仍会自动回退到默认主题模板。';
    }

    protected static function missingGroups(string $theme): array
    {
        if ($theme === 'default') {
            return [];
        }

        $current = static::templateCounts($theme);
        $baseline = static::templateCounts('default');

        return collect([
            'pages' => max(0, $baseline['pages'] - $current['pages']) > 0 ? '内容页' : null,
            'member' => max(0, $baseline['member'] - $current['member']) > 0 ? '会员中心页' : null,
            'auth' => max(0, $baseline['auth'] - $current['auth']) > 0 ? '认证页' : null,
            'partials' => max(0, $baseline['partials'] - $current['partials']) > 0 ? '局部模板' : null,
            'components' => max(0, $baseline['components'] - $current['components']) > 0 ? '共享组件' : null,
        ])->filter()->values()->all();
    }
}
