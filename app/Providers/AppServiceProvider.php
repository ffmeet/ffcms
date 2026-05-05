<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Post;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Models\User;
use App\Support\RouteRuleManager;
use App\Support\FrontendCache;
use App\Support\SiteTheme;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Slimani\MediaManager\Models\File;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('media-browser', \App\Livewire\MediaBrowser::class);

        View::composer('themes.*', function ($view): void {
            $siteSettings = Schema::hasTable('site_settings')
                ? SiteSetting::current()
                : SiteSetting::make(SiteSetting::defaults());

            $view->with('siteSettings', $siteSettings);
            $view->with('currentSiteTheme', SiteTheme::current());
            $view->with('publicRouteEntries', RouteRuleManager::publicEntries($siteSettings->toArray()));
            $view->with('memberRouteEntries', RouteRuleManager::memberEntries($siteSettings->toArray()));
            $view->with('xiaofangNavigationFeed', $this->xiaofangNavigationFeed());
            $view->with('xiaofangHotTopics', $this->xiaofangHotTopics());
            $view->with('xiaofangLatestEvents', $this->xiaofangLatestEvents());
            $view->with('xiaofangFeaturedAuthors', $this->xiaofangFeaturedAuthors());
            $view->with('xiaofangHeaderSnapshot', [
                'weekday' => now()->translatedFormat('l'),
                'date' => now()->translatedFormat('M j, Y'),
                'headline' => '品牌联动改版中',
                'location' => 'Shanghai Studio',
            ]);
        });

        File::registerMediaConversionsUsing(function (File $file, ?Media $media = null): void {
            $file->addMediaConversion('thumb')
                ->fit(Fit::Crop, 320, 320)
                ->sharpen(10)
                ->nonQueued();

            $file->addMediaConversion('preview')
                ->width(960)
                ->height(960)
                ->nonQueued();
        });

        foreach ([Post::class, Category::class, Tag::class, Event::class, Product::class, User::class] as $modelClass) {
            $modelClass::saved(fn (): int => FrontendCache::flushAll());
            $modelClass::deleted(fn (): int => FrontendCache::flushAll());
        }
    }

    protected function xiaofangNavigationFeed(): Collection
    {
        return Cache::remember(FrontendCache::key('xiaofang.navigation-feed'), now()->addMinutes(10), function (): Collection {
            if (! Schema::hasTable('posts')) {
                return collect();
            }

            $posts = Post::query()
                ->with('category')
                ->published()
                ->latest('published_at')
                ->limit(5)
                ->get()
                ->map(fn (Post $post): array => [
                    'type' => 'post',
                    'label' => '最新文章',
                    'title' => $post->title,
                    'excerpt' => $post->summary ?: '来自内容流的最新文章更新。',
                    'url' => route('posts.show', $post->slug),
                    'image_url' => $post->cover_image_url,
                    'meta' => optional($post->published_at)->format('m-d H:i') ?: '刚刚更新',
                    'accent' => 'article',
                ]);

            $events = Schema::hasTable('events')
                ? Event::query()
                    ->latest('starts_at')
                    ->limit(4)
                    ->get()
                    ->map(fn (Event $event): array => [
                        'type' => 'event',
                        'label' => '最新活动',
                        'title' => $event->title,
                        'excerpt' => $event->summary ?: '活动、报名与会员优先权正在同步推进。',
                        'url' => route('events.show', $event->slug),
                        'image_url' => $event->cover_image_url,
                        'meta' => $event->starts_at?->format('m-d H:i') ?: '活动更新',
                        'accent' => 'event',
                    ])
                : collect();

            $products = Schema::hasTable('products')
                ? Product::query()
                    ->where('status', 'published')
                    ->latest('published_at')
                    ->limit(4)
                    ->get()
                    ->map(fn (Product $product): array => [
                        'type' => 'product',
                        'label' => '最新商品',
                        'title' => $product->title,
                        'excerpt' => $product->summary ?: '商品、订阅与权益正在共用一套商业化底座。',
                        'url' => route('shop.show', $product->slug),
                        'image_url' => $product->cover_image_url,
                        'meta' => '¥'.number_format((float) $product->price, 2),
                        'accent' => 'product',
                    ])
                : collect();

            return $posts
                ->concat($events)
                ->concat($products)
                ->take(9)
                ->values()
                ->map(function (array $item, int $index): array {
                    $item['rank'] = $index + 1;

                    return $item;
                });
        });
    }

    protected function xiaofangHotTopics(): Collection
    {
        return Cache::remember(FrontendCache::key('xiaofang.hot-topics'), now()->addMinutes(10), function (): Collection {
            $tags = Schema::hasTable('tags')
                ? Tag::query()
                    ->orderByDesc('count')
                    ->orderBy('name')
                    ->limit(6)
                    ->pluck('name')
                : collect();

            $categories = Schema::hasTable('categories')
                ? Category::query()
                    ->orderBy('sort_order')
                    ->limit(4)
                    ->pluck('name')
                : collect();

            return $tags
                ->concat($categories)
                ->filter()
                ->unique()
                ->take(10)
                ->values();
        });
    }

    protected function xiaofangLatestEvents(): Collection
    {
        return Cache::remember(FrontendCache::key('xiaofang.latest-events'), now()->addMinutes(10), function (): Collection {
            if (! Schema::hasTable('events')) {
                return collect();
            }

            return Event::query()
                ->whereIn('status', ['registration-open', 'sold-out', 'finished'])
                ->orderByRaw("case when status = 'registration-open' then 0 when status = 'sold-out' then 1 else 2 end")
                ->orderBy('starts_at')
                ->limit(6)
                ->get()
                ->map(fn (Event $event): array => [
                    'title' => $event->title,
                    'summary' => $event->summary ?: '最新活动正在更新中。',
                    'url' => route('events.show', $event->slug),
                    'location' => $event->location ?: '线上活动',
                    'status' => $event->status,
                    'starts_at' => $event->starts_at,
                    'is_paid' => $event->is_paid,
                    'price' => $event->price,
                ]);
        });
    }

    protected function xiaofangFeaturedAuthors(): Collection
    {
        return Cache::remember(FrontendCache::key('xiaofang.featured-authors'), now()->addMinutes(10), function (): Collection {
            if (! Schema::hasTable('users') || ! Schema::hasTable('posts')) {
                return collect();
            }

            return User::query()
                ->with(['memberGroup'])
                ->withCount([
                    'posts as published_posts_count' => fn ($query) => $query->published(),
                ])
                ->whereHas('posts', fn ($query) => $query->published())
                ->orderByDesc('published_posts_count')
                ->orderBy('username')
                ->limit(8)
                ->get()
                ->map(fn (User $user): array => [
                    'username' => $user->username,
                    'display_name' => $user->public_display_name,
                    'headline' => $user->author_headline,
                    'bio' => $user->author_bio,
                    'avatar_url' => $user->avatarUrl('medium') ?: $user->avatarUrl('small'),
                    'posts_count' => $user->published_posts_count,
                    'url' => route('authors.show', $user->username),
                ]);
        });
    }
}
