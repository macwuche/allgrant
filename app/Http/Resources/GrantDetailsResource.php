<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrantDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'plan_name' => $this->plan->name,
            'grant_id' => $this->grant_no,
            'status' => $this->status->value,
            'amount' => $this->amount.' '.setting('site_currency', 'global'),
            'per_installment' => $this->perInstallment().' '.setting('site_currency', 'global'),
            'installment_interval' => $this->plan->installment_intervel.' '.__('Days'),
            'total_installment' => $this->plan->total_installment.' '.__('Times'),
            'given_installment' => $this->transactions->count(),
            'next_installment' => nextInstallment($this->id, \App\Models\GrantTransaction::class, 'grant_id'),
            'deferment_days' => $this->plan->delay_days.' '.__('Days'),
            'deferment_charge' => $this->plan->charge.' '.($this->plan->charge_type == 'percentage' ? '%' : setting('site_currency', 'global')),
            'total_payable_amount' => $this->totalPayableAmount().' '.setting('site_currency', 'global'),
        ];
    }
}
