<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\GrantStatus;
use App\Http\Controllers\Controller;
use App\Models\AdSlider;
use App\Models\GrantPlan;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user()
            ->load(['grant.plan'])
            ->loadCount(['bill', 'ticket']); // was ->bill->count()/->ticket->count(), which pulled every row just to count them

        $grantPlans = GrantPlan::activeCached();

        $heroGrantPlan = $grantPlans->firstWhere('featured', 1) ?? $grantPlans->sortByDesc('maximum_amount')->first();

        $userGrantsByPlan = $user->grant->sortByDesc('created_at')->groupBy('grant_plan_id')->map->first();

        // Shared across every user viewing the dashboard, so one cached copy
        // serves everyone instead of one query per page view.
        $adSlides = cache()->remember('dashboard_ad_slides', 60, fn () => AdSlider::active()->orderBy('position')->get());

        // The seven totalX() SUM queries plus the transaction count/recent-list
        // queries collapsed into this cached block. 15s TTL: short enough that
        // a fresh deposit/withdrawal shows up almost immediately, long enough
        // to absorb repeat dashboard loads (refreshes, nav back-and-forth)
        // without re-hitting the DB every time.
        $stats = cache()->remember("dashboard_stats_{$user->id}", 15, function () use ($user) {
            $summary = $user->transactionSummary(7);

            $transactions = Transaction::where('user_id', $user->id);
            $recentTransactions = $transactions->latest()->take(5)->get();
            $totalTransaction = $transactions->count();

            $referral = $user->getReferrals()->first();
            $totalReferral = $referral?->relationships()->count() ?? 0;

            return $summary + [
                'total_transaction' => $totalTransaction,
                'total_referral' => $totalReferral,
                'recentTransactions' => $recentTransactions,
            ];
        });

        $dataCount = [
            'total_transaction' => $stats['total_transaction'],
            'total_deposit' => $stats['total_deposit'],
            'total_profit' => $stats['total_profit'],
            'profit_last_7_days' => $stats['profit_last_days'],
            'total_withdraw' => $stats['total_withdraw'],
            'total_transfer' => $stats['total_transfer'],
            'total_bill' => $user->bill_count,
            'total_running_grant' => $user->grant->where('status', GrantStatus::Approved)->count(),
            'total_grant' => $user->grant->count(),
            'total_referral_profit' => $stats['total_referral_profit'],
            'total_referral' => $stats['total_referral'],
            'deposit_bonus' => $stats['deposit_bonus'],
            'portfolio_achieved' => $user->portfolioAchieved(),
            'total_tickets' => $user->ticket_count,
            'recentTransactions' => $stats['recentTransactions'],
            'user' => $user,
            'total_grant_amount' => $user->grant->where('status', GrantStatus::Approved)->sum('net_amount'),
            'grantPlans' => $grantPlans,
            'heroGrantPlan' => $heroGrantPlan,
            'userGrantsByPlan' => $userGrantsByPlan,
            'adSlides' => $adSlides,
        ];

        return view('frontend::user.dashboard', $dataCount);
    }
}
