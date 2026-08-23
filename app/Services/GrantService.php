<?php

namespace App\Services;

use App\Enums\GrantStatus;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Facades\Txn\Txn;
use App\Models\Grant;
use App\Models\GrantPlan;
use App\Models\User;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GrantService
{
    use ImageUpload, NotifyTrait;

    public function validate(User $user, GrantPlan $plan, $amount, $request)
    {
        if (! setting('user_grant', 'permission') || ! $user->grant_status) {
            throw ValidationException::withMessages(['error' => __('Grant currently unavailable!')]);
        }

        if (! setting('kyc_grant') && ! $user->kyc) {
            throw ValidationException::withMessages(['error' => __('Please verify your KYC.')]);
        }

        $validator = Validator::make($request->all(), [
            // Plans with no custom fields configured (field_options: []) render no
            // submitted_data[...] inputs at all, so this must tolerate an absent/empty
            // value rather than require it — subscribe() already defaults it to [].
            'submitted_data' => 'nullable|array',
            'amount' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages(['error' => $validator->errors()->first()]);
        }

        if (! $plan) {
            throw ValidationException::withMessages(['error' => __('Grant plan not found!')]);
        }

        if ($plan->minimum_amount > $amount || $plan->maximum_amount < $amount) {
            throw ValidationException::withMessages(['error' => __('You can grant minimum :minimum_amount and maximum :maximum_amount', ['minimum_amount' => $plan->minimum_amount, 'maximum_amount' => $plan->maximum_amount])]);
        }

        // Deliberately no balance check here: applying is always allowed regardless of
        // current balance, even $0 — subscribe() below deducts the Application Charge
        // unconditionally, which is allowed to take the balance negative. This is the
        // user's own self-service apply flow only; the admin-initiated "Create Grant
        // Request" flow (Backend\GrantController::subscribeGrantRequest()) keeps its own
        // separate "User balance is low." check.
    }

    public function subscribe(User $user, GrantPlan $plan, $amount, $request)
    {
        // Grant application process
        $submitted_data = [];

        foreach ($request->submitted_data ?? [] as $key => $value) {

            if (is_file($value)) {
                $submitted_data[$key] = self::imageUploadTrait($value);
            } else {
                $submitted_data[$key] = $value;
            }
        }

        // Create grant request
        $grant = Grant::create([
            'grant_no' => 'G'.random_int(10000000, 99999999),
            'txn_id' => 0,
            'grant_plan_id' => $plan->id,
            'user_id' => $user->id,
            'submitted_data' => json_encode($submitted_data),
            'amount' => $amount,
            'status' => GrantStatus::Reviewing,
        ]);

        $applicationFee = $plan->applicationFee($amount);

        $user->decrement('balance', $applicationFee);

        $txn = (new Txn)->new(0, $applicationFee, $amount + $applicationFee, 'System', 'Grant Applied #'.$grant->grant_no.'', TxnType::GrantApply, TxnStatus::Success, '', null, $user->id, null, 'User');

        $grant->update([
            'txn_id' => $txn->id,
        ]);

        $shortcodes = [
            '[[site_title]]' => setting('site_title', 'global'),
            '[[site_url]]' => route('home'),
            '[[plan_name]]' => $plan->name,
            '[[user_name]]' => $user->full_name,
            '[[full_name]]' => $user->full_name,
            '[[grant_id]]' => $grant->grant_no,
            '[[grant_amount]]' => $grant->amount.' '.setting('site_currency', 'global'),
            '[[application_fee]]' => $applicationFee.' '.setting('site_currency', 'global'),
            '[[approval_days]]' => $plan->approval_days,
        ];
        $this->smsNotify('grant_apply', $shortcodes, $user->phone);
        $this->mailNotify(setting('support_email', 'global'), 'grant_apply', $shortcodes);
        $this->pushNotify('grant_apply', $shortcodes, route('user.grant.details', $grant->grant_no), $user->id);
        $this->pushNotify('grant_apply', $shortcodes, route('admin.grant.details', $grant->id), $user->id, 'Admin');

        return $grant;
    }

    public function cancel(User $user, Grant $grant)
    {
        if ($grant->status !== GrantStatus::Reviewing) {
            throw ValidationException::withMessages(['error' => __('Grant request already approved!')]);
        }

        $grant->update([
            'cancel_date' => now(),
            'status' => GrantStatus::Cancelled,
        ]);

        $user->increment('balance', $grant->plan->applicationFee($grant->amount));

        return $grant;
    }
}
