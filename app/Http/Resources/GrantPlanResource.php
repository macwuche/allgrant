<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrantPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currency = setting('site_currency', 'global');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'minimum_amount' => $this->minimum_amount.' '.$currency,
            'maximum_amount' => $this->maximum_amount.' '.$currency,
            'approval_days' => $this->approval_days,
            'application_charge' => $this->grant_fee.($this->grant_fee_type == 'percentage' ? '%' : ' '.$currency),
            'commission_charge' => $this->commission_charge.($this->commission_charge_type == 'percentage' ? '%' : ' '.$currency),
            'fields' => $this->field_options,
            'instructions' => strip_tags($this->instructions),
            'plan_data' => [
                'approval_days' => $this->approval_days,
                'application_charge' => $this->grant_fee,
                'application_charge_type' => $this->grant_fee_type,
                'commission_charge' => $this->commission_charge,
                'commission_charge_type' => $this->commission_charge_type,
            ],
        ];
    }
}
