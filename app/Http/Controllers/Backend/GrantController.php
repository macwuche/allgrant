<?php

namespace App\Http\Controllers\Backend;

use App\Enums\GrantStatus;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\LevelReferral;
use App\Models\Grant;
use App\Models\GrantPlan;
use App\Models\GrantTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Txn;

class GrantController extends Controller
{
    use ImageUpload, NotifyTrait;

    public function __construct()
    {
        $this->middleware('permission:pending-grant', ['only' => ['request']]);
        $this->middleware('permission:running-grant', ['only' => ['approved']]);
        $this->middleware('permission:due-grant', ['only' => ['payable']]);
        $this->middleware('permission:paid-grant', ['only' => ['completed']]);
        $this->middleware('permission:rejected-grant', ['only' => ['rejected']]);
        $this->middleware('permission:all-grant', ['only' => ['all']]);
        $this->middleware('permission:view-grant-details', ['only' => ['details']]);
        $this->middleware('permission:grant-approval', ['only' => ['approvalAction']]);
        $this->middleware('permission:subscribe-user-grant', ['only' => ['createGrantRequest', 'subscribeGrantRequest']]);
    }

    public function all(Request $request)
    {
        $search = $request->search;
        $grant = Grant::with(['plan', 'user'])
            ->search($search)
            ->when(in_array($request->sort_field, ['grant_no', 'created_at', 'amount', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->latest()
            ->paginate(10);

        $statusForFrontend = __('All');

        return view('backend.grant.index', compact('grant', 'statusForFrontend'));
    }

    public function request(Request $request)
    {
        $search = $request->search;
        $grant = Grant::with(['plan', 'user'])
            ->reviewing()
            ->search($search)
            ->when(in_array($request->sort_field, ['grant_no', 'created_at', 'amount', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->latest()
            ->paginate(10);

        $statusForFrontend = __('Requested');

        return view('backend.grant.index', compact('grant', 'statusForFrontend'));
    }

    public function rejected(Request $request)
    {
        $search = $request->search;

        $grant = Grant::with(['plan', 'user'])
            ->rejected()
            ->search($search)
            ->when(in_array($request->sort_field, ['grant_no', 'created_at', 'amount', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->latest()
            ->paginate(10);

        $statusForFrontend = __('Rejected');

        return view('backend.grant.index', compact('grant', 'statusForFrontend'));
    }

    public function approved(Request $request)
    {
        $search = $request->search;

        $grant = Grant::with(['plan', 'user'])
            ->running()
            ->search($search)
            ->when(in_array($request->sort_field, ['grant_no', 'created_at', 'amount', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->latest()
            ->paginate(10);

        $statusForFrontend = __('Approved');

        return view('backend.grant.index', compact('grant', 'statusForFrontend'));
    }

    public function payable(Request $request)
    {
        $search = $request->search;

        $grant = Grant::with(['plan', 'user'])
            ->due()
            ->search($search)
            ->when(in_array($request->sort_field, ['grant_no', 'created_at', 'amount', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->latest()
            ->paginate(10);

        $statusForFrontend = __('Payable');

        return view('backend.grant.index', compact('grant', 'statusForFrontend'));
    }

    public function completed(Request $request)
    {
        $search = $request->search;

        $grant = Grant::with(['plan', 'user'])
            ->completed()
            ->search($search)
            ->when(in_array($request->sort_field, ['grant_no', 'created_at', 'amount', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->latest()
            ->paginate(10);

        $statusForFrontend = __('Completed');

        return view('backend.grant.index', compact('grant', 'statusForFrontend'));
    }

    public function details($id)
    {
        $grant = Grant::with(['user', 'plan', 'transactions'])->find($id);

        return view('backend.grant.details', compact('grant'));
    }

    public function approvalAction(Request $request)
    {
        $grant = Grant::findOrFail($request->id);

        $grant->update([
            'status' => $request->status,
        ]);

        $plan = $grant->plan;

        $shortcodes = [
            '[[site_title]]' => setting('site_title', 'global'),
            '[[site_url]]' => route('home'),
            '[[plan_name]]' => $grant->plan->name,
            '[[user_name]]' => $grant->user->full_name,
            '[[grant_id]]' => $grant->grant_no,
            '[[given_installment]]' => 0,
            '[[total_installment]]' => $grant->plan->total_installment,
            '[[next_installment_date]]' => nextInstallment($grant->id, \App\Models\GrantTransaction::class, 'grant_id'),
            '[[grant_amount]]' => $grant->amount.' '.setting('site_currency', 'global'),
            '[[installment_interval]]' => $grant->plan->installment_intervel,
            '[[installment_rate]]' => $grant->plan->installment_rate,
        ];

        if ($request->status == 'running') {
            $grantTransactions = [];

            for ($i = 1; $i <= $plan->total_installment; $i++) {
                $grantTransactions[] = [
                    'grant_id' => $grant->id,
                    'installment_date' => Carbon::now()->addDays($plan->installment_intervel * $i),
                    'paid_amount' => $grant->perInstallment(),
                    'created_at' => now(),
                ];
            }

            GrantTransaction::insert($grantTransactions);

            $grant->user->increment('balance', $grant->amount);

            // Create Transaction
            Txn::new($grant->amount, 0, $grant->amount, 'System', 'Grant Approved #'.$grant->grant_no.'', TxnType::Grant, TxnStatus::Success, 'System', null, $grant->user_id, null, 'User');

            $this->smsNotify('grant_approved', $shortcodes, $grant->user->phone);
            $this->mailNotify($grant->user->email, 'grant_approved', $shortcodes);
            $this->pushNotify('grant_approved', $shortcodes, route('user.grant.details', $grant->grant_no), $grant->user_id);

            // Level referral
            if (setting('grant_level')) {
                $level = LevelReferral::where('type', 'grant')->max('the_order') + 1;
                creditReferralBonus($grant->user, 'grant', $grant->amount, $level);
            }

            $message = __('Grant request approved successfully!');
        } else {

            $transaction = Transaction::find($grant->txn_id);

            $transaction?->update([
                'status' => TxnStatus::Failed,
            ]);

            $grant->user->increment('balance', $transaction->charge);

            $message = __('Grant request rejected successfully!');

            $this->smsNotify('grant_rejected', $shortcodes, $grant->user->phone);
            $this->mailNotify($grant->user->email, 'grant_rejected', $shortcodes);
            $this->pushNotify('grant_rejected', $shortcodes, route('user.grant.details', $grant->grant_no), $grant->user_id);
        }

        notify()->success($message, 'Success');

        return redirect()->route('admin.grant.all');
    }

    public function createGrantRequest(Request $request)
    {
        $grantPlans = GrantPlan::query()
            ->active()
            ->get();

        $selectGrantPlan = $request->filled('grant_plan_id') ? GrantPlan::query()->active()
            ->where('id', $request->grant_plan_id)
            ->first() : [];

        return view('backend.grant.subscribe', compact('grantPlans', 'selectGrantPlan'));
    }

    public function subscribeGrantRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'grant_plan_id' => 'required|integer|exists:grant_plans,id',
            'grant_amount' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $user = User::find($request->user_id);

        if (! $user) {
            notify()->error(__('User not found'), 'Error');

            return redirect()->back();
        }

        $plan = GrantPlan::find($request->grant_plan_id);

        if (! $plan) {
            notify()->error(__('Grant Plan Not found.'), 'Error');

            return redirect()->back();
        }

        $amount = (float) $request->grant_amount;

        $currency = setting('currency_symbol', 'global');

        $min = (int) $plan->minimum_amount;
        $max = (int) $plan->maximum_amount;

        if ($amount < $min || $max < $amount) {
            $message = __('You must choice minimum :minimum and maximum :maximum', ['minimum' => $currency.$plan->minimum_amount, 'maximum' => $currency.$plan->maximum_amount]);
            notify()->error($message, 'Error');

            return redirect()->back();
        }

        $grant_fee = $plan->grant_fee;

        if ($user->balance < $grant_fee) {
            notify()->error(__('User balance is low.'), 'Error');

            return redirect()->back();
        }

        $submitted_data = [];

        foreach ($request->submitted_data ?? [] as $key => $value) {

            if (is_file($value)) {
                $submitted_data[$key] = self::imageUploadTrait($value);
            } else {
                $submitted_data[$key] = $value;
            }
        }

        try {

            DB::beginTransaction();

            $grant = Grant::create([
                'grant_no' => 'L'.random_int(10000000, 99999999),
                'txn_id' => 0,
                'grant_plan_id' => $plan->id,
                'user_id' => $user->id,
                'submitted_data' => json_encode($submitted_data),
                'amount' => $amount,
                'status' => GrantStatus::Running,
            ]);

            if ($grant_fee) {
                $user->decrement('balance', $grant_fee);
            }

            $grantTransactions = [];

            for ($i = 1; $i <= $plan->total_installment; $i++) {
                $grantTransactions[] = [
                    'grant_id' => $grant->id,
                    'installment_date' => Carbon::now()->addDays($plan->installment_intervel * $i),
                ];
            }

            GrantTransaction::insert($grantTransactions);

            $user->increment('balance', $grant->amount);

            $txn = Txn::new($amount, $grant_fee, $amount + $grant_fee, 'System', 'Grant Applied #'.$grant->grant_no.'', TxnType::Grant, TxnStatus::Success, '', null, $user->id, null, 'User');

            $grant->update([
                'txn_id' => $txn->id,
            ]);

            $shortcodes = [
                '[[site_title]]' => setting('site_title', 'global'),
                '[[site_url]]' => route('home'),
                '[[plan_name]]' => $grant->plan->name,
                '[[user_name]]' => $grant->user->full_name,
                '[[full_name]]' => $grant->user->full_name,
                '[[grant_id]]' => $grant->grant_no,
                '[[grant_amount]]' => $grant->amount.' '.setting('site_currency', 'global'),
                '[[installment_interval]]' => $grant->plan->installment_intervel,
                '[[installment_rate]]' => $grant->plan->installment_rate,
            ];

            $this->smsNotify('grant_approved', $shortcodes, $grant->user->phone);
            $this->mailNotify(setting('support_email', 'global'), 'grant_approved', $shortcodes);
            $this->pushNotify('grant_approved', $shortcodes, route('user.grant.details', $grant->grant_no), $grant->user_id);
            $this->pushNotify('grant_approved', $shortcodes, route('admin.grant.details', $grant->id), $grant->user_id, 'Admin');

            DB::commit();

            notify()->success(__('Grant has been created successfully!'), 'Success');

            return to_route('admin.grant.all');
        } catch (\Throwable $e) {
            DB::rollBack();
            notify()->error(__('Sorry! Something went wrong. Please try again'), 'Error');

            return redirect()->back();
        }
    }
}
