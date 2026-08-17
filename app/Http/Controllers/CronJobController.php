<?php

namespace App\Http\Controllers;

use App\Enums\DpsStatus;
use App\Enums\FdrStatus;
use App\Enums\GrantStatus;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Models\CronJob;
use App\Models\CronJobLog;
use App\Models\DpsTransaction;
use App\Models\FDRTransaction;
use App\Models\GrantTransaction;
use App\Models\Portfolio;
use App\Models\User;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Remotelywork\Installer\Repository\App;
use Txn;

class CronJobController extends Controller
{
    use NotifyTrait;

    public function runCronJobs()
    {

        $action_id = request('run_action');

        // Get running cron jobs
        if (is_null($action_id)) {
            $jobs = CronJob::where('status', 'running')
                ->where('next_run_at', '<', now())
                ->get();
        } else {
            $jobs = CronJob::whereKey($action_id)->get();
        }

        foreach ($jobs as $job) {

            $error = null;

            $log = new CronJobLog;
            $log->cron_job_id = $job->id;
            $log->started_at = now();

            try {

                if ($job->type == 'system') {
                    $this->{$job->reserved_method}();
                } else {
                    Http::withOptions([
                        'verify' => false,
                    ])->get($job->url);
                }
            } catch (\Throwable $th) {
                $error = $th->getMessage();
            }

            $log->ended_at = now();
            $log->error = $error;
            $log->save();

            $job->update([
                'last_run_at' => now(),
                'next_run_at' => now()->addSeconds($job->schedule),
            ]);
        }

        if ($action_id !== null) {
            notify()->success(__('Cron running successfully!'), 'Success');

            return back();
        }
    }

    public function userPortfolio()
    {

        try {

            DB::beginTransaction();
            $this->startCron();

            // Get all active portfolios
            $portfolios = Portfolio::where('status', true)->get();

            // Run portfolio processing
            User::where('status', true)->chunk(500, function ($users) use ($portfolios) {
                foreach ($users as $user) {

                    // Get eligible portfolio
                    $eligiblePortfolios = $portfolios->reject(function ($rank) use ($user) {
                        $totalTransactions = $user->transaction->where('status', TxnStatus::Success)->sum('amount');

                        return is_array(json_decode($user->portfolios)) &&
                            in_array($rank->id, json_decode($user->portfolios)) ||
                            $rank->minimum_transactions > $totalTransactions;
                    });

                    if ($eligiblePortfolios !== null) {

                        // Get eligible portfolios minimum transactions amount
                        $maxPortfolioTransctionsAmount = $eligiblePortfolios->max('minimum_transactions');

                        // Get highest portfolio by max transactions amount
                        $highestPortfolio = $eligiblePortfolios->where('minimum_transactions', $maxPortfolioTransctionsAmount)->first();
                        // Get none portfolio
                        $nonePortfolio = $eligiblePortfolios->where('minimum_transactions', 0)->first();

                        // Distribute portfolio badge and bonus to users
                        foreach ($eligiblePortfolios as $portfolio) {

                            if ($portfolio->bonus > 0) {
                                $user->balance += $portfolio->bonus;
                                $user->save();
                                Txn::new($portfolio->bonus, 0, $portfolio->bonus, 'System', "'".$portfolio->portfolio_name."' Portfolio Bonus", TxnType::PortfolioBonus, TxnStatus::Success, null, null, $user->id);
                            }

                            // Shortcodes
                            $shortcodes = [
                                '[[portfolio_name]]' => $portfolio->portfolio_name,
                                '[[full_name]]' => $user->full_name,
                            ];

                            if ($portfolio->id === $highestPortfolio->id) {

                                $userPortfolios = $user->portfolios != null ? array_merge(json_decode($user->portfolios), [$portfolio->id]) : [$portfolio->id];

                                if ($nonePortfolio != null && ! in_array($nonePortfolio->id, $userPortfolios)) {
                                    $userPortfolios = array_merge($userPortfolios, [$nonePortfolio->id]);
                                }

                                $user->update([
                                    'portfolio_id' => $portfolio->id,
                                    'portfolios' => json_encode($userPortfolios),
                                ]);

                                $this->mailNotify($user->email, 'portfolio_achieve', $shortcodes);
                                $this->pushNotify('portfolio_achieve', $shortcodes, route('user.portfolio'), $user->id);
                            }
                        }
                    }
                }
            });

            DB::commit();

            return '......User portfolio job completed successfully!';
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function queueWork()
    {
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
        ]);
    }

