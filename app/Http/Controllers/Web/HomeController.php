<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $posts = Post::query()
            ->with(['category', 'contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
            ->published()
            ->latest('published_at')
            ->limit(10)
            ->get();

        $categories = Category::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $tags = Tag::query()
            ->orderByDesc('count')
            ->orderBy('name')
            ->limit(12)
            ->get();

        return view('site.home', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }
}
