<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailAddress;
use App\Models\EmailAttachment;
use App\Models\EmailWebhookEvent;
use App\Services\ResendMailService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Purifier;
use Throwable;

/**
 * Public, unauthenticated endpoint Resend calls for the `email.received`
 * event. Registered at the top level of routes/web.php (outside the
 * auth-protected groups) and excepted from CSRF in
 * App\Http\Middleware\VerifyCsrfToken -- same pattern as the existing
 * `stripe-webhook` route. Signature verification (Svix) stands in for
 * both of those, see App\Services\ResendMailService::verifyWebhookSignature.
 *
 * See email.md section 3 for the exact payload/API contracts this
 * implements, and section 6 for why this is a purpose-built controller
 * rather than the dormant spatie/laravel-webhook-client package.
 */
class ResendInboundWebhookController extends Controller
{
    public function __invoke(Request $request, ResendMailService $resend): Response
    {
        $svixId = (string) $request->header('svix-id');
        $svixTimestamp = (string) $request->header('svix-timestamp');
        $svixSignature = (string) $request->header('svix-signature');
        $rawBody = $request->getContent();

        if (! ResendMailService::verifyWebhookSignature($svixId, $svixTimestamp, $svixSignature, $rawBody)) {
            Log::warning('Resend inbound webhook: invalid signature', ['svix_id' => $svixId]);

            return response('invalid signature', 401);
        }

        $payload = json_decode($rawBody, true) ?? [];
        $eventType = $payload['type'] ?? null;

        // At-least-once delivery -- a redelivered svix-id is a no-op, not an error.
        $event = EmailWebhookEvent::firstOrCreate(
            ['svix_id' => $svixId],
            ['event_type' => $eventType, 'payload' => $payload]
        );

        if ($event->processed_at) {
            return response('ok', 200);
        }

        if ($eventType !== 'email.received') {
            // We only subscribed to email.received, but ignore anything else gracefully.
            $event->update(['processed_at' => now()]);

            return response('ignored', 200);
        }

        try {
            $this->processReceivedEmail($payload['data'] ?? [], $resend);
            $event->update(['processed_at' => now()]);
        } catch (Throwable $e) {
            $event->update(['error' => $e->getMessage()]);
            Log::error('Resend inbound webhook processing failed', [
                'svix_id' => $svixId,
                'error' => $e->getMessage(),
            ]);

            // Non-2xx so Resend retries -- the svix_id row already exists so
            // the retry re-enters this same method idempotently above.
            return response('processing failed', 500);
        }

        return response('ok', 200);
    }

    protected function processReceivedEmail(array $data, ResendMailService $resend): void
    {
        $emailId = $data['email_id'] ?? null;

        if (! $emailId) {
            return;
        }

        $recipients = array_merge($data['to'] ?? [], $data['received_for'] ?? []);
        $emailAddress = $this->matchConfiguredAddress($recipients);

        if (! $emailAddress) {
            // Not addressed to any address we've configured to receive on --
            // ignore rather than store stray mail against a catch-all domain.
            return;
        }

        if (Email::where('resend_id', $emailId)->exists()) {
            return;
        }

        $full = $resend->getReceivedEmail($emailId);

        $threadKey = $this->resolveThreadKey($full['message_id'] ?? null, $full['headers']['in-reply-to'] ?? null);

        // Inbound HTML is fully sender-controlled -- never trust it. Purifier::clean()
        // is this app's existing convention for sanitizing rich HTML before storage
        // (see e.g. PageController, DepositController); its default config here
        // already explicitly allows `data:` img src, which is exactly what carries
        // Resend's inline-image rewrite (see email.md section 3).
        $sanitizedHtml = ! empty($full['html']) ? Purifier::clean($full['html'], 'email_inbox') : null;

        $email = Email::create([
            'resend_id' => $emailId,
            'direction' => 'inbound',
            'email_address_id' => $emailAddress->id,
            'from_address' => $full['from'] ?? ($data['from'] ?? ''),
            'to_addresses' => $full['to'] ?? ($data['to'] ?? []),
            'cc_addresses' => $full['cc'] ?? [],
            'bcc_addresses' => $full['bcc'] ?? [],
            'subject' => $full['subject'] ?? ($data['subject'] ?? null),
            'html_body' => $sanitizedHtml,
            'text_body' => $full['text'] ?? null,
            'snippet' => Email::makeSnippet($full['text'] ?? null, $sanitizedHtml),
            'message_id' => $full['message_id'] ?? null,
            'in_reply_to' => $full['headers']['in-reply-to'] ?? null,
            'thread_key' => $threadKey,
            'status' => 'received',
            'is_read' => false,
            'raw_headers' => $full['headers'] ?? null,
        ]);

        $this->storeAttachments($email, $emailId, $resend);
    }

    protected function matchConfiguredAddress(array $recipients): ?EmailAddress
    {
        $recipients = array_map('mb_strtolower', array_filter($recipients));

        if (empty($recipients)) {
            return null;
        }

        return EmailAddress::active()
            ->get()
            ->first(fn (EmailAddress $address) => in_array(mb_strtolower($address->email), $recipients));
    }

    /**
     * Groups replies under the original message's thread_key. If this
     * message is a reply (has In-Reply-To) and we already have the
     * original stored, reuse its thread_key; otherwise this message starts
     * (or continues, if we don't have the parent) its own thread keyed on
     * whichever id we do have.
     */
    protected function resolveThreadKey(?string $messageId, ?string $inReplyTo): string
    {
        if ($inReplyTo) {
            $parent = Email::where('message_id', $inReplyTo)->first();

            if ($parent) {
                return $parent->thread_key;
            }

            return $inReplyTo;
        }

        return $messageId ?: (string) Str::uuid();
    }

    protected function storeAttachments(Email $email, string $emailId, ResendMailService $resend): void
    {
        $attachments = $resend->listAttachments($emailId);

        foreach ($attachments as $attachment) {
            if (empty($attachment['download_url'])) {
                continue;
            }

            $bytes = $resend->downloadAttachment($attachment['download_url']);
            $diskPath = 'email-attachments/'.$emailId.'/'.($attachment['filename'] ?? Str::uuid());

            Storage::disk('public')->put($diskPath, $bytes);

            EmailAttachment::create([
                'email_id' => $email->id,
                'filename' => $attachment['filename'] ?? 'attachment',
                'mime_type' => $attachment['content_type'] ?? null,
                'size' => strlen($bytes),
                'disk_path' => $diskPath,
                'content_id' => $attachment['content_id'] ?? null,
                'is_inline' => ($attachment['content_disposition'] ?? null) === 'inline',
            ]);
        }
    }
}