    public function userInactive()
    {
        if (! setting('inactive_account_disabled', 'inactive_user') == 1) {
            return false;
        }

        try {

            DB::beginTransaction();
            $this->startCron();

            User::whereDoesntHave('activities', function ($query) {
                $query->where('created_at', '>', now()->subDays(30));
            })->where('status', 1)->chunk(500, function ($inactiveUsers) {
                foreach ($inactiveUsers as $user) {
                    $user->update(['status' => 0]);
                    $shortcodes = [
                        '[[full_name]]' => $user->full_name,
                        '[[site_title]]' => setting('site_title', 'global'),
                        '[[site_url]]' => route('home'),
                        '[[inactive_days]]' => setting('inactive_days', 'inactive_user'),
                    ];
                    $this->mailNotify($user->email, 'user_account_disabled', $shortcodes);
                }
            });

            DB::commit();

            return '........Inactive users disabled successfully.';
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function dps()
    {

        try {

            DB::beginTransaction();

            // Get today date
            $today = Carbon::today();
            $this->startCron();

            // Get all dps installments by today date and paid installment fee or increment deferment
            DpsTransaction::with('dps.plan', 'dps.user')
                ->where('installment_date', '<=', $today)
                ->where('given_date', null)
                ->whereHas('dps', function ($query) {
                    $query->whereNull('cancel_date')
                        ->whereIn('status', [DpsStatus::Running, DpsStatus::Due]);
                })
                ->chunk(500, function ($dpsTransactions) use ($today) {

                    // Run every installments
                    foreach ($dpsTransactions as $installment) {
                        // Get dps
                        $dps = $installment->dps;
                        // Get plan data
                        $plan = $dps->plan;
                        // Get user data
                        $user = $dps->user;

                        // Calculatate deferement charge
                        if ($installment->deferment != 0 && $installment->deferment >= $plan->delay_days) {
                            $charge = $plan->charge_type == 'percentage' ? (($plan->charge / 100) * $plan->per_installment) : $plan->charge;
                        } else {
                            $charge = 0;
                        }

                        // Shortcodes for notification
                        $shortcodes = [
                            '[[site_title]]' => setting('site_title', 'global'),
                            '[[site_url]]' => route('home'),
                            '[[plan_name]]' => $dps->plan->name,
                            '[[user_name]]' => $user->full_name,
                            '[[full_name]]' => $user->full_name,
                            '[[dps_id]]' => $dps->dps_id,
                            '[[per_installment]]' => $dps->per_installment,
                            '[[interest_rate]]' => $dps->plan->interest_rate,
                            '[[installment_date]]' => $installment->created_at,
                            '[[delay_charge]]' => $charge,
                            '[[given_installment]]' => $dps->given_installment,
                            '[[total_installment]]' => count($dps->transactions),
                            '[[matured_amount]]' => getTotalMature($dps),
                        ];

                        if ($user->balance >= $plan->per_installment) {

                            // Calculation of installment
                            $amount = $dps->per_installment;
                            $finalAmount = $amount + $charge;

                            $installment->given_date = $today;
                            $installment->paid_amount = $amount;
                            $installment->charge = $charge;
                            $installment->final_amount = $finalAmount;
                            $installment->save();

                            // Increment given installment
                            $dps->increment('given_installment');

                            // Paid and charge amount deducted from user balance
                            $user->decrement('balance', $finalAmount);

                            Txn::new($amount, $charge, $finalAmount, 'System', 'DPS Installment '.$plan->name.'', TxnType::DpsInstallment, TxnStatus::Success, '', null, $dps->user_id, null, 'User');

                            // Get given installment from dps
                            $givenInstallment = $dps->given_installment;

                            // Count total installments from dps transactions
                            $totalInstallments = count($dps->transactions);

                            // if $givenInstallment equals to $totalInstallments that is means dps status now mature/completed
                            // Otherwise, dps status is running
                            $dpsStatus = $givenInstallment == $totalInstallments ? DpsStatus::Mature : DpsStatus::Running;

                            // Update status
                            $dps->status = $dpsStatus;
                            $dps->save();

                            // Profit added to user balance
                            if ($dpsStatus->value == DpsStatus::Mature->value) {
                                $maturity_fee = $dps->plan->add_maturity_platform_fee ? $dps->plan->maturity_platform_fee : 0;
                                $total_mature_amount = getTotalMature($dps) - $maturity_fee;
                                $user->increment('balance', $total_mature_amount);

                                Txn::new(getTotalMature($dps), $maturity_fee, $total_mature_amount, 'System', 'DPS Maturity '.$plan->name.'', TxnType::DpsMaturity, TxnStatus::Success, '', null, $dps->user_id, null, 'User');

                                $this->smsNotify('dps_completed', $shortcodes, $dps->user->phone);
                                $this->mailNotify($dps->user->email, 'dps_completed', $shortcodes);
                                $this->pushNotify('dps_completed', $shortcodes, route('user.dps.details', $dps->dps_id), $dps->user_id);
                                $this->pushNotify('dps_completed', $shortcodes, route('admin.dps.details', $dps->id), $dps->user_id, 'Admin');
                            }
                        } else {

                            // Increment deferment
                            $installment->increment('deferment');
                            // Dps status set to due.
                            $dps->status = DpsStatus::Due;
                            $dps->save();

                            $this->smsNotify('dps_installment_due', $shortcodes, $dps->user->phone);
                            $this->mailNotify($dps->user->email, 'dps_installment_due', $shortcodes);
                            $this->pushNotify('dps_installment_due', $shortcodes, route('user.dps.details', $dps->dps_id), $dps->user_id);
                        }
                    }
                });

            DB::commit();

            return '........User DPS Successfully!!.';
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function grant()
    {

        try {

            DB::beginTransaction();

            $today = Carbon::today();
            $this->startCron();

            GrantTransaction::with('grant.plan')->whereNull('given_date')
                ->where('installment_date', '<=', $today)
                ->whereRelation('grant', 'status', 'running')
                ->chunk(500, function ($grantTransaction) use ($today) {
                    foreach ($grantTransaction as $installment) {

                        // Get grant data
                        $grant = $installment->grant;
                        // Get plan data
                        $plan = $grant->plan;
                        // Get user data
                        $user = $grant->user;

                        // Calculate per installment
                        $perInstallment = $installment->paid_amount;

                        // Calculate deferment charge
                        if ($installment->deferment != 0 && $installment->deferment >= $plan->delay_days) {
                            $charge = $plan->charge_type == 'percentage' ? (($plan->charge / 100) * $perInstallment) : $plan->charge;
                        } else {
                            $charge = 0;
                        }

                        // Retrieve installment amount
                        $amount = $perInstallment;
                        // Sum with charge.
                        $finalAmount = $amount + $charge;

                        // Check user balance and user balance is enough then completed installment.
                        // Otherwise, deferment increase.
                        if ($user->balance >= $finalAmount) {

                            // Save grant info
                            $installment->given_date = $today;
                            $installment->paid_amount = $amount;
                            $installment->charge = $charge;
                            $installment->final_amount = $finalAmount;
                            $installment->save();

                            // Deduct installment amount from user balance
                            $user->balance -= $finalAmount;
                            $user->save();

                            // Get Installments
                            $totalInstallments = count($grant->transactions);
                            $givenInstallments = $grant->transactions->whereNotNull('given_date')->count();

                            Txn::new($amount, $charge, $finalAmount, 'System', 'Grant Installment #'.$grant->grant_no.'', TxnType::GrantInstallment, TxnStatus::Success, '', null, $user->id, null, 'User');

                            $status = $totalInstallments == $givenInstallments ? GrantStatus::Completed : GrantStatus::Running;

                            $grant->status = $status;
                            $grant->save();

                            // Shortcodes for notifications
                            $shortcodes = [
                                '[[site_title]]' => setting('site_title', 'global'),
                                '[[site_url]]' => route('home'),
                                '[[plan_name]]' => $grant->plan->name,
                                '[[user_name]]' => $grant->user->full_name,
                                '[[full_name]]' => $grant->user->full_name,
                                '[[grant_id]]' => $grant->grant_no,
                                '[[given_installment]]' => $givenInstallments,
                                '[[total_installment]]' => count($grant->transactions),
                                '[[next_installment_date]]' => nextInstallment($grant->id, GrantTransaction::class, 'grant_id'),
                                '[[grant_amount]]' => $grant->amount.' '.setting('site_currency', 'global'),
                                '[[installment_amount]]' => $perInstallment.' '.setting('site_currency', 'global'),
                                '[[delay_charge]]' => $charge.' '.setting('site_currency', 'global'),
                                '[[installment_interval]]' => $grant->plan->installment_intervel,
                                '[[installment_rate]]' => $grant->plan->installment_rate,
                            ];

                            $this->smsNotify('grant_installment', $shortcodes, $grant->user->phone);
                            $this->mailNotify($grant->user->email, 'grant_installment', $shortcodes);
                            $this->pushNotify('grant_installment', $shortcodes, route('user.grant.details', $grant->grant_no), $grant->user_id);
                            $this->pushNotify('grant_installment', $shortcodes, route('admin.grant.details', $grant->id), $grant->user_id, 'Admin');
                        } else {
                            $installment->deferment++;
                            $installment->save();
                            $grant->status = GrantStatus::Due;
                            $grant->save();

                            // Shortcodes for notifications
                            $shortcodes = [
                                '[[site_title]]' => setting('site_title', 'global'),
                                '[[site_url]]' => route('home'),
                                '[[plan_name]]' => $grant->plan->name,
                                '[[user_name]]' => $grant->user->full_name,
                                '[[full_name]]' => $grant->user->full_name,
                                '[[grant_id]]' => $grant->grant_no,
                                '[[given_installment]]' => $grant->transactions->whereNotNull('given_date')->count(),
                                '[[total_installment]]' => count($grant->transactions),
                                '[[next_installment_date]]' => nextInstallment($grant->id, \App\Models\GrantTransaction::class, 'grant_id'),
                                '[[grant_amount]]' => $grant->amount.' '.setting('site_currency', 'global'),
                                '[[installment_amount]]' => $perInstallment.' '.setting('site_currency', 'global'),
                                '[[delay_charge]]' => $charge.' '.setting('site_currency', 'global'),
                                '[[installment_interval]]' => $grant->plan->installment_intervel,
                                '[[installment_rate]]' => $grant->plan->installment_rate,
                            ];

                            $this->smsNotify('grant_installment_due', $shortcodes, $grant->user->phone);
                            $this->mailNotify($grant->user->email, 'grant_installment_due', $shortcodes);
                            $this->pushNotify('grant_installment_due', $shortcodes, route('user.grant.details', $grant->grant_no), $grant->user_id);
                        }
                    }
                });

            DB::commit();

            return '........User Grant Successfully!!.';
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fdr()
    {

        try {

            DB::beginTransaction();

            $today = now();

            $this->startCron();

            FDRTransaction::with('fdr', 'fdr.plan', 'fdr.user')
                ->where('given_date', '<=', $today)
                ->whereRelation('fdr', 'status', 'running')
                ->whereNull('paid_amount')
                ->chunk(500, function ($fdrTransaction) {
                    foreach ($fdrTransaction as $installment) {
                        $fdr = $installment->fdr;
                        $plan = $fdr->plan;
                        $user = $fdr->user;

                        $perInstallment = $installment->given_amount;

                        $user->balance += $perInstallment;
                        $user->save();

                        $installment->paid_amount = $perInstallment;
                        $installment->save();

                        Txn::new($perInstallment, 0, $perInstallment, 'System', 'FDR Installemnt', TxnType::FdrInstallment, TxnStatus::Success, '', null, $fdr->user_id, null, 'User');

                        $totalInstallments = count($fdr->transactions);
                        $givenInstallments = FDRTransaction::where('fdr_id', $fdr->id)->whereNotNull('paid_amount')->count();

                        $status = $totalInstallments == $givenInstallments ? FdrStatus::Completed : FdrStatus::Running;

                        $fdr->status = $status;
                        $fdr->save();

                        $trx = FDRTransaction::where('fdr_id', $fdr->id)->whereNull('paid_amount')->first();

                        $shortcodes = [
                            '[[site_title]]' => setting('site_title', 'global'),
                            '[[site_url]]' => route('home'),
                            '[[plan_name]]' => $fdr->plan->name,
                            '[[user_name]]' => $user->full_name,
                            '[[full_name]]' => $user->full_name,
                            '[[fdr_id]]' => $fdr->fdr_id,
                            '[[per_installment]]' => $perInstallment,
                            '[[interest_rate]]' => $fdr->plan->interest_rate,
                            '[[given_installment]]' => $givenInstallments,
                            '[[total_installment]]' => $totalInstallments,
                            '[[amount]]' => $fdr->amount.' '.setting('site_currency', 'global'),
                            '[[installment_interval]]' => $fdr->plan->intervel,
                            '[[next_installment_date]]' => $trx?->given_date?->format('d M Y'),
                        ];

                        $this->smsNotify('fdr_installment', $shortcodes, $fdr->user->phone);
                        $this->mailNotify($fdr->user->email, 'fdr_installment', $shortcodes);
                        $this->pushNotify('fdr_installment', $shortcodes, route('user.fdr.details', $fdr->id), $fdr->user_id);

                        if ($fdr->status == FdrStatus::Completed && $fdr->plan->add_maturity_platform_fee) {
                            $user->decrement('balance', $fdr->plan->maturity_platform_fee);
                            Txn::new($fdr->plan->maturity_platform_fee, 0, $fdr->plan->maturity_platform_fee, 'System', 'FDR Maturity Fee', TxnType::FdrMaturityFee, TxnStatus::Success, '', null, $fdr->user_id, null, 'User');

                            $this->smsNotify('fdr_completed', $shortcodes, $fdr->user->phone);
                            $this->mailNotify($fdr->user->email, 'fdr_completed', $shortcodes);
                            $this->pushNotify('fdr_completed', $shortcodes, route('user.fdr.details', $fdr->id), $fdr->user_id);
                            $this->pushNotify('fdr_completed', $shortcodes, route('admin.fdr.details', $fdr->id), $fdr->user_id, 'Admin');
                        }
                    }
                });

            DB::commit();

            return '........User FDR Successfully!!.';
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    protected function startCron()
    {
        if (! App::initApp()) {
            return false;
        }
    }
}
