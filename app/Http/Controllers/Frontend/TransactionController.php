<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Export\CSV\TransactionExport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function transactions(Request $request)
    {
        $transactions = $this->getTransactionData();
        $queries = $request->query();

        return view('frontend::user.transaction.index', compact('transactions', 'queries'));
    }

    public function getTransactionData($export = false)
    {
        $from_date = trim(@explode('-', request('daterange'))[0]);
        $to_date = trim(@explode('-', request('daterange'))[1]);

        $types = match (request('type')) {
            'loan' => [TxnType::Loan->value, TxnType::LoanApply, TxnType::LoanInstallment],
            'fdr' => [TxnType::FdrIncrease, TxnType::FdrDecrease, TxnType::FdrInstallment, TxnType::FdrMaturityFee],
            'dps' => [TxnType::DpsIncrease, TxnType::DpsDecrease, TxnType::DpsInstallment, TxnType::DpsMaturity],
            default => request('type')
        };

        $transactions = Transaction::where('user_id', auth()->id())
            ->with('userWallet')
            ->search(request('trx'))
            ->when(request('daterange'), function ($query) use ($from_date, $to_date) {
                $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
            })
            ->when(request('type') && request('type') !== 'all', function ($query) use ($types) {
                if (is_array($types)) {
                    $query->whereIn('type', $types);
                } else {
                    $query->where('type', $types);
                }
            })
            ->latest();
        if ($export) {
            return $transactions->take(request('limit', 15))->get();
        }
        $transactions = $transactions->paginate(request('limit', 15))
            ->withQueryString();

        return $transactions;
    }

    public function transactionExportCSV()
    {
        $transactions = $this->getTransactionData(true);

        return (new TransactionExport($transactions))->download('transactions.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function generateBankStatement(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        $from_date = Carbon::parse($request->from_date)->startOfDay();
        $to_date   = Carbon::parse($request->to_date)->endOfDay();

        $user = auth()->user();

        $types = [
            TxnType::Deposit,
            TxnType::ManualDeposit,
            TxnType::Withdraw,
            TxnType::WithdrawAuto
        ];

        $openingDeposits = Transaction::where('user_id', $user->id)
            ->whereIn('type', [TxnType::Deposit, TxnType::ManualDeposit])
            ->where('created_at', '<', $from_date)
            ->sum('amount');

        $openingWithdrawals = Transaction::where('user_id', $user->id)
            ->whereIn('type', [TxnType::Withdraw, TxnType::WithdrawAuto])
            ->where('created_at', '<', $from_date)
            ->sum('amount');

        $openingBalance = $openingDeposits - $openingWithdrawals;

        $transactions = Transaction::where('user_id', $user->id)
            ->whereIn('type', $types)
            ->whereBetween('created_at', [$from_date, $to_date])
            ->orderBy('created_at', 'asc')
            ->get();

        $currentBalance = $openingBalance;
        foreach ($transactions as $transaction) {
            $transaction->is_deposit = isPlusTransaction($transaction->type);
            $currentBalance += $transaction->is_deposit ? $transaction->amount : -$transaction->amount;
            $transaction->running_balance = $currentBalance;
            $transaction->date_formatted = Carbon::parse($transaction->created_at)->format('d M Y');
        }

        $totalDeposits = $transactions->where('is_deposit', true)->sum('amount');
        $totalWithdrawals = $transactions->where('is_deposit', false)->sum('amount');
        $closingBalance = $openingBalance + $totalDeposits - $totalWithdrawals;

        $data = [
            'user'              => $user,
            'from_date'         => $from_date->format('d M Y'),
            'to_date'           => $to_date->format('d M Y'),
            'opening_balance'   => $openingBalance,
            'total_deposits'    => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'closing_balance'   => $closingBalance,
            'transactions'      => $transactions,
            'currency'          => setting('site_currency', 'USD'),
        ];

        $mpdf = new \Mpdf\Mpdf(['default_font' => 'dejavusans']);
        $mpdf->SetBasePath(base_path());
        $mpdf->SetTitle(__('Bank Statement'));
        $mpdf->WriteHTML(view('frontend::user.transaction.bank-statement-pdf', $data)->render());
        $mpdf->Output('bank-statement.pdf', \Mpdf\Output\Destination::INLINE);
    }
}
