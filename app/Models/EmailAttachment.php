<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_inline' => 'boolean',
    ];

    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->disk_path);
    }
}
