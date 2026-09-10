<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per inbound Resend webhook delivery, keyed on its unique
 * `svix-id`. Resend/Svix delivery is at-least-once, so this is purely an
 * idempotency ledger — a redelivered event is recognized and skipped
 * instead of creating a duplicate Email row.
 */
class EmailWebhookEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
