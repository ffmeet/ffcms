<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Support\MemberOperationsSummary;
use App\Support\SiteTheme;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $recentPosts = Post::query()
            ->with('category')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(8)
            ->get();

        return view(SiteTheme::view('member.dashboard', 'themes.default.member.dashboard'), [
            'user' => $user,
            'recentPosts' => $recentPosts,
            'recentComments' => Comment::query()
                ->with('post')
                ->where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
            'memberMetrics' => MemberOperationsSummary::dashboardMetrics($user),
            'attentionCards' => MemberOperationsSummary::attentionCards($user),
        ]);
    }
}
