<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrantPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'icon',
        'name',
        'minimum_amount',
        'maximum_amount',
        'approval_days',
        'instructions',
        'grant_fee',
        'grant_fee_type',
        'commission_charge',
        'commission_charge_type',
        'field_options',
        'status',
        'badge',
        'featured',
    ];

    public function grants()
    {
        return $this->hasMany(Grant::class, 'grant_plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    private static ?\Illuminate\Support\Collection $activeCache = null;

    public static function activeCached(): \Illuminate\Support\Collection
    {
        if (self::$activeCache === null) {
            self::$activeCache = cache()->remember('grant_plans_active', 300, fn() => self::active()->get());
        }
        return self::$activeCache;
    }

    /**
     * The non-refundable fee charged when applying for this plan.
     */
    public function applicationFee($amount): float
    {
        $fee = $this->grant_fee_type == 'percentage' ? ($amount / 100) * $this->grant_fee : $this->grant_fee;

        return round((float) $fee, 2);
    }

    /**
     * The commission deducted from the grant amount at approval time.
     */
    public function commissionAmount($amount): float
    {
        $commission = $this->commission_charge_type == 'percentage' ? ($amount / 100) * $this->commission_charge : $this->commission_charge;

        return round((float) $commission, 2);
    }
}
