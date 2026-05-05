<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Post;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Support\FrontendCache;
use App\Support\Homepage\XiaofangHomepageBuilder;
use App\Support\SiteTheme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $settings = SiteSetting::current();
        $activeTheme = SiteTheme::current();
        $businessSettings = $settings->business_settings ?? [];
        $themeHomepageSettings = SiteTheme::homepageSettings($activeTheme, $businessSettings);
        $homepagePayload = Cache::remember(
            FrontendCache::key('homepage.payload', [
                'theme' => $activeTheme,
                'featured_posts_limit' => (int) $settings->featured_posts_limit,
                'featured_categories_limit' => (int) $settings->featured_categories_limit,
                'featured_tags_limit' => (int) $settings->featured_tags_limit,
                'theme_homepage_settings' => $themeHomepageSettings,
            ]),
            now()->addMinutes(10),
            function () use ($settings, $activeTheme, $themeHomepageSettings, $businessSettings): array {
                $featuredPostsLimit = max(4, min(12, (int) $settings->featured_posts_limit));
                $featuredCategoriesLimit = max(3, min(10, (int) $settings->featured_categories_limit));
                $featuredTagsLimit = max(6, min(20, (int) $settings->featured_tags_limit));
                $contentPostsQuery = Post::query()
                    ->with(['category', 'contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
                    ->published()
                    ->nonFlash();

                $homepagePosts = (clone $contentPostsQuery)
                    ->latest('published_at')
                    ->limit($featuredPostsLimit + 10)
                    ->get();

                $leadPost = (clone $contentPostsQuery)
                    ->headline()
                    ->latest('published_at')
                    ->first();

                $leadPost ??= $homepagePosts->first();

                $featuredCandidates = (clone $contentPostsQuery)
                    ->featuredPlacement()
                    ->when($leadPost, fn ($query) => $query->whereKeyNot($leadPost->getKey()))
                    ->latest('published_at')
                    ->limit(max(2, $featuredPostsLimit))
                    ->get();

                $recommendedCandidates = (clone $contentPostsQuery)
                    ->recommendedPlacement()
                    ->when($leadPost, fn ($query) => $query->whereKeyNot($leadPost->getKey()))
                    ->whereNotIn('id', $featuredCandidates->pluck('id')->all())
                    ->latest('published_at')
                    ->limit(2)
                    ->get();

                $featuredPosts = $featuredCandidates
                    ->concat($recommendedCandidates)
                    ->concat(
                        $homepagePosts->reject(fn (Post $post): bool => collect([
                            $leadPost?->id,
                            ...$featuredCandidates->pluck('id')->all(),
                            ...$recommendedCandidates->pluck('id')->all(),
                        ])->contains($post->id))
                    )
                    ->take($featuredPostsLimit)
                    ->values();

                $flashPosts = Post::query()
                    ->with(['category', 'contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
                    ->published()
                    ->latest('published_at')
                    ->get()
                    ->filter(fn (Post $post): bool => $post->isFlashModel())
                    ->take(4)
                    ->values();

                $categories = Category::query()
                    ->withCount('posts')
                    ->orderBy('sort_order')
                    ->limit($featuredCategoriesLimit)
                    ->get();

                $tags = Tag::query()
                    ->orderByDesc('count')
                    ->orderBy('name')
                    ->limit($featuredTagsLimit)
                    ->get();

                $metrics = [
                    'published_posts' => Post::query()->published()->count(),
                    'categories' => Category::query()->count(),
                    'tags' => Tag::query()->count(),
                ];

                $featuredEvents = Schema::hasTable('events')
                    ? Event::query()
                        ->latest('starts_at')
                        ->limit(4)
                        ->get()
                    : collect();

                $featuredProducts = Schema::hasTable('products')
                    ? Product::query()
                        ->where('status', 'published')
                        ->latest('published_at')
                        ->limit(4)
                        ->get()
                    : collect();

                $fallbackNewsCategoryId = $this->firstMatchingCategoryId([
                    ['slug' => 'news'],
                    ['name' => '新闻中心'],
                ]);

                $slot04CategoryIds = SiteTheme::homepageSlotCategoryIds($activeTheme, 'slot_04', $businessSettings);
                $slot04HasExplicitConfig = $slot04CategoryIds !== [];
                $primaryFeatureGroupCategoryIds = collect($slot04CategoryIds)->take(1)->values()->all();
                $secondaryFeatureGroupCategoryIds = collect($slot04CategoryIds)->skip(1)->take(1)->values()->all();

                $fallbackCultureCategoryId = $this->firstMatchingCategoryId([
                    ['slug' => 'culture'],
                    ['name' => 'Culture'],
                    ['name' => '文化'],
                ]);

                $fallbackDesignCategoryId = $this->firstMatchingCategoryId([
                    ['slug' => 'design'],
                    ['name' => 'Design'],
                    ['name' => '设计'],
                ]);

                if (! $slot04HasExplicitConfig && $primaryFeatureGroupCategoryIds === [] && filled($fallbackCultureCategoryId)) {
                    $primaryFeatureGroupCategoryIds = [(int) $fallbackCultureCategoryId];
                }

                if (! $slot04HasExplicitConfig && $secondaryFeatureGroupCategoryIds === [] && filled($fallbackDesignCategoryId)) {
                    $secondaryFeatureGroupCategoryIds = [(int) $fallbackDesignCategoryId];
                }

                $fallbackLifestyleCategoryId = $this->firstMatchingCategoryId([
                    ['slug' => 'lifestyle'],
                    ['name' => 'Lifestyle'],
                    ['name' => '生活方式'],
                ]);

                $fallbackTechnologyCategoryId = $this->firstMatchingCategoryId([
                    ['slug' => 'technology'],
                    ['name' => 'Technology'],
                    ['name' => '科技'],
                ]);

                $fallbackPoliticsCategoryId = $this->firstMatchingCategoryId([
                    ['slug' => 'politics'],
                    ['name' => 'Politics'],
                    ['name' => '政治'],
                ]);

                $fallbackLeadStories = collect([$leadPost])->filter();

                if (filled($fallbackNewsCategoryId)) {
                    $fallbackLeadStories = $fallbackLeadStories->concat(
                        $this->homepageStoriesForCategories(
                            (clone $contentPostsQuery)->when($leadPost, fn ($query) => $query->whereKeyNot($leadPost->getKey())),
                            [(int) $fallbackNewsCategoryId],
                            1,
                            'latest',
                        )
                    );
                }

                $fallbackSecondaryCategoryIds = collect([
                    $fallbackCultureCategoryId,
                    $fallbackTechnologyCategoryId,
                    $fallbackPoliticsCategoryId,
                    $fallbackLifestyleCategoryId,
                ])->filter()->map(fn ($id): int => (int) $id)->values()->all();

                $fallbackSecondaryStories = $fallbackSecondaryCategoryIds === []
                    ? collect()
                    : $this->homepageStoriesForCategories(
                        (clone $contentPostsQuery)
                            ->whereNotIn('id', $fallbackLeadStories->pluck('id')->filter()->all()),
                        $fallbackSecondaryCategoryIds,
                        2,
                        'latest',
                    );

                $fallbackLatestStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery)
                        ->whereNotIn('id', $fallbackLeadStories->pluck('id')->filter()->all())
                        ->whereNotIn('id', $fallbackSecondaryStories->pluck('id')->all()),
                    collect([
                        $fallbackNewsCategoryId,
                        $fallbackPoliticsCategoryId,
                        $fallbackTechnologyCategoryId,
                        $fallbackLifestyleCategoryId,
                        $fallbackCultureCategoryId,
                    ])->filter()->map(fn ($id): int => (int) $id)->values()->all(),
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_03', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_03', $businessSettings),
                );

                $fallbackInspirationStories = filled($fallbackLifestyleCategoryId)
                    ? $this->homepageStoriesForCategories(
                        (clone $contentPostsQuery),
                        [(int) $fallbackLifestyleCategoryId],
                        SiteTheme::homepageSlotLimit($activeTheme, 'slot_06', $businessSettings),
                        SiteTheme::homepageSlotSort($activeTheme, 'slot_06', $businessSettings),
                    )
                    : collect();

                $fallbackReadMoreStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery)->whereNotIn('id', collect([
                        ...$fallbackLeadStories->pluck('id')->all(),
                        ...$fallbackSecondaryStories->pluck('id')->all(),
                    ])->filter()->all()),
                    collect([
                        $fallbackTechnologyCategoryId,
                        $fallbackPoliticsCategoryId,
                        $fallbackDesignCategoryId,
                        $fallbackCultureCategoryId,
                        $fallbackLifestyleCategoryId,
                    ])->filter()->map(fn ($id): int => (int) $id)->values()->all(),
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_07', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_07', $businessSettings),
                );

                $slotLeadStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery),
                    SiteTheme::homepageSlotCategoryIds($activeTheme, 'slot_01', $businessSettings),
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_01', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_01', $businessSettings),
                );
                if ($slotLeadStories->isEmpty()) {
                    $slotLeadStories = $fallbackLeadStories->values();
                }

                $slotSecondaryStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery),
                    SiteTheme::homepageSlotCategoryIds($activeTheme, 'slot_02', $businessSettings),
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_02', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_02', $businessSettings),
                );
                if ($slotSecondaryStories->isEmpty()) {
                    $slotSecondaryStories = $fallbackSecondaryStories->values();
                }

                $slotLatestStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery),
                    SiteTheme::homepageSlotCategoryIds($activeTheme, 'slot_03', $businessSettings),
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_03', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_03', $businessSettings),
                );
                if ($slotLatestStories->isEmpty()) {
                    $slotLatestStories = $fallbackLatestStories->values();
                }

                $usedHomeIds = collect([
                    ...$slotLeadStories->pluck('id')->all(),
                    ...$slotSecondaryStories->pluck('id')->all(),
                    ...$slotLatestStories->pluck('id')->all(),
                ])->filter()->unique()->values()->all();

                $slotFeatureGroupStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery)->whereNotIn('id', $usedHomeIds),
                    $primaryFeatureGroupCategoryIds,
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_04', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_04', $businessSettings),
                );
                if (! $slot04HasExplicitConfig && $slotFeatureGroupStories->isEmpty() && filled($fallbackCultureCategoryId)) {
                    $slotFeatureGroupStories = $this->homepageStoriesForCategories(
                        (clone $contentPostsQuery)->whereNotIn('id', $usedHomeIds),
                        [(int) $fallbackCultureCategoryId],
                        SiteTheme::homepageSlotLimit($activeTheme, 'slot_04', $businessSettings),
                        SiteTheme::homepageSlotSort($activeTheme, 'slot_04', $businessSettings),
                    )->values();
                }

                $usedAfterCultureIds = collect([
                    ...$usedHomeIds,
                    ...$slotFeatureGroupStories->pluck('id')->all(),
                ])->filter()->unique()->values()->all();

                $slotDesignGroupStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery)->whereNotIn('id', $usedAfterCultureIds),
                    $secondaryFeatureGroupCategoryIds,
                    4,
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_04', $businessSettings),
                );
                if (! $slot04HasExplicitConfig && $slotDesignGroupStories->isEmpty() && filled($fallbackDesignCategoryId)) {
                    $slotDesignGroupStories = $this->homepageStoriesForCategories(
                        (clone $contentPostsQuery)->whereNotIn('id', $usedAfterCultureIds),
                        [(int) $fallbackDesignCategoryId],
                        4,
                        'latest',
                    )->values();
                }

                $usedAfterGroupsIds = collect([
                    ...$usedAfterCultureIds,
                    ...$slotDesignGroupStories->pluck('id')->all(),
                ])->filter()->unique()->values()->all();

                $slotInspirationStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery)->whereNotIn('id', $usedAfterGroupsIds),
                    SiteTheme::homepageSlotCategoryIds($activeTheme, 'slot_06', $businessSettings),
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_06', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_06', $businessSettings),
                );
                if ($slotInspirationStories->isEmpty()) {
                    $slotInspirationStories = $fallbackInspirationStories
                        ->reject(fn (Post $post): bool => in_array($post->id, $usedAfterGroupsIds, true))
                        ->values();
                }

                $usedAfterInspirationIds = collect([
                    ...$usedAfterGroupsIds,
                    ...$slotInspirationStories->pluck('id')->all(),
                ])->filter()->unique()->values()->all();

                $slotReadMoreStories = $this->homepageStoriesForCategories(
                    (clone $contentPostsQuery)->whereNotIn('id', $usedAfterInspirationIds),
                    SiteTheme::homepageSlotCategoryIds($activeTheme, 'slot_07', $businessSettings),
                    SiteTheme::homepageSlotLimit($activeTheme, 'slot_07', $businessSettings),
                    SiteTheme::homepageSlotSort($activeTheme, 'slot_07', $businessSettings),
                );
                if ($slotReadMoreStories->isEmpty()) {
                    $slotReadMoreStories = $fallbackReadMoreStories
                        ->reject(fn (Post $post): bool => in_array($post->id, $usedAfterInspirationIds, true))
                        ->values();
                }

                $themeHomepageContent = match ($activeTheme) {
                    'xiaofang' => [
                        'slot_limits' => [
                            'slot_03' => SiteTheme::homepageSlotLimit($activeTheme, 'slot_03', $businessSettings),
                        ],
                        'top_lead_stories' => $slotLeadStories,
                        'top_secondary_stories' => $slotSecondaryStories,
                        'latest_rail_stories' => $slotLatestStories,
                        'feature_group_stories' => $slotFeatureGroupStories,
                        'design_group_stories' => $slotDesignGroupStories,
                        'inspiration_stories' => $slotInspirationStories,
                        'read_more_stories' => $slotReadMoreStories,
                    ],
                    default => [],
                };

                return compact(
                    'leadPost',
                    'featuredPosts',
                    'flashPosts',
                    'categories',
                    'tags',
                    'metrics',
                    'featuredEvents',
                    'featuredProducts',
                    'themeHomepageContent',
                );
            }
        );

        $homeContent = [
            'sections_eyebrow' => data_get($settings->business_settings, 'home_sections_eyebrow', 'Sections'),
            'sections_title' => data_get($settings->business_settings, 'home_sections_title', '栏目导航'),
            'sections_cta' => data_get($settings->business_settings, 'home_sections_cta', '进入内容检索'),
            'latest_eyebrow' => data_get($settings->business_settings, 'home_latest_eyebrow', 'Latest Content'),
            'latest_title' => data_get($settings->business_settings, 'home_latest_title', '最新内容'),
            'tags_eyebrow' => data_get($settings->business_settings, 'home_tags_eyebrow', 'Topics'),
            'tags_title' => data_get($settings->business_settings, 'home_tags_title', '热门标签'),
            'flash_eyebrow' => data_get($settings->business_settings, 'home_flash_eyebrow', 'Flash'),
            'flash_title' => data_get($settings->business_settings, 'home_flash_title', '快讯与更新'),
            'roadmap_eyebrow' => data_get($settings->business_settings, 'home_roadmap_eyebrow', 'Roadmap'),
            'roadmap_title' => data_get($settings->business_settings, 'home_roadmap_title', '会员、活动与商店'),
            'membership_title' => data_get($settings->member_settings, 'home_membership_title', '会员与订阅'),
            'membership_copy' => data_get($settings->member_settings, 'free_access_copy', '免费会员与多付费层将服务内容、活动和商店权益。'),
            'events_title' => data_get($settings->business_settings, 'home_events_title', '活动系统'),
            'events_copy' => data_get($settings->business_settings, 'home_events_copy', '免费活动和付费活动都将共用统一的报名与支付底座。'),
            'shop_title' => data_get($settings->business_settings, 'home_shop_title', '商店系统'),
            'shop_copy' => data_get($settings->business_settings, 'home_shop_copy', '商店会优先保持通用商品底座，并为未来实体商品扩展预留空间。'),
        ];

        $xiaofangHomepage = $activeTheme === 'xiaofang'
            ? app(XiaofangHomepageBuilder::class)->build(
                $homepagePayload['leadPost'],
                $homepagePayload['featuredPosts'],
                $homepagePayload['themeHomepageContent'],
            )
            : [];

        return view(SiteTheme::view('pages.home', 'themes.default.pages.home'), [
            'leadPost' => $homepagePayload['leadPost'],
            'featuredPosts' => $homepagePayload['featuredPosts'],
            'flashPosts' => $homepagePayload['flashPosts'],
            'categories' => $homepagePayload['categories'],
            'tags' => $homepagePayload['tags'],
            'metrics' => $homepagePayload['metrics'],
            'featuredEvents' => $homepagePayload['featuredEvents'],
            'featuredProducts' => $homepagePayload['featuredProducts'],
            'homeContent' => $homeContent,
            'themeHomepageContent' => $homepagePayload['themeHomepageContent'],
            'xiaofangHomepage' => $xiaofangHomepage,
            'heroLeadPost' => $xiaofangHomepage['lead_post'] ?? null,
            'stripStory' => $xiaofangHomepage['strip_story'] ?? null,
            'editorialStories' => collect($xiaofangHomepage['editorial_stories'] ?? []),
            'latestList' => collect($xiaofangHomepage['latest_stories'] ?? []),
            'groupStories' => collect($xiaofangHomepage['feature_group_stories'] ?? []),
            'groupLead' => $xiaofangHomepage['feature_group_lead'] ?? null,
            'groupItems' => collect($xiaofangHomepage['feature_group_items'] ?? []),
            'groupLabel' => $xiaofangHomepage['feature_group_label'] ?? '专题组',
            'designGroupStories' => collect($xiaofangHomepage['design_group_stories'] ?? []),
            'designGroupLead' => $xiaofangHomepage['design_group_lead'] ?? null,
            'designGroupItems' => collect($xiaofangHomepage['design_group_items'] ?? []),
            'designGroupLabel' => $xiaofangHomepage['design_group_label'] ?? '设计组',
            'inspirationStories' => collect($xiaofangHomepage['inspiration_stories'] ?? []),
            'readMoreStories' => collect($xiaofangHomepage['read_more_stories'] ?? []),
        ]);
    }

    protected function homepageStoriesForCategories(Builder $query, array $categoryIds, int $limit, string $sort = 'latest'): Collection
    {
        $categoryIds = collect($categoryIds)
            ->filter(fn ($id): bool => filled($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        $query->whereIn('category_id', $categoryIds->all());

        match ($sort) {
            'oldest' => $query->oldest('published_at'),
            'featured_first' => $query
                ->orderByDesc('is_featured')
                ->latest('published_at'),
            'recommended_first' => $query
                ->orderByDesc('is_recommended')
                ->latest('published_at'),
            default => $query->latest('published_at'),
        };

        return $query
            ->limit(max(1, min(20, $limit)))
            ->get()
            ->values();
    }

    protected function firstMatchingCategoryId(array $conditions): ?int
    {
        foreach ($conditions as $condition) {
            $query = Category::query();

            foreach ($condition as $column => $value) {
                $query->where($column, $value);
            }

            $id = $query->value('id');

            if (filled($id)) {
                return (int) $id;
            }
        }

        return null;
    }
}
