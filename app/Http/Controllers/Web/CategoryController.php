<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Support\SiteTheme;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __invoke(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = Post::query()
            ->with(['contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
            ->where('category_id', $category->id)
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $relatedCategories = Category::query()
            ->withCount('posts')
            ->whereKeyNot($category->id)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $trendingTags = \App\Models\Tag::query()
            ->orderByDesc('count')
            ->orderBy('name')
            ->limit(12)
            ->get();

        return view(SiteTheme::view('pages.category-show', 'themes.default.pages.category-show'), [
            'category' => $category,
            'posts' => $posts,
            'relatedCategories' => $relatedCategories,
            'trendingTags' => $trendingTags,
        ]);
    }
}
