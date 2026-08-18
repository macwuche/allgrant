<?php

namespace App\Http\Controllers\Api;

use App\Enums\GrantStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Export\CSV\TransactionExport;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Models\Transaction;
use App\Models\UserWallet;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $wallets = UserWallet::with('currency')->where('user_id', auth()->id())->get();
        $currency_symbol = setting('currency_symbol');
        $user = auth()->user();

        return response()->json([
            'status' => true,
            'data' => [
                'greeting' => grettings(),
                'user_name' => $user->full_name,
                'portfolio' => [
                    'name' => $user->portfolio?->portfolio_name,
                    'icon' => asset($user->portfolio?->icon),
                ],
                'portfolio_showable' => (bool) setting('user_portfolio', 'permission') && $user->portfolio,
                'earn_text' => __('Earn :amount', ['amount' => $currency_symbol.setting('referral_bonus', 'fee')]),
                'wallets' => WalletResource::collectionWithDefault($wallets, $user),
                'grant_data' => [
                    'total_running_grant_amount' => $currency_symbol.$user->grant->whereIn('status', [GrantStatus::Running, GrantStatus::Due])->sum('total_grant_amount'),
                    'running_grant_summary' => $user->grant->whereIn('status', [GrantStatus::Running, GrantStatus::Due])->map(function ($grant) {
                        return [
                            'name' => $grant->plan?->name,
                            'end_date' => now()->parse($grant->last_date)->format('d M Y'),
                        ];
                    })->values(),
                ],
                'statistics' => [
                    'total_transactions' => $user->transaction()->count(),
                    'total_deposit' => $currency_symbol.$user->total_deposit,
                    'total_transfer' => $currency_symbol.$user->totalTransfer(),
                    'total_pay_bill' => $user->bill->count(),
                    'total_referral_profit' => $currency_symbol.$user->totalReferralProfit(),
                ],
                'transactions' => TransactionResource::collection($user->transaction()->latest()->take(5)->get()),
            ],
        ]);
    }

    public function transactions()
    {
        $transactions = Transaction::with('userWallet')->where('user_id', auth()->id())->when(request()->has('type'), function ($query) {
            $types = collect(explode(',', request('type')))->map(function ($type) {
                return str()->of($type)->snake()->lower()->value();
            })->toArray();
            $query->whereIn('type', $types);
        })->when(request()->has('transaction_id'), function ($query) {
            $query->where('tnx', 'LIKE', '%'.request('transaction_id').'%');
        })->when(request()->has(['from_date', 'to_date']), function ($query) {
            $query->whereDate('created_at', '>=', request('from_date'))
                ->whereDate('created_at', '<=', request('to_date'));
        })->latest();

        if (request()->has('export')) {
            return $this->exportTransactions($transactions->get());
        }

        $transactions = $transactions->paginate()->withQueryString();

        return TransactionResource::collection($transactions);
    }

    public function statistics()
    {
        $user = auth()->user();
        $currency_symbol = setting('currency_symbol');

        return response()->json([
            'status' => true,
            'data' => [
                'all_transactions' => $user->transaction()->count(),
                'total_deposit' => $currency_symbol.$user->total_deposit,
                'total_withdraw' => $currency_symbol.$user->totalWithdraw(),
                'total_transfer' => $currency_symbol.$user->totalTransfer(),
                'total_grant' => $currency_symbol.$user->grant->sum('amount'),
                'total_bill' => $currency_symbol.$user->bill->sum('amount'),
                'total_referral_profit' => $currency_symbol.$user->totalReferralProfit(),
                'total_referral' => $user->referrals()->count(),
                'deposit_bonus' => $currency_symbol.$user->totalDepositBonus(),
                'portfolio_achieved' => $user->portfolioAchieved(),
                'total_tickets' => $user->ticket->count(),
                'points' => $user->points,
            ],
        ]);
    }

    public function markNotification()
    {
        auth()->user()->notifications()->update(['read' => true]);

        return response()->json([
            'status' => true,
            'message' => __('All Notifications marked as read'),
        ]);
    }

    public function exportTransactions($transactions)
    {
        return (new TransactionExport($transactions))->download('transactions.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
