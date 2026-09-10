<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'to_addresses' => 'array',
        'cc_addresses' => 'array',
        'bcc_addresses' => 'array',
        'raw_headers' => 'array',
        'is_read' => 'boolean',
    ];

    public function emailAddress()
    {
        return $this->belongsTo(EmailAddress::class);
    }

    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Every message that shares this one's thread_key, oldest first —
     * what the thread/detail view renders.
     */
    public function thread()
    {
        return self::query()
            ->where('thread_key', $this->thread_key)
            ->orderBy('created_at')
            ->get();
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeForAddress($query, $emailAddressId)
    {
        return $query->when($emailAddressId, fn ($q) => $q->where('email_address_id', $emailAddressId));
    }

    /**
     * Short plain-text preview for the list view — strips tags/whitespace
     * from whichever body is available and trims to length.
     */
    public static function makeSnippet(?string $text, ?string $html, int $length = 140): ?string
    {
        $source = $text ?: strip_tags((string) $html);
        $source = trim(preg_replace('/\s+/', ' ', $source));

        return $source === '' ? null : \Illuminate\Support\Str::limit($source, $length);
    }
}
