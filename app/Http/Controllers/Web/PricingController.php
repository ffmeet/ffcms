<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Support\SiteTheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class PricingController extends Controller
{
    public function __invoke(): View
    {
        $plans = MembershipPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $activePlanIds = Auth::check()
            ? Auth::user()
                ->subscriptions()
                ->where('status', 'active')
                ->pluck('plan_id')
                ->all()
            : [];

        return view(SiteTheme::view('pages.pricing', 'themes.default.pages.pricing'), [
            'plans' => $plans,
            'activePlanIds' => $activePlanIds,
            'pricingMetrics' => [
                'active_plans' => $plans->count(),
                'monthly_plans' => $plans->where('billing_period', 'monthly')->count(),
                'yearly_plans' => $plans->where('billing_period', 'yearly')->count(),
            ],
        ]);
    }
}
