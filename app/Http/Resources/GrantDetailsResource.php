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
        $currency = setting('site_currency', 'global');

        return [
            'plan_name' => $this->plan->name,
            'grant_id' => $this->grant_no,
            'status' => $this->status->value,
            'amount' => $this->amount.' '.$currency,
            'application_fee' => $this->plan->applicationFee($this->amount).' '.$currency,
            'commission_amount' => $this->commission_amount.' '.$currency,
            'net_amount' => $this->net_amount !== null ? $this->net_amount.' '.$currency : null,
            'approved_at' => $this->approved_at?->format('d M Y h:i A'),
        ];
    }
}
