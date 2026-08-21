<?php

namespace App\Http\Controllers\Backend;

use App\Enums\GrantStatus;
use App\Enums\ReferralType;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\LevelReferral;
use App\Models\Grant;
use App\Models\GrantPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
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
            ->approved()
            ->search($search)
            ->when(in_array($request->sort_field, ['grant_no', 'created_at', 'amount', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->latest()
            ->paginate(10);

        $statusForFrontend = __('Approved');

        return view('backend.grant.index', compact('grant', 'statusForFrontend'));
    }

    public function details($id)
    {
        $grant = Grant::with(['user', 'plan'])->find($id);

        return view('backend.grant.details', compact('grant'));
    }

    public function approvalAction(Request $request)
    {
        $grant = Grant::findOrFail($request->id);

        $plan = $grant->plan;

        $shortcodes = [
            '[[site_title]]' => setting('site_title', 'global'),
            '[[site_url]]' => route('home'),
            '[[plan_name]]' => $plan->name,
            '[[user_name]]' => $grant->user->full_name,
            '[[grant_id]]' => $grant->grant_no,
            '[[grant_amount]]' => $grant->amount.' '.setting('site_currency', 'global'),
        ];

        if ($request->status == 'approved') {

            $commissionAmount = $plan->commissionAmount($grant->amount);
            $netAmount = $grant->amount - $commissionAmount;

            $grant->update([
                'status' => GrantStatus::Approved,
                'commission_amount' => $commissionAmount,
                'net_amount' => $netAmount,
                'approved_at' => now(),
            ]);

            $grant->user->increment('balance', $netAmount);

            // Create Transaction
            Txn::new($netAmount, $commissionAmount, $grant->amount, 'System', 'Grant Approved #'.$grant->grant_no.'', TxnType::Grant, TxnStatus::Success, 'System', null, $grant->user_id, null, 'User');

            $shortcodes['[[commission_amount]]'] = $commissionAmount.' '.setting('site_currency', 'global');
            $shortcodes['[[net_amount]]'] = $netAmount.' '.setting('site_currency', 'global');

            $this->smsNotify('grant_approved', $shortcodes, $grant->user->phone);
            $this->mailNotify($grant->user->email, 'grant_approved', $shortcodes);
            $this->pushNotify('grant_approved', $shortcodes, route('user.grant.details', $grant->grant_no), $grant->user_id);

            // Level referral
            if (setting('grant_level')) {
                $level = LevelReferral::where('type', ReferralType::Grant->value)->max('the_order') + 1;
                creditReferralBonus($grant->user, ReferralType::Grant->value, $grant->amount, $level);
            }

            $message = __('Grant request approved successfully!');
        } else {

            $grant->update([
                'status' => GrantStatus::Rejected,
            ]);

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

        $applicationFee = $plan->applicationFee($amount);

        if ($user->balance < $applicationFee) {
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

            // Admin-initiated grants skip the review queue and disburse immediately.
            $commissionAmount = $plan->commissionAmount($amount);
            $netAmount = $amount - $commissionAmount;

            $grant = Grant::create([
                'grant_no' => 'G'.random_int(10000000, 99999999),
                'txn_id' => 0,
                'grant_plan_id' => $plan->id,
                'user_id' => $user->id,
                'submitted_data' => json_encode($submitted_data),
                'amount' => $amount,
                'commission_amount' => $commissionAmount,
                'net_amount' => $netAmount,
                'status' => GrantStatus::Approved,
                'approved_at' => now(),
            ]);

            if ($applicationFee) {
                $user->decrement('balance', $applicationFee);
            }

            $user->increment('balance', $netAmount);

            $txn = Txn::new($netAmount, $applicationFee + $commissionAmount, $netAmount + $applicationFee + $commissionAmount, 'System', 'Grant Applied #'.$grant->grant_no.'', TxnType::Grant, TxnStatus::Success, '', null, $user->id, null, 'User');

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
                '[[commission_amount]]' => $commissionAmount.' '.setting('site_currency', 'global'),
                '[[net_amount]]' => $netAmount.' '.setting('site_currency', 'global'),
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
