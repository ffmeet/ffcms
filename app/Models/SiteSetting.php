<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    public const CURRENT_CACHE_KEY = 'site_settings.current';

    protected $fillable = [
        'site_name',
        'site_tagline',
        'site_description',
        'logo_text',
        'frontend_logo_path',
        'admin_logo_path',
        'favicon_path',
        'apple_touch_icon_path',
        'seo_title',
        'seo_description',
        'hero_eyebrow',
        'hero_title',
        'hero_body',
        'hero_primary_label',
        'hero_primary_url',
        'hero_secondary_label',
        'hero_secondary_url',
        'featured_posts_limit',
        'featured_categories_limit',
        'featured_tags_limit',
        'show_shop_section',
        'show_events_section',
        'show_membership_section',
        'primary_navigation',
        'footer_navigation',
        'social_links',
        'business_settings',
        'member_settings',
        'footer_copyright',
    ];

    protected function casts(): array
    {
        return [
            'primary_navigation' => 'array',
            'footer_navigation' => 'array',
            'social_links' => 'array',
            'business_settings' => 'array',
            'member_settings' => 'array',
            'show_shop_section' => 'boolean',
            'show_events_section' => 'boolean',
            'show_membership_section' => 'boolean',
        ];
    }

    public static function defaults(): array
    {
        return [
            'site_name' => '年度科技先生',
            'site_tagline' => '内容杂志与会员经济实验场',
            'site_description' => '以科技、产品、商业化和专题内容为核心的内容门户，逐步扩展会员、活动和商店能力。',
            'logo_text' => '帝',
            'frontend_logo_path' => null,
            'admin_logo_path' => null,
            'favicon_path' => null,
            'apple_touch_icon_path' => null,
            'seo_title' => '年度科技先生',
            'seo_description' => '一个以内容杂志风为基础，向会员、活动与商店延展的内容门户。',
            'hero_eyebrow' => 'CONTENT PORTAL',
            'hero_title' => '把内容、专题、活动和会员体系，编织成一个真正可运营的门户首页。',
            'hero_body' => '这一期先完成前台设计系统、首页、分类页、标签页和站点配置骨架，为后续商城、订阅、支付和活动系统铺底。',
            'hero_primary_label' => '浏览最新内容',
            'hero_primary_url' => '/search',
            'hero_secondary_label' => '了解会员计划',
            'hero_secondary_url' => '/pricing',
            'featured_posts_limit' => 8,
            'featured_categories_limit' => 6,
            'featured_tags_limit' => 12,
            'show_shop_section' => true,
            'show_events_section' => true,
            'show_membership_section' => true,
            'primary_navigation' => [
                ['label' => '首页', 'url' => '/'],
                ['label' => '栏目', 'url' => '/search'],
                ['label' => '会员', 'url' => '/pricing'],
                ['label' => '评论', 'url' => '/search?q=%E8%AF%84%E8%AE%BA'],
                ['label' => '媒体', 'url' => '/shop'],
            ],
            'footer_navigation' => [
                ['label' => '活动', 'url' => '/events'],
                ['label' => '商店', 'url' => '/shop'],
                ['label' => '会员计划', 'url' => '/pricing'],
                ['label' => '后台', 'url' => '/admin'],
            ],
            'social_links' => [
                ['label' => 'GitHub', 'url' => 'https://github.com/ffmeet/ffcms'],
            ],
            'business_settings' => [
                'shop_enabled' => true,
                'events_enabled' => true,
                'subscriptions_enabled' => true,
                'active_theme' => 'default',
                'wechat_enabled' => true,
                'alipay_enabled' => true,
                'paypal_enabled' => true,
                'stripe_enabled' => true,
                'payment_mode' => 'sandbox',
                'payment_statement_descriptor' => 'FFMEET',
                'payment_operator_note' => '先在后台完成渠道参数，再对接真实网关与签名校验。',
                'payment_metadata' => [],
                'wechat_app_id' => '',
                'wechat_mch_id' => '',
                'wechat_api_v3_key' => '',
                'wechat_private_key' => '',
                'wechat_serial_no' => '',
                'wechat_platform_certificate' => '',
                'alipay_app_id' => '',
                'alipay_pid' => '',
                'alipay_public_key' => '',
                'alipay_private_key' => '',
                'paypal_client_id' => '',
                'paypal_client_secret' => '',
                'paypal_webhook_id' => '',
                'stripe_publishable_key' => '',
                'stripe_secret_key' => '',
                'stripe_webhook_secret' => '',
                'route_settings' => [
                    'public_entries' => [
                        'search' => ['label' => '搜索', 'url' => ''],
                        'pricing' => ['label' => '会员计划', 'url' => ''],
                        'events' => ['label' => '活动', 'url' => ''],
                        'shop' => ['label' => '商店', 'url' => ''],
                        'member' => ['label' => '会员中心', 'url' => ''],
                        'admin' => ['label' => '后台', 'url' => ''],
                        'login' => ['label' => '登录', 'url' => ''],
                        'register' => ['label' => '注册', 'url' => ''],
                        'home' => ['label' => '前台首页', 'url' => ''],
                    ],
                    'member_entries' => [
                        'dashboard' => ['label' => '总览', 'url' => ''],
                        'posts' => ['label' => '我的稿件', 'url' => ''],
                        'comments' => ['label' => '我的评论', 'url' => ''],
                        'orders' => ['label' => '我的订单', 'url' => ''],
                        'subscriptions' => ['label' => '我的订阅', 'url' => ''],
                        'create_post' => ['label' => '发布新稿件', 'url' => ''],
                        'profile' => ['label' => '修改资料', 'url' => ''],
                        'activity_center' => ['label' => '活动中心', 'url' => ''],
                        'activities' => ['label' => '我的活动', 'url' => ''],
                    ],
                ],
                'home_sections_eyebrow' => 'Sections',
                'home_sections_title' => '栏目导航',
                'home_sections_cta' => '进入内容检索',
                'home_latest_eyebrow' => 'Latest Content',
                'home_latest_title' => '最新内容',
                'home_tags_eyebrow' => 'Topics',
                'home_tags_title' => '热门标签',
                'home_flash_eyebrow' => 'Flash',
                'home_flash_title' => '快讯与更新',
                'home_roadmap_eyebrow' => 'Roadmap',
                'home_roadmap_title' => '会员、活动与商店',
                'home_shop_title' => '商店系统',
                'home_shop_copy' => '商店会优先保持通用商品底座，并为未来实体商品扩展预留空间。',
                'home_events_title' => '活动系统',
                'home_events_copy' => '免费活动和付费活动都将共用统一的报名与支付底座。',
                'theme_homepage' => [
                    'default' => [
                        'slot_01' => ['category_ids' => [], 'limit' => 2, 'sort' => 'latest'],
                        'slot_02' => ['category_ids' => [], 'limit' => 6, 'sort' => 'latest'],
                        'slot_03' => ['category_ids' => [], 'limit' => 4, 'sort' => 'latest'],
                    ],
                    'editorial' => [
                        'slot_01' => ['category_ids' => [], 'limit' => 2, 'sort' => 'latest'],
                        'slot_02' => ['category_ids' => [], 'limit' => 4, 'sort' => 'latest'],
                        'slot_03' => ['category_ids' => [], 'limit' => 4, 'sort' => 'latest'],
                    ],
                    'xiaofang' => [
                        'slot_01' => ['category_ids' => [], 'limit' => 2, 'sort' => 'latest'],
                        'slot_02' => ['category_ids' => [], 'limit' => 2, 'sort' => 'latest'],
                        'slot_03' => ['category_ids' => [], 'limit' => 7, 'sort' => 'latest'],
                        'slot_04' => ['category_ids' => [], 'limit' => 4, 'sort' => 'latest'],
                        'slot_05' => ['category_ids' => [], 'limit' => 0, 'sort' => 'latest'],
                        'slot_06' => ['category_ids' => [], 'limit' => 4, 'sort' => 'latest'],
                        'slot_07' => ['category_ids' => [], 'limit' => 4, 'sort' => 'latest'],
                    ],
                ],
            ],
            'member_settings' => [
                'free_access_copy' => '免费会员可浏览公开内容、参与基础互动。',
                'paid_access_copy' => '付费层将逐步开放专题、活动优先权与商店权益。',
                'home_membership_title' => '会员与订阅',
            ],
            'footer_copyright' => '© 年度科技先生. 内容、会员与活动持续构建中。',
        ];
    }

    public static function current(): self
    {
        return Cache::rememberForever(static::CURRENT_CACHE_KEY, function (): self {
            $record = static::query()->first();

            if ($record) {
                $record->fillMissingDefaults();

                return $record;
            }

            return static::query()->create(static::defaults());
        });
    }

    public static function flushCurrentCache(): void
    {
        Cache::forget(static::CURRENT_CACHE_KEY);
    }

    public function fillMissingDefaults(): void
    {
        $defaults = static::defaults();
        $dirty = false;

        foreach ($defaults as $key => $value) {
            if (is_array($value) && is_array($this->{$key})) {
                $merged = $this->mergeMissingArrayValues($this->{$key}, $value);

                if ($merged !== $this->{$key}) {
                    $this->{$key} = $merged;
                    $dirty = true;
                }

                continue;
            }

            if (blank($this->{$key}) && $this->{$key} !== false && $this->{$key} !== 0 && $this->{$key} !== '0') {
                $this->{$key} = $value;
                $dirty = true;
            }
        }

        if ($dirty && $this->exists) {
            $this->saveQuietly();
        }
    }

    public function faviconUrl(): ?string
    {
        if (! filled($this->favicon_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->favicon_path);
    }

    public function appleTouchIconUrl(): ?string
    {
        if (! filled($this->apple_touch_icon_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->apple_touch_icon_path);
    }

    protected function mergeMissingArrayValues(array $current, array $defaults): array
    {
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $current) || (blank($current[$key]) && $current[$key] !== false && $current[$key] !== 0 && $current[$key] !== '0')) {
                $current[$key] = $value;
                continue;
            }

            if (is_array($value) && is_array($current[$key])) {
                $current[$key] = $this->mergeMissingArrayValues($current[$key], $value);
            }
        }

        return $current;
    }

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget(static::CURRENT_CACHE_KEY));
        static::deleted(fn (): bool => Cache::forget(static::CURRENT_CACHE_KEY));
    }
}
