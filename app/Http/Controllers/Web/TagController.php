<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tag;
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

        return view('site.tag.show', [
            'tag' => $tag,
            'posts' => $posts,
        ]);
    }
}
