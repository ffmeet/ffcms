<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MemberActivityController extends Controller
{
    public function center(Request $request): View
    {
        $user = $request->user();

        return view('site.member.activities.center', [
            'user' => $user,
            'activitySummary' => $this->buildActivitySummary($user),
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('site.member.activities.index', [
            'user' => $user,
            'activitySummary' => $this->buildActivitySummary($user),
            'timeline' => $this->buildTimeline($user),
        ]);
    }

    protected function buildActivitySummary($user): array
    {
        return [
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'pending_posts' => $user->posts()->where('status', 'pending')->count(),
            'draft_posts' => $user->posts()->where('status', 'draft')->count(),
            'approved_comments' => $user->comments()->where('status', 'approved')->count(),
        ];
    }

    protected function buildTimeline($user): Collection
    {
        $postTimeline = $user->posts()
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(fn ($post) => [
                'type' => '稿件',
                'title' => $post->title,
                'description' => $post->status === 'published' ? '稿件已发布到前台。' : ($post->status === 'pending' ? '稿件已提交审核。' : '稿件保存在草稿箱。'),
                'time' => optional($post->updated_at)->format('Y-m-d H:i'),
                'badge' => $post->status === 'published' ? '已发布' : ($post->status === 'pending' ? '待审核' : '草稿'),
            ]);

        $commentTimeline = $user->comments()
            ->with('post')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn ($comment) => [
                'type' => '评论',
                'title' => $comment->post?->title ?? '评论动态',
                'description' => mb_strimwidth($comment->content, 0, 64, '...'),
                'time' => optional($comment->created_at)->format('Y-m-d H:i'),
                'badge' => $comment->status === 'approved' ? '已通过' : '待审核',
            ]);

        return $postTimeline
            ->concat($commentTimeline)
            ->sortByDesc('time')
            ->take(10)
            ->values();
    }
}
