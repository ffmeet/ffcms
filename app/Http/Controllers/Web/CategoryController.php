<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
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

        return view('site.category.show', [
            'category' => $category,
            'posts' => $posts,
        ]);
    }
}
