<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\MemberOperationsSummary;
use App\Support\SiteTheme;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view(SiteTheme::view('member.subscriptions-index', 'themes.default.member.subscriptions-index'), [
            'subscriptions' => $user->subscriptions()
                ->with(['plan', 'lastOrder'])
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'subscriptionSummaryCards' => MemberOperationsSummary::subscriptionSummary($user),
            'attentionCards' => MemberOperationsSummary::attentionCards($user),
        ]);
    }
}
