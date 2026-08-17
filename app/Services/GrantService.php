<?php

namespace App\Services;

use App\Enums\GrantStatus;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Facades\Txn\Txn;
use App\Models\Grant;
use App\Models\GrantPlan;
use App\Models\GrantTransaction;
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
            'submitted_data' => 'required|array',
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

        if ($user->balance < $plan->grant_fee) {
            throw ValidationException::withMessages(['error' => __('Insufficient balance!')]);
        }
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
            'grant_no' => 'L'.random_int(10000000, 99999999),
            'txn_id' => 0,
            'grant_plan_id' => $plan->id,
            'user_id' => $user->id,
            'submitted_data' => json_encode($submitted_data),
            'amount' => $amount,
            'status' => GrantStatus::Reviewing,
        ]);

        if ($plan->grant_fee_type == 'percentage') {
            $grant_fee = ($amount / 100) * $plan->grant_fee;
        } else {
            $grant_fee = $plan->grant_fee;
        }

        $user->decrement('balance', $grant_fee);

        $txn = (new Txn)->new(0, $grant_fee, $amount + $plan->grant_fee, 'System', 'Grant Applied #'.$grant->grant_no.'', TxnType::GrantApply, TxnStatus::Success, '', null, $user->id, null, 'User');

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
            '[[installment_interval]]' => $plan->installment_intervel,
            '[[installment_rate]]' => $plan->installment_rate,
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

        $user->increment('balance', $grant->plan->grant_fee);

        return $grant;
    }

    public function payInstallment(User $user, Grant $grant, GrantTransaction $grantTransaction)
    {
        $plan = $grant->plan;

        $perInstallment = $grantTransaction->paid_amount;

        if ($grantTransaction->deferment != 0 && $grantTransaction->deferment >= $plan->delay_days) {
            $charge = $plan->charge_type == 'percentage' ? (($plan->charge / 100) * $perInstallment) : $plan->charge;
        } else {
            $charge = 0;
        }

        $amount = $perInstallment;

        $finalAmount = $amount + $charge;

        if ($user->balance < $finalAmount) {
            throw ValidationException::withMessages(['error' => __('Insufficient balance!')]);
        }

        $grantTransaction->given_date = now();
        $grantTransaction->paid_amount = $amount;
        $grantTransaction->charge = $charge;
        $grantTransaction->final_amount = $finalAmount;
        $grantTransaction->save();

        $user->balance -= $finalAmount;
        $user->save();

        $totalInstallments = count($grant->transactions);
        $givenInstallments = $grant->transactions->whereNotNull('given_date')->count();

        (new Txn)->new($amount, $charge, $finalAmount, 'User', 'Grant Installment #'.$grant->grant_no.'', TxnType::GrantInstallment, TxnStatus::Success, '', null, $user->id, null, 'User');

        $status = $totalInstallments == $givenInstallments ? GrantStatus::Completed : GrantStatus::Running;

        $grant->status = $status;

        $grant->save();

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

    }
}
