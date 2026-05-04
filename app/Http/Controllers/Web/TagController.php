<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Support\SiteTheme;
use Illuminate\Contracts\View\View;

class TagController extends Controller
{
    public function __invoke(string $slug): View
    {
        $tag = Tag::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = $tag->posts()
            ->with(['category', 'contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
            ->published()
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $trendingTags = Tag::query()
            ->orderByDesc('count')
            ->orderBy('name')
            ->limit(12)
            ->get();

        $featuredCategories = \App\Models\Category::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view(SiteTheme::view('pages.tag-show', 'themes.default.pages.tag-show'), [
            'tag' => $tag,
            'posts' => $posts,
            'trendingTags' => $trendingTags,
            'featuredCategories' => $featuredCategories,
        ]);
    }
}
