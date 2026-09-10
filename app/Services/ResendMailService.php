<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around Resend's HTTP API — sending, and reading received
 * mail back. No SDK: this sandbox has no php/composer to safely add and
 * verify a new dependency, and Resend's send/receive surface is small
 * enough that plain Http calls are simpler to audit. See email.md section 3
 * for the exact contracts each method below implements (verified against
 * Resend's live docs, not assumed from memory) -- if Resend's API ever
 * changes shape, this is the one place to update.
 */
class ResendMailService
{
    protected const BASE_URL = 'https://api.resend.com';

    protected function apiKey(): string
    {
        $key = setting('email_inbox_api_key', 'email_inbox');

        if (blank($key)) {
            throw new RuntimeException('Resend API key is not configured (Settings > Email Inbox).');
        }

        return $key;
    }

    protected function client()
    {
        return Http::withToken($this->apiKey())->baseUrl(self::BASE_URL)->acceptJson();
    }

    /**
     * Send an email. $params matches Resend's /emails body: from, to,
     * subject, html/text, cc, bcc, reply_to, headers, attachments.
     * Returns the decoded response (contains 'id' on success).
     *
     * @throws RuntimeException on a non-2xx response
     */
    public function send(array $params): array
    {
        $response = $this->client()->post('/emails', $params);

        if ($response->failed()) {
            throw new RuntimeException('Resend send failed: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Retrieve a previously-sent email by the id POST /emails returned.
     * Unlike the send response (which only contains `id`, Resend's own
     * internal id), this returns the actual RFC 2822 `Message-ID` header
     * value that went out on the wire, e.g. "<111-222-333@email.example.com>"
     * -- the value a recipient's reply will echo back in In-Reply-To. We
     * must store *this*, not the send-response id, or thread matching
     * against inbound replies silently fails (see email.md section 10).
     */
    public function getSentEmail(string $id): array
    {
        $response = $this->client()->get("/emails/{$id}");

        if ($response->failed()) {
            throw new RuntimeException("Resend getSentEmail({$id}) failed: ".$response->body());
        }

        return $response->json();
    }

    /**
     * Full content of a received email -- the webhook payload only carries
     * metadata, this is the follow-up call for html/text/headers. Passing
     * html_format=data_uri (the default, made explicit here) makes Resend
     * rewrite any cid: inline-image references in the returned html into
     * data: URIs, so images just render with no cid-handling on our side.
     */
    public function getReceivedEmail(string $emailId): array
    {
        $response = $this->client()->get("/emails/receiving/{$emailId}", [
            'html_format' => 'data_uri',
        ]);

        if ($response->failed()) {
            throw new RuntimeException("Resend getReceivedEmail({$emailId}) failed: ".$response->body());
        }

        return $response->json();
    }

    /**
     * Attachment metadata + a short-lived (~1 hour) download_url per
     * attachment for a received email. Callers should download the bytes
     * immediately -- the URL is not valid long enough to fetch lazily when
     * an admin later opens the thread.
     */
    public function listAttachments(string $emailId): array
    {
        $response = $this->client()->get("/emails/receiving/{$emailId}/attachments");

        if ($response->failed()) {
            throw new RuntimeException("Resend listAttachments({$emailId}) failed: ".$response->body());
        }

        return $response->json()['data'] ?? $response->json();
    }

    /**
     * Downloads one attachment's raw bytes from its (short-lived)
     * download_url. Not authenticated with our API key -- it's a
     * pre-signed URL, fetched as-is.
     */
    public function downloadAttachment(string $downloadUrl): string
    {
        $response = Http::get($downloadUrl);

        if ($response->failed()) {
            throw new RuntimeException('Resend attachment download failed: '.$downloadUrl);
        }

        return $response->body();
    }

    /**
     * Verifies a Resend inbound webhook request using the Svix signing
     * scheme (Resend webhooks are Svix-signed, not a Resend-proprietary
     * format -- see email.md section 3 for the worked example this was
     * checked against). $rawBody must be the exact, unmodified request
     * body -- re-encoding parsed JSON breaks the signature.
     */
    public static function verifyWebhookSignature(string $svixId, string $svixTimestamp, string $svixSignatureHeader, string $rawBody): bool
    {
        $secret = setting('email_inbox_webhook_secret', 'email_inbox');

        if (blank($secret) || blank($svixId) || blank($svixTimestamp) || blank($svixSignatureHeader)) {
            return false;
        }

        // Reject stale/replayed deliveries outside a 5 minute tolerance.
        if (abs(time() - (int) $svixTimestamp) > 300) {
            return false;
        }

        $secretBytes = base64_decode(preg_replace('/^whsec_/', '', $secret));
        $signedContent = "{$svixId}.{$svixTimestamp}.{$rawBody}";
        $expected = base64_encode(hash_hmac('sha256', $signedContent, $secretBytes, true));

        // Header is space-delimited "v1,<base64sig>" entries -- valid if any match.
        foreach (explode(' ', $svixSignatureHeader) as $entry) {
            $signature = str_contains($entry, ',') ? explode(',', $entry, 2)[1] : $entry;

            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
