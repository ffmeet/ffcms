<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        $posts = Post::query()
            ->with(['category', 'contentModel', 'user', 'statistics', 'detail', 'coverMediaFiles.media'])
            ->published()
            ->when(
                filled($query),
                fn ($builder) => $builder->where(function ($search) use ($query): void {
                    $search
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%")
                        ->orWhereHas('detail', fn ($detail) => $detail->where('content', 'like', "%{$query}%"))
                        ->orWhereHas('tags', fn ($tags) => $tags->where('name', 'like', "%{$query}%")->orWhere('slug', 'like', "%{$query}%"));
                }),
            )
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('site.search', [
            'query' => $query,
            'posts' => $posts,
        ]);
    }
}
