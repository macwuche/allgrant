<?php

namespace App\Models;

use App\Enums\GrantStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grant extends Model
{
    use HasFactory;

    protected $appends = ['day'];

    protected $fillable = [
        'grant_no',
        'txn_id',
        'grant_plan_id',
        'user_id',
        'amount',
        'commission_amount',
        'net_amount',
        'submitted_data',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'status' => GrantStatus::class,
        'amount' => 'int',
        'approved_at' => 'datetime',
    ];

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('username', 'like', '%'.$search.'%');
                })->orWhereHas('plan', function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%');
                });
            });
        }

        return $query;
    }

    public function getDayAttribute(): string
    {
        return Carbon::parse($this->attributes['created_at'])->format('d M');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->hasOne(GrantPlan::class, 'id', 'grant_plan_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', GrantStatus::Approved->value);
    }

    public function scopeReviewing($query)
    {
        return $query->where('status', GrantStatus::Reviewing->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', GrantStatus::Rejected->value);
    }
}
