<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Support\SiteTheme;
use Illuminate\Contracts\View\View;

class AuthorController extends Controller
{
    public function show(string $username): View
    {
        $author = User::query()
            ->with(['memberGroup'])
            ->where('username', $username)
            ->firstOrFail();

        $featuredPosts = Post::query()
            ->with(['category', 'detail', 'statistics', 'coverMediaFiles.media'])
            ->where('user_id', $author->id)
            ->published()
            ->latest('published_at')
            ->limit(2)
            ->get();

        $authorPosts = Post::query()
            ->with(['category', 'detail', 'statistics', 'coverMediaFiles.media'])
            ->where('user_id', $author->id)
            ->published()
            ->when($featuredPosts->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $featuredPosts->pluck('id')))
            ->latest('published_at')
            ->paginate(8)
            ->withQueryString();

        return view(SiteTheme::view('pages.author-show', 'themes.default.pages.author-show'), [
            'author' => $author,
            'featuredPosts' => $featuredPosts,
            'authorPosts' => $authorPosts,
            'publishedPostsCount' => Post::query()
                ->where('user_id', $author->id)
                ->published()
                ->count(),
        ]);
    }
}
