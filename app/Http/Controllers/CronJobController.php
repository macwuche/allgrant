<?php

namespace App\Http\Controllers;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Models\CronJob;
use App\Models\CronJobLog;
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

    protected function startCron()
    {
        if (! App::initApp()) {
            return false;
        }
    }
}
