<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAddress extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
