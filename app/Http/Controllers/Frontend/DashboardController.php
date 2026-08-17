<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\DpsStatus;
use App\Enums\FdrStatus;
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
            'dps.plan',
            'fdr.plan',
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
            'total_dps' => $user->dps->count(),
            'total_bill' => $user->bill->count(),
            'total_fdr' => $user->fdr->count(),
            'total_running_dps' => $user->dps->whereIn('status', [DpsStatus::Running, DpsStatus::Due])->count(),
            'total_running_grant' => $user->grant->whereIn('status', [GrantStatus::Running, GrantStatus::Due])->count(),
            'total_running_fdr' => $user->fdr->where('status', FdrStatus::Running)->count(),
            'total_grant' => $user->grant->count(),
            'total_referral_profit' => $user->totalReferralProfit(),
            'total_referral' => $referral?->relationships()->count() ?? 0,
            'deposit_bonus' => $user->totalDepositBonus(),
            'portfolio_achieved' => $user->portfolioAchieved(),
            'total_tickets' => $user->ticket->count(),
            'recentTransactions' => $recentTransactions,
            'user' => $user,
            'dps_mature_amount' => $user->dps->whereIn('status', [DpsStatus::Running, DpsStatus::Due])->sum('total_mature_amount'),
            'fdr_mature_amount' => $user->fdr->where('status', FdrStatus::Running)->sum('total_mature_amount'),
            'total_grant_amount' => $user->grant->whereIn('status', [GrantStatus::Running, GrantStatus::Due])->sum('total_grant_amount'),
            'grantPlans' => $grantPlans,
            'heroGrantPlan' => $heroGrantPlan,
            'userGrantsByPlan' => $userGrantsByPlan,
            'adSlides' => $adSlides,
        ];

        return view('frontend::user.dashboard', $dataCount);
    }
}
