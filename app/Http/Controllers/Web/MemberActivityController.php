<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SiteTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MemberActivityController extends Controller
{
    public function center(Request $request): View
    {
        $user = $request->user();

        return view(SiteTheme::view('member.activities-center', 'themes.default.member.activities-center'), [
            'user' => $user,
            'activitySummary' => $this->buildActivitySummary($user),
            'upcomingRegistrations' => $this->upcomingRegistrations($user),
            'recentOrders' => $this->recentOrders($user),
            'recentSubscriptions' => $this->recentSubscriptions($user),
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view(SiteTheme::view('member.activities-index', 'themes.default.member.activities-index'), [
            'user' => $user,
            'activitySummary' => $this->buildActivitySummary($user),
            'registrations' => $user->eventRegistrations()
                ->with(['event', 'order.payments'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'recentOrders' => $this->recentOrders($user, 8),
            'recentSubscriptions' => $this->recentSubscriptions($user, 6),
        ]);
    }

    protected function buildActivitySummary($user): array
    {
        return [
            'event_registrations' => $user->eventRegistrations()->count(),
            'pending_registrations' => $user->eventRegistrations()->where('status', 'pending')->count(),
            'upcoming_events' => $user->eventRegistrations()
                ->whereIn('status', ['pending', 'approved'])
                ->whereHas('event', fn ($query) => $query->whereNotNull('starts_at')->where('starts_at', '>=', now()))
                ->count(),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'active_subscriptions' => $user->subscriptions()->where('status', 'active')->count(),
            'pending_subscriptions' => $user->subscriptions()->where('status', 'pending')->count(),
        ];
    }

    protected function upcomingRegistrations($user, int $limit = 4): Collection
    {
        return $user->eventRegistrations()
            ->with('event')
            ->whereIn('status', ['pending', 'approved'])
            ->whereHas('event', fn ($query) => $query->whereNotNull('starts_at')->where('starts_at', '>=', now()))
            ->latest()
            ->limit($limit)
            ->get();
    }

    protected function recentOrders($user, int $limit = 6): Collection
    {
        return $user->orders()
            ->with('payments')
            ->latest()
            ->limit($limit)
            ->get();
    }

    protected function recentSubscriptions($user, int $limit = 4): Collection
    {
        return $user->subscriptions()
            ->with(['plan', 'lastOrder'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
