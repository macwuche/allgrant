<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrantTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'grant_id',
        'installment_date',
        'given_date',
        'deferment',
        'paid_amount',
        'charge',
        'final_amount',
    ];

    protected $casts = [
        'installment_date' => 'date',
        'given_date' => 'date',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class);
    }
}
