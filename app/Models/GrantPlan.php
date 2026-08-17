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
        'interest_rate',
        'per_installment',
        'installment_intervel',
        'total_installment',
        'admin_profit',
        'instructions',
        'delay_days',
        'charge',
        'charge_type',
        'grant_fee',
        'grant_fee_type',
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

    public function getTotalAmountAttribute()
    {
        return $this->per_installment * $this->total_installment;
    }

    public function getBankProfitAttribute()
    {
        return ($this->total_amount * $this->interest_rate / 100) + $this->total_amount;
    }
}
