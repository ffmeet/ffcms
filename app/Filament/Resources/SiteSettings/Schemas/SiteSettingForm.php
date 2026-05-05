<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Models\Category;
use App\Models\SiteSetting;
use App\Support\SiteTheme;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::siteComponents());
    }

    /**
     * @return array<Component>
     */
    public static function siteComponents(): array
    {
        return [
            static::overviewSection(),
            static::brandSection(),
            static::navigationSection(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function siteWorkbenchComponents(): array
    {
        return [
            static::brandSection('site-brand', true, 'openBrandModal'),
            static::navigationSection('site-navigation', true, 'openNavigationModal'),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function themeComponents(): array
    {
        $defaults = SiteSetting::defaults();
        $themeCards = SiteTheme::themeCards();

        return [
            Section::make('主题工作台')
                ->description('这里展示每套主题的定位、模板覆盖范围和预览入口，方便切换前先判断是否需要补模板。')
                ->compact()
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'xl' => 2,
                    ])->schema(
                        collect($themeCards)->map(function (array $theme): Section {
                            return Section::make($theme['label'])
                                ->compact()
                                ->schema([
                                    Placeholder::make('theme_description_'.$theme['key'])
                                        ->hiddenLabel()
                                        ->content($theme['description']),
                                    Placeholder::make('theme_coverage_'.$theme['key'])
                                        ->label('模板覆盖')
                                        ->content($theme['coverage'] ?: '当前只提供最小骨架'),
                                    Placeholder::make('theme_fallback_'.$theme['key'])
                                        ->label('切换提示')
                                        ->content($theme['fallback_summary']),
                                    Placeholder::make('theme_gap_'.$theme['key'])
                                        ->label('覆盖缺口')
                                        ->content(
                                            filled(implode(' / ', $theme['missing_groups']))
                                                ? implode(' / ', $theme['missing_groups'])
                                                : '当前没有明显缺口'
                                        ),
                                    Placeholder::make('theme_preview_'.$theme['key'])
                                        ->label('预览入口')
                                        ->content(new HtmlString(
                                            '<a href="'.$theme['preview_url'].'" target="_blank" rel="noopener noreferrer" style="color:#1d4ed8;text-decoration:underline;">打开前台预览</a>'
                                        )),
                                ]);
                        })->all()
                    ),
                ]),
            Section::make('前台主题与展示开关')
                ->description('控制当前启用主题，以及首页是否展示商店、活动、会员入口。')
                ->schema([
                    Select::make('business_settings.active_theme')
                        ->label('前台主题')
                        ->options(SiteTheme::options())
                        ->default(data_get($defaults, 'business_settings.active_theme', 'default'))
                        ->required()
                        ->live()
                        ->native(false)
                        ->helperText('切换前台主题时，未单独覆盖的页面会自动回退到默认主题模板。'),
                    Placeholder::make('business_settings.active_theme_hint')
                        ->label('当前切换提示')
                        ->content(function (Get $get): string {
                            $theme = (string) ($get('business_settings.active_theme') ?: data_get(SiteSetting::defaults(), 'business_settings.active_theme', 'default'));

                            return SiteTheme::themeCard($theme)['fallback_summary'];
                        })
                        ->columnSpan(2),
                    Placeholder::make('business_settings.active_theme_gap')
                        ->label('当前覆盖缺口')
                        ->content(function (Get $get): string {
                            $theme = (string) ($get('business_settings.active_theme') ?: data_get(SiteSetting::defaults(), 'business_settings.active_theme', 'default'));
                            $gaps = SiteTheme::themeCard($theme)['missing_groups'] ?? [];

                            return filled(implode(' / ', $gaps))
                                ? implode(' / ', $gaps)
                                : '当前主题已经覆盖默认主题的主要模板族';
                        })
                        ->columnSpan(2),
                    Toggle::make('show_shop_section')
                        ->label('显示商店入口')
                        ->inline(false),
                    Toggle::make('show_events_section')
                        ->label('显示活动入口')
                        ->inline(false),
                    Toggle::make('show_membership_section')
                        ->label('显示会员入口')
                        ->inline(false),
                    Toggle::make('business_settings.shop_enabled')
                        ->label('启用商店系统')
                        ->default(data_get($defaults, 'business_settings.shop_enabled', true))
                        ->inline(false),
                    Toggle::make('business_settings.events_enabled')
                        ->label('启用活动系统')
                        ->default(data_get($defaults, 'business_settings.events_enabled', true))
                        ->inline(false),
                    Toggle::make('business_settings.subscriptions_enabled')
                        ->label('启用订阅体系')
                        ->default(data_get($defaults, 'business_settings.subscriptions_enabled', true))
                        ->inline(false),
                ])
                ->columns(3),
            Section::make('首页 Hero')
                ->description('控制首页首屏的大标题、说明文案和主按钮。')
                ->schema([
                    TextInput::make('hero_eyebrow')
                        ->label('Hero 眉题')
                        ->maxLength(255)
                        ->placeholder('例如：CONTENT PORTAL'),
                    TextInput::make('hero_title')
                        ->label('Hero 主标题')
                        ->required()
                        ->placeholder('首页首屏主标题')
                        ->columnSpanFull(),
                    Textarea::make('hero_body')
                        ->label('Hero 说明')
                        ->rows(4)
                        ->placeholder('告诉访客站点的内容方向与价值')
                        ->columnSpanFull(),
                    TextInput::make('hero_primary_label')
                        ->label('主按钮文案')
                        ->placeholder('例如：浏览最新内容'),
                    TextInput::make('hero_primary_url')
                        ->label('主按钮链接')
                        ->placeholder('/search'),
                    TextInput::make('hero_secondary_label')
                        ->label('次按钮文案')
                        ->placeholder('例如：了解会员计划'),
                    TextInput::make('hero_secondary_url')
                        ->label('次按钮链接')
                        ->placeholder('/pricing'),
                ])
                ->columns(2),
            Section::make('首页展示节奏')
                ->description('控制首页各区块的数量和信息密度。')
                ->schema([
                    TextInput::make('featured_posts_limit')
                        ->label('首页内容数量')
                        ->numeric()
                        ->minValue(3)
                        ->maxValue(18)
                        ->helperText('推荐 6 到 12 条，避免首屏过重。'),
                    TextInput::make('featured_categories_limit')
                        ->label('栏目展示数量')
                        ->numeric()
                        ->minValue(3)
                        ->maxValue(12)
                        ->helperText('建议保留核心栏目，避免导航分散。'),
                    TextInput::make('featured_tags_limit')
                        ->label('标签展示数量')
                        ->numeric()
                        ->minValue(6)
                        ->maxValue(24)
                        ->helperText('标签太多会削弱首页信息密度。'),
                ])
                ->columns(3),
            Section::make('首页区块文案')
                ->description('统一管理首页栏目、最新内容、标签、快讯和路线图区块标题。')
                ->schema([
                    TextInput::make('business_settings.home_sections_eyebrow')
                        ->label('栏目眉题')
                        ->default(data_get($defaults, 'business_settings.home_sections_eyebrow')),
                    TextInput::make('business_settings.home_sections_title')
                        ->label('栏目标题')
                        ->default(data_get($defaults, 'business_settings.home_sections_title')),
                    TextInput::make('business_settings.home_sections_cta')
                        ->label('栏目按钮')
                        ->default(data_get($defaults, 'business_settings.home_sections_cta')),
                    TextInput::make('business_settings.home_latest_eyebrow')
                        ->label('最新内容眉题')
                        ->default(data_get($defaults, 'business_settings.home_latest_eyebrow')),
                    TextInput::make('business_settings.home_latest_title')
                        ->label('最新内容标题')
                        ->default(data_get($defaults, 'business_settings.home_latest_title')),
                    TextInput::make('business_settings.home_tags_eyebrow')
                        ->label('标签眉题')
                        ->default(data_get($defaults, 'business_settings.home_tags_eyebrow')),
                    TextInput::make('business_settings.home_tags_title')
                        ->label('标签标题')
                        ->default(data_get($defaults, 'business_settings.home_tags_title')),
                    TextInput::make('business_settings.home_flash_eyebrow')
                        ->label('快讯眉题')
                        ->default(data_get($defaults, 'business_settings.home_flash_eyebrow')),
                    TextInput::make('business_settings.home_flash_title')
                        ->label('快讯标题')
                        ->default(data_get($defaults, 'business_settings.home_flash_title')),
                    TextInput::make('business_settings.home_roadmap_eyebrow')
                        ->label('路线图眉题')
                        ->default(data_get($defaults, 'business_settings.home_roadmap_eyebrow')),
                    TextInput::make('business_settings.home_roadmap_title')
                        ->label('路线图标题')
                        ->default(data_get($defaults, 'business_settings.home_roadmap_title')),
                ])
                ->columns(3),
            Section::make('会员展示文案')
                ->description('用于首页会员模块和订阅入口的说明文案。')
                ->schema([
                    TextInput::make('member_settings.home_membership_title')
                        ->label('会员模块标题')
                        ->default(data_get($defaults, 'member_settings.home_membership_title'))
                        ->columnSpanFull(),
                    Textarea::make('member_settings.free_access_copy')
                        ->label('免费会员说明')
                        ->rows(3)
                        ->default(data_get($defaults, 'member_settings.free_access_copy')),
                    Textarea::make('member_settings.paid_access_copy')
                        ->label('付费会员说明')
                        ->rows(3)
                        ->default(data_get($defaults, 'member_settings.paid_access_copy')),
                ])
                ->columns(2),
            Section::make('商业化区块文案')
                ->description('首页商店与活动区块的标题和辅助说明从这里统一管理。')
                ->schema([
                    TextInput::make('business_settings.home_shop_title')
                        ->label('商店标题')
                        ->default(data_get($defaults, 'business_settings.home_shop_title')),
                    Textarea::make('business_settings.home_shop_copy')
                        ->label('商店说明')
                        ->rows(3)
                        ->default(data_get($defaults, 'business_settings.home_shop_copy'))
                        ->columnSpan(2),
                    TextInput::make('business_settings.home_events_title')
                        ->label('活动标题')
                        ->default(data_get($defaults, 'business_settings.home_events_title')),
                    Textarea::make('business_settings.home_events_copy')
                        ->label('活动说明')
                        ->rows(3)
                        ->default(data_get($defaults, 'business_settings.home_events_copy'))
                        ->columnSpan(2),
                ])
                ->columns(2),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function paymentComponents(): array
    {
        $defaults = SiteSetting::defaults();

        return [
            Section::make('支付中心')
                ->description('统一维护支付环境、渠道启用状态和基础参数，让前台下单、会员支付页和后台记录保持同一套规则。')
                ->schema([
                    Select::make('business_settings.payment_mode')
                        ->label('运行环境')
                        ->options([
                            'sandbox' => 'Sandbox / 演练环境',
                            'production' => 'Production / 正式环境',
                        ])
                        ->default(data_get($defaults, 'business_settings.payment_mode', 'sandbox'))
                        ->native(false)
                        ->live(),
                    TextInput::make('business_settings.payment_statement_descriptor')
                        ->label('统一账单描述')
                        ->default(data_get($defaults, 'business_settings.payment_statement_descriptor'))
                        ->maxLength(32)
                        ->helperText('建议控制在 22 到 32 个字符以内，避免不同渠道的账单摘要不一致。'),
                    Textarea::make('business_settings.payment_operator_note')
                        ->label('运营备注')
                        ->default(data_get($defaults, 'business_settings.payment_operator_note'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ])->schema([
                        Toggle::make('business_settings.wechat_enabled')
                            ->label('微信支付')
                            ->default(data_get($defaults, 'business_settings.wechat_enabled', true))
                            ->inline(false),
                        Toggle::make('business_settings.alipay_enabled')
                            ->label('支付宝')
                            ->default(data_get($defaults, 'business_settings.alipay_enabled', true))
                            ->inline(false),
                        Toggle::make('business_settings.paypal_enabled')
                            ->label('PayPal')
                            ->default(data_get($defaults, 'business_settings.paypal_enabled', true))
                            ->inline(false),
                        Toggle::make('business_settings.stripe_enabled')
                            ->label('Stripe')
                            ->default(data_get($defaults, 'business_settings.stripe_enabled', true))
                            ->inline(false),
                    ]),
                ])
                ->columns(2),
            Section::make('渠道参数')
                ->description('只有在渠道启用且关键参数补齐后，前台结算页才会把它当作可用支付方式。')
                ->schema([
                    Tabs::make('payment_provider_tabs')
                        ->tabs([
                            Tab::make('微信支付')
                                ->schema([
                                    Toggle::make('business_settings.wechat_enabled')
                                        ->label('启用微信支付')
                                        ->inline(false),
                                    TextInput::make('business_settings.wechat_app_id')
                                        ->label('App ID')
                                        ->default(data_get($defaults, 'business_settings.wechat_app_id'))
                                        ->live(debounce: 400),
                                    TextInput::make('business_settings.wechat_mch_id')
                                        ->label('商户号')
                                        ->default(data_get($defaults, 'business_settings.wechat_mch_id'))
                                        ->live(debounce: 400),
                                    TextInput::make('business_settings.wechat_api_v3_key')
                                        ->label('API v3 Key')
                                        ->password()
                                        ->revealable()
                                        ->default(data_get($defaults, 'business_settings.wechat_api_v3_key'))
                                        ->live(debounce: 400),
                                    Textarea::make('business_settings.wechat_private_key')
                                        ->label('商户私钥')
                                        ->rows(4)
                                        ->default(data_get($defaults, 'business_settings.wechat_private_key'))
                                        ->live(debounce: 400)
                                        ->columnSpanFull(),
                                    TextInput::make('business_settings.wechat_serial_no')
                                        ->label('商户证书序列号')
                                        ->default(data_get($defaults, 'business_settings.wechat_serial_no'))
                                        ->live(debounce: 400),
                                    Textarea::make('business_settings.wechat_platform_certificate')
                                        ->label('微信平台证书')
                                        ->rows(4)
                                        ->default(data_get($defaults, 'business_settings.wechat_platform_certificate'))
                                        ->live(debounce: 400)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                            Tab::make('支付宝')
                                ->schema([
                                    Toggle::make('business_settings.alipay_enabled')
                                        ->label('启用支付宝')
                                        ->inline(false),
                                    TextInput::make('business_settings.alipay_app_id')
                                        ->label('App ID')
                                        ->default(data_get($defaults, 'business_settings.alipay_app_id'))
                                        ->live(debounce: 400),
                                    TextInput::make('business_settings.alipay_pid')
                                        ->label('PID / 商户身份')
                                        ->default(data_get($defaults, 'business_settings.alipay_pid'))
                                        ->live(debounce: 400),
                                    Textarea::make('business_settings.alipay_public_key')
                                        ->label('支付宝公钥')
                                        ->default(data_get($defaults, 'business_settings.alipay_public_key'))
                                        ->rows(4)
                                        ->live(debounce: 400)
                                        ->columnSpanFull(),
                                    Textarea::make('business_settings.alipay_private_key')
                                        ->label('商户私钥')
                                        ->default(data_get($defaults, 'business_settings.alipay_private_key'))
                                        ->rows(4)
                                        ->live(debounce: 400)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                            Tab::make('PayPal')
                                ->schema([
                                    Toggle::make('business_settings.paypal_enabled')
                                        ->label('启用 PayPal')
                                        ->inline(false),
                                    TextInput::make('business_settings.paypal_client_id')
                                        ->label('Client ID')
                                        ->default(data_get($defaults, 'business_settings.paypal_client_id'))
                                        ->live(debounce: 400),
                                    TextInput::make('business_settings.paypal_client_secret')
                                        ->label('Client Secret')
                                        ->password()
                                        ->revealable()
                                        ->default(data_get($defaults, 'business_settings.paypal_client_secret'))
                                        ->live(debounce: 400),
                                    TextInput::make('business_settings.paypal_webhook_id')
                                        ->label('Webhook ID')
                                        ->default(data_get($defaults, 'business_settings.paypal_webhook_id'))
                                        ->live(debounce: 400),
                                ])
                                ->columns(2),
                            Tab::make('Stripe')
                                ->schema([
                                    Toggle::make('business_settings.stripe_enabled')
                                        ->label('启用 Stripe')
                                        ->inline(false),
                                    TextInput::make('business_settings.stripe_publishable_key')
                                        ->label('Publishable Key')
                                        ->default(data_get($defaults, 'business_settings.stripe_publishable_key'))
                                        ->live(debounce: 400),
                                    TextInput::make('business_settings.stripe_secret_key')
                                        ->label('Secret Key')
                                        ->password()
                                        ->revealable()
                                        ->default(data_get($defaults, 'business_settings.stripe_secret_key'))
                                        ->live(debounce: 400),
                                    TextInput::make('business_settings.stripe_webhook_secret')
                                        ->label('Webhook Secret')
                                        ->password()
                                        ->revealable()
                                        ->default(data_get($defaults, 'business_settings.stripe_webhook_secret'))
                                        ->live(debounce: 400),
                                ])
                                ->columns(2),
                        ])
                        ->columnSpanFull(),
                ]),
            Section::make('支付记录补充数据')
                ->description('预留给人工补单、对账回写和渠道扩展字段，避免把杂项配置散落到别处。')
                ->schema([
                    KeyValue::make('business_settings.payment_metadata')
                        ->label('支付扩展字段')
                        ->default(data_get($defaults, 'business_settings.payment_metadata', []))
                        ->addActionLabel('新增字段')
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function homepageComponents(?string $theme = null): array
    {
        $defaults = SiteSetting::defaults();
        $theme ??= (string) data_get($defaults, 'business_settings.active_theme', 'default');
        $theme = array_key_exists($theme, SiteTheme::THEMES) ? $theme : 'default';
        $themeCard = SiteTheme::themeCard($theme);
        $themePath = SiteTheme::homepageSettingPath($theme);
        $themeDefaults = SiteTheme::homepageSettings($theme, $defaults['business_settings'] ?? []);

        return [
            Section::make('当前首页主题')
                ->description('首页设置页会按当前前台主题显示对应字段，不同主题可以拥有不同的首页内容位配置。')
                ->schema([
                    Placeholder::make('homepage_theme_label')
                        ->label('当前主题')
                        ->content($themeCard['label']),
                    Placeholder::make('homepage_theme_description')
                        ->label('主题说明')
                        ->content($themeCard['description']),
                    Placeholder::make('homepage_theme_scope')
                        ->label('配置作用域')
                        ->content('当前页面保存的是 '.$theme.' 主题自己的首页设置，不会覆盖其他主题的首页配置。')
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('位置图')
                ->description('首页内容位以位置编号为准，不依赖“灵感”“继续阅读”这类可能变化的运营文案。')
                ->schema([
                    Placeholder::make('homepage_slot_map')
                        ->hiddenLabel()
                        ->content(new HtmlString(SiteTheme::homepageSlotMapHtml($theme)))
                        ->columnSpanFull(),
                ]),
            Section::make('首页展示数量')
                ->description('控制首页整体信息密度。数量控制仍然属于全站通用，不随主题切换。')
                ->schema([
                    TextInput::make('featured_posts_limit')
                        ->label('首页文章池数量')
                        ->numeric()
                        ->minValue(3)
                        ->maxValue(18)
                        ->helperText('当前控制器仍会参考这组数量限制。'),
                    TextInput::make('featured_categories_limit')
                        ->label('栏目展示数量')
                        ->numeric()
                        ->minValue(3)
                        ->maxValue(12),
                    TextInput::make('featured_tags_limit')
                        ->label('标签展示数量')
                        ->numeric()
                        ->minValue(6)
                        ->maxValue(24),
                ])
                ->columns(3),
            Section::make('首页模块文案')
                ->description('保留首页通用眉题、标题与辅助说明，供所有主题复用。')
                ->schema([
                    TextInput::make('business_settings.home_latest_title')
                        ->label('最新内容标题')
                        ->default(data_get($defaults, 'business_settings.home_latest_title')),
                    TextInput::make('business_settings.home_tags_title')
                        ->label('标签标题')
                        ->default(data_get($defaults, 'business_settings.home_tags_title')),
                    TextInput::make('business_settings.home_flash_title')
                        ->label('快讯标题')
                        ->default(data_get($defaults, 'business_settings.home_flash_title')),
                    TextInput::make('business_settings.home_roadmap_title')
                        ->label('路线图标题')
                        ->default(data_get($defaults, 'business_settings.home_roadmap_title')),
                    TextInput::make('member_settings.home_membership_title')
                        ->label('会员模块标题')
                        ->default(data_get($defaults, 'member_settings.home_membership_title')),
                    TextInput::make('business_settings.home_events_title')
                        ->label('活动模块标题')
                        ->default(data_get($defaults, 'business_settings.home_events_title')),
                    TextInput::make('business_settings.home_shop_title')
                        ->label('商店模块标题')
                        ->default(data_get($defaults, 'business_settings.home_shop_title')),
                ])
                ->columns(2),
            Section::make('主题首页内容位')
                ->description('这里按当前主题维护首页位置编号与分类来源的映射。每个位置支持挂多个分类，系统会按顺序混合取数；同一个分类也可以同时服务多个位置。')
                ->schema(static::homepageThemeSlotComponents($themePath, $themeDefaults, $theme))
                ->columns(1),
        ];
    }

    protected static function overviewSection(): Section
    {
        return Section::make('当前配置概览')
            ->description('先快速确认当前主题、启用能力和前台导航规模，再决定是否需要继续往下调整。')
            ->compact()
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 4,
                ])->schema([
                    Placeholder::make('overview_active_theme')
                        ->label('当前主题')
                        ->content(function ($record): string {
                            $theme = data_get($record?->business_settings, 'active_theme')
                                ?? data_get(SiteSetting::defaults(), 'business_settings.active_theme', 'default');

                            return str((string) $theme)->headline()->toString();
                        }),
                    Placeholder::make('overview_enabled_modules')
                        ->label('已启用业务')
                        ->content(function ($record): string {
                            $settings = $record?->business_settings ?? SiteSetting::defaults()['business_settings'];

                            $enabled = collect([
                                '商店' => data_get($settings, 'shop_enabled', true),
                                '活动' => data_get($settings, 'events_enabled', true),
                                '订阅' => data_get($settings, 'subscriptions_enabled', true),
                            ])->filter()->keys()->values();

                            return $enabled->isNotEmpty()
                                ? $enabled->implode(' / ')
                                : '全部关闭';
                        }),
                    Placeholder::make('overview_payment_channels')
                        ->label('支付渠道')
                        ->content(function ($record): string {
                            $settings = $record?->business_settings ?? SiteSetting::defaults()['business_settings'];

                            $enabled = collect([
                                '微信' => data_get($settings, 'wechat_enabled', true),
                                '支付宝' => data_get($settings, 'alipay_enabled', true),
                                'PayPal' => data_get($settings, 'paypal_enabled', true),
                                'Stripe' => data_get($settings, 'stripe_enabled', true),
                            ])->filter()->keys()->values();

                            return $enabled->isNotEmpty()
                                ? $enabled->implode(' / ')
                                : '未启用';
                        }),
                    Placeholder::make('overview_navigation_count')
                        ->label('顶部导航项')
                        ->content(function ($record): string {
                            $navigation = $record?->primary_navigation ?? SiteSetting::defaults()['primary_navigation'];

                            return (string) count($navigation);
                        }),
                ]),
            ]);
    }

    /**
     * @return array<Component>
     */
    protected static function homepageThemeSlotComponents(string $themePath, array $themeDefaults, string $theme): array
    {
        $categoryOptions = static::categoryOptions();
        $sortOptions = [
            'latest' => '最新优先',
            'oldest' => '最早优先',
            'featured_first' => '精选优先',
            'recommended_first' => '推荐优先',
        ];

        return collect(SiteTheme::homepageSettingBlueprint($theme))
            ->map(function (array $item) use ($themePath, $themeDefaults, $categoryOptions, $sortOptions): Grid {
                $baseField = $themePath.'.'.$item['key'];
                $defaultCategories = data_get($themeDefaults, $item['key'].'.category_ids', []);
                $defaultLimit = data_get($themeDefaults, $item['key'].'.limit', $item['default_limit'] ?? 4);
                $defaultSort = data_get($themeDefaults, $item['key'].'.sort', 'latest');
                $configurable = (bool) ($item['configurable'] ?? true);

                return Grid::make(1)->schema([
                    Placeholder::make($baseField.'_label')
                        ->label($item['label'])
                        ->content($item['description'])
                        ->columnSpanFull(),
                    ...($configurable ? [
                        Grid::make([
                            'default' => 1,
                            'xl' => 12,
                        ])->schema([
                            Select::make($baseField.'.category_ids')
                                ->label('分类来源')
                                ->options($categoryOptions)
                                ->default($defaultCategories)
                                ->searchable()
                                ->multiple()
                                ->native(false)
                                ->preload()
                                ->helperText('可选择多个分类，可复用到其他位置。')
                                ->placeholder('未指定')
                                ->columnSpan([
                                    'default' => 1,
                                    'xl' => 7,
                                ]),
                            TextInput::make($baseField.'.limit')
                                ->label('取数上限')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(20)
                                ->default($defaultLimit)
                                ->helperText('1-20 条')
                                ->columnSpan([
                                    'default' => 1,
                                    'xl' => 2,
                                ]),
                            Select::make($baseField.'.sort')
                                ->label('排序方式')
                                ->options($sortOptions)
                                ->default($defaultSort)
                                ->native(false)
                                ->helperText('内容优先级')
                                ->columnSpan([
                                    'default' => 1,
                                    'xl' => 3,
                                ]),
                        ])->columnSpanFull(),
                    ] : [
                        Placeholder::make($baseField.'_system_note')
                            ->label('系统说明')
                            ->content('该位置当前承载订阅入口与活动列表，不走文章分类配置。')
                            ->columnSpanFull(),
                    ]),
                ]);
            })
            ->all();
    }

    protected static function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function brandSection(?string $anchorId = null, bool $collapsible = false, ?string $editAction = null): Section
    {
        $section = Section::make('站点品牌')
            ->description($editAction ? static::sectionDescription('', $editAction) : null)
            ->schema([
                TextInput::make('site_name')
                    ->label('站点名称')
                    ->required()
                    ->live(debounce: 400)
                    ->maxLength(255)
                    ->placeholder('例如：年度科技先生'),
                TextInput::make('site_tagline')
                    ->label('站点副标题')
                    ->live(debounce: 400)
                    ->maxLength(255)
                    ->placeholder('一句话描述站点定位'),
                FileUpload::make('frontend_logo_path')
                    ->label('前台 Logo')
                    ->disk('public')
                    ->directory('branding/frontend')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->panelLayout('compact')
                    ->imagePreviewHeight(56)
                    ->extraAttributes(['class' => 'ecms-logo-upload'])
                    ->columnSpan(1),
                FileUpload::make('admin_logo_path')
                    ->label('后台图标')
                    ->disk('public')
                    ->directory('branding/admin')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(1024)
                    ->panelLayout('compact')
                    ->imagePreviewHeight(56)
                    ->extraAttributes(['class' => 'ecms-logo-upload'])
                    ->columnSpan(1),
                Textarea::make('site_description')
                    ->label('站点描述')
                    ->live(debounce: 400)
                    ->rows(3)
                    ->placeholder('面向访客说明这是一个什么站点')
                    ->columnSpanFull(),
                TextInput::make('seo_title')
                    ->label('默认 SEO 标题')
                    ->live(debounce: 400)
                    ->placeholder('未单独配置页面 SEO 时使用'),
            ])
            ->columns(3);

        if ($collapsible) {
            $section->collapsible()->collapsed();
        }

        if ($anchorId) {
            $section->extraAttributes([
                'id' => $anchorId,
                'class' => 'ecms-settings-workbench-section',
            ]);
        }

        return $section;
    }

    public static function navigationSection(?string $anchorId = null, bool $collapsible = false, ?string $editAction = null): Section
    {
        $defaults = SiteSetting::defaults();

        $section = Section::make('菜单项')
            ->description($editAction ? static::sectionDescription('', $editAction) : null)
            ->schema([
                Tabs::make('navigation_tabs')
                    ->tabs([
                        Tab::make('顶部导航')
                            ->schema([
                                Repeater::make('primary_navigation')
                                    ->label('顶部导航')
                                    ->default($defaults['primary_navigation'])
                                    ->addActionLabel('新增')
                                    ->reorderable(false)
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('名称')
                                            ->inlineLabel()
                                            ->required()
                                            ->live(debounce: 300)
                                            ->extraInputAttributes(['class' => 'ecms-settings-input-compact']),
                                        TextInput::make('url')
                                            ->label('链接')
                                            ->inlineLabel()
                                            ->required()
                                            ->live(debounce: 300)
                                            ->extraInputAttributes(['class' => 'ecms-settings-input-wide']),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('页脚导航')
                            ->schema([
                                Repeater::make('footer_navigation')
                                    ->label('页脚导航')
                                    ->default($defaults['footer_navigation'])
                                    ->addActionLabel('新增')
                                    ->reorderable(false)
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('名称')
                                            ->inlineLabel()
                                            ->required()
                                            ->live(debounce: 300)
                                            ->extraInputAttributes(['class' => 'ecms-settings-input-compact']),
                                        TextInput::make('url')
                                            ->label('链接')
                                            ->inlineLabel()
                                            ->required()
                                            ->live(debounce: 300)
                                            ->extraInputAttributes(['class' => 'ecms-settings-input-wide']),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('社交链接')
                            ->schema([
                                Repeater::make('social_links')
                                    ->label('社交链接')
                                    ->default($defaults['social_links'])
                                    ->addActionLabel('新增')
                                    ->reorderable(false)
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('名称')
                                            ->inlineLabel()
                                            ->required()
                                            ->live(debounce: 300)
                                            ->extraInputAttributes(['class' => 'ecms-settings-input-compact']),
                                        TextInput::make('url')
                                            ->label('链接')
                                            ->inlineLabel()
                                            ->required()
                                            ->live(debounce: 300)
                                            ->extraInputAttributes(['class' => 'ecms-settings-input-wide']),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Textarea::make('footer_copyright')
                    ->label('页脚版权文案')
                    ->live(debounce: 400)
                    ->rows(3)
                    ->columnSpanFull(),
            ]);

        if ($collapsible) {
            $section->collapsible()->collapsed();
        }

        if ($anchorId) {
            $section->extraAttributes([
                'id' => $anchorId,
                'class' => 'ecms-settings-workbench-section',
            ]);
        }

        return $section;
    }

    protected static function sectionDescription(string $text, ?string $editAction = null): HtmlString|string
    {
        if (! $editAction) {
            return $text;
        }

        $button = '<button type="button" class="ecms-settings-section-action" wire:click="'.$editAction.'">编辑</button>';

        return new HtmlString(
            '<div class="ecms-settings-section-description">'.$button.'</div>'
        );
    }
}
