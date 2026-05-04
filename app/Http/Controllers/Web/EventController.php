<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\SiteTheme;
use Illuminate\Contracts\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->with('memberGroup')
            ->whereIn('status', ['registration-open', 'sold-out', 'finished'])
            ->orderBy('starts_at')
            ->paginate(12)
            ->withQueryString();

        $highlightedEvents = Event::query()
            ->with('memberGroup')
            ->whereIn('status', ['registration-open', 'sold-out'])
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return view(SiteTheme::view('pages.events-index', 'themes.default.pages.events-index'), [
            'events' => $events,
            'highlightedEvents' => $highlightedEvents,
            'eventMetrics' => [
                'all_events' => Event::query()->whereIn('status', ['registration-open', 'sold-out', 'finished'])->count(),
                'open_events' => Event::query()->where('status', 'registration-open')->count(),
                'paid_events' => Event::query()->whereIn('status', ['registration-open', 'sold-out', 'finished'])->where('is_paid', true)->count(),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $event = Event::query()
            ->with(['memberGroup'])
            ->where('slug', $slug)
            ->whereIn('status', ['registration-open', 'sold-out', 'finished'])
            ->firstOrFail();

        $relatedEvents = Event::query()
            ->with('memberGroup')
            ->whereIn('status', ['registration-open', 'sold-out', 'finished'])
            ->whereKeyNot($event->id)
            ->orderBy('starts_at')
            ->limit(4)
            ->get();

        return view(SiteTheme::view('pages.events-show', 'themes.default.pages.events-show'), [
            'event' => $event,
            'relatedEvents' => $relatedEvents,
        ]);
    }
}
