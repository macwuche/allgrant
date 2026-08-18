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
        $user = auth()->user()->load([
            'grant.plan',
        ]);

        $transactions = Transaction::where('user_id', $user->id);

        $recentTransactions = $transactions->latest()->take(5)->get();

        $referral = $user->getReferrals()->first();

        $grantPlans = GrantPlan::activeCached();

        $heroGrantPlan = $grantPlans->firstWhere('featured', 1) ?? $grantPlans->sortByDesc('maximum_amount')->first();

        $userGrantsByPlan = $user->grant->sortByDesc('created_at')->groupBy('grant_plan_id')->map->first();

        $adSlides = AdSlider::active()->orderBy('position')->get();

        $dataCount = [
            'total_transaction' => $transactions->count(),
            'total_deposit' => $user->totalDeposit(),
            'total_profit' => $user->totalProfit(),
            'profit_last_7_days' => $user->totalProfit(7),
            'total_withdraw' => $user->totalWithdraw(),
            'total_transfer' => $user->totalTransfer(),
            'total_bill' => $user->bill->count(),
            'total_running_grant' => $user->grant->whereIn('status', [GrantStatus::Running, GrantStatus::Due])->count(),
            'total_grant' => $user->grant->count(),
            'total_referral_profit' => $user->totalReferralProfit(),
            'total_referral' => $referral?->relationships()->count() ?? 0,
            'deposit_bonus' => $user->totalDepositBonus(),
            'portfolio_achieved' => $user->portfolioAchieved(),
            'total_tickets' => $user->ticket->count(),
            'recentTransactions' => $recentTransactions,
            'user' => $user,
            'total_grant_amount' => $user->grant->whereIn('status', [GrantStatus::Running, GrantStatus::Due])->sum('total_grant_amount'),
            'grantPlans' => $grantPlans,
            'heroGrantPlan' => $heroGrantPlan,
            'userGrantsByPlan' => $userGrantsByPlan,
            'adSlides' => $adSlides,
        ];

        return view('frontend::user.dashboard', $dataCount);
    }
}
