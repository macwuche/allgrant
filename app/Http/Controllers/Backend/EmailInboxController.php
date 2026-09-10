<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Models\EmailAddress;
use App\Models\EmailAttachment;
use App\Services\ResendMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Purifier;

class EmailInboxController extends Controller
{
    public function __construct()
    {
        $this->middleware('email-inbox-enabled');
        $this->middleware('permission:email-inbox-view', ['only' => ['index', 'show', 'poll', 'download']]);
        $this->middleware('permission:email-inbox-send', ['only' => ['compose', 'send', 'reply']]);
    }

    public function index(Request $request)
    {
        $addresses = EmailAddress::active()->orderByDesc('is_default')->get();
        $addressId = $request->get('address_id');

        $emails = $this->latestPerThread($addressId)
            ->when($request->get('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%'.$request->get('q').'%')
                    ->orWhere('from_address', 'like', '%'.$request->get('q').'%')
                    ->orWhere('snippet', 'like', '%'.$request->get('q').'%');
            }))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $this->attachThreadCounts($emails->getCollection());

        $unreadCount = Email::inbound()->where('is_read', false)
            ->forAddress($addressId)
            ->count();

        return view('backend.email_inbox.index', compact('emails', 'addresses', 'unreadCount'));
    }

    /**
     * Base query for "one row per thread" -- the thread's most recent
     * message (highest id, which for an append-only conversation is always
     * the most recently created one too), filtered/ordered the same way a
     * flat message list would be. Grouping happens via a MAX(id)-per-
     * thread_key subquery rather than e.g. Postgres's DISTINCT ON, since
     * this app runs on both Postgres and MySQL across its two live hosts
     * (see email.md section 9) and this stays portable across both.
     */
    protected function latestPerThread(?string $addressId)
    {
        return Email::query()
            ->withCount('attachments')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')->from('emails')->groupBy('thread_key');
            })
            ->forAddress($addressId);
    }

    /**
     * Sets thread_message_count / thread_unread_count on each (head-of-
     * thread) Email in $emails -- the index/poll rows need these to show a
     * "3 messages" style count and to bold a thread when any message in it
     * (not just the latest one) is unread, even if the latest one happens
     * to be our own outbound reply.
     */
    protected function attachThreadCounts($emails): void
    {
        $threadKeys = $emails->pluck('thread_key')->unique()->values();

        if ($threadKeys->isEmpty()) {
            return;
        }

        $counts = Email::query()
            ->whereIn('thread_key', $threadKeys)
            ->selectRaw('thread_key, COUNT(*) as message_count')
            ->groupBy('thread_key')
            ->pluck('message_count', 'thread_key');

        $unread = Email::inbound()
            ->whereIn('thread_key', $threadKeys)
            ->where('is_read', false)
            ->selectRaw('thread_key, COUNT(*) as unread_count')
            ->groupBy('thread_key')
            ->pluck('unread_count', 'thread_key');

        $emails->each(function (Email $email) use ($counts, $unread) {
            $email->thread_message_count = (int) ($counts[$email->thread_key] ?? 1);
            $email->thread_unread_count = (int) ($unread[$email->thread_key] ?? 0);
        });
    }

    /**
     * Lightweight JSON feed the index page polls (and the Refresh button
     * calls on demand) to surface newly arrived mail without a full page
     * reload. One row per thread that has any new message (id greater than
     * `after_id`) -- an existing thread bumped by a new reply is meant to
     * move to the top and refresh in place client-side, not duplicate.
     */
    public function poll(Request $request)
    {
        $addressId = $request->get('address_id');
        $afterId = $request->integer('after_id');

        $activeThreadKeys = Email::query()
            ->forAddress($addressId)
            ->when($afterId, fn ($q) => $q->where('id', '>', $afterId))
            ->pluck('thread_key')
            ->unique()
            ->values();

        $threads = collect();

        if ($activeThreadKeys->isNotEmpty()) {
            $threads = Email::query()
                ->withCount('attachments')
                ->whereIn('thread_key', $activeThreadKeys)
                ->whereIn('id', function ($query) use ($activeThreadKeys) {
                    $query->selectRaw('MAX(id)')->from('emails')
                        ->whereIn('thread_key', $activeThreadKeys)
                        ->groupBy('thread_key');
                })
                ->orderByDesc('created_at')
                ->get();

            $this->attachThreadCounts($threads);
        }

        $new = $threads->map(fn (Email $email) => [
            'id' => $email->id,
            'thread_key' => $email->thread_key,
            'direction' => $email->direction,
            'from' => $email->otherParty(),
            'subject' => $email->subject,
            'snippet' => $email->snippet,
            'unread' => $email->thread_unread_count > 0,
            'message_count' => $email->thread_message_count,
            'has_attachments' => $email->attachments_count > 0,
            'created_at' => $email->created_at->diffForHumans(),
            'url' => route('admin.email-inbox.show', $email->id),
        ]);

        $unreadCount = Email::inbound()->where('is_read', false)->forAddress($addressId)->count();

        return response()->json(['emails' => $new, 'unread_count' => $unreadCount]);
    }

    public function show($id)
    {
        $email = Email::with('attachments', 'emailAddress')->findOrFail($id);
        $email->update(['is_read' => true]);

        $thread = $email->thread()->load('attachments');
        $addresses = EmailAddress::active()->get();

        return view('backend.email_inbox.show', compact('email', 'thread', 'addresses'));
    }

    public function compose()
    {
        $addresses = EmailAddress::active()->get();

        return view('backend.email_inbox.compose', compact('addresses'));
    }

    public function send(Request $request, ResendMailService $resend)
    {
        $data = $request->validate([
            'email_address_id' => 'required|exists:email_addresses,id',
            'to' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|max:20480',
        ]);

        $from = EmailAddress::findOrFail($data['email_address_id']);

        return $this->deliver($resend, $from, $data, null);
    }

    public function reply(Request $request, $id, ResendMailService $resend)
    {
        $original = Email::findOrFail($id);

        $data = $request->validate([
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|max:20480',
        ]);

        $from = $original->emailAddress ?: EmailAddress::active()->first();

        if (! $from) {
            notify()->error(__('No sending address configured.'), 'Error');

            return redirect()->back();
        }

        // Reply must always go to "the other party," not whichever address
        // happens to be in from_address -- for an outbound row, that's one
        // of *our own* addresses, and replying from one of those used to
        // send the reply back to ourselves instead of the recipient.
        $data['to'] = $original->otherParty();
        $data['subject'] = Str::startsWith(Str::lower((string) $original->subject), 're:')
            ? $original->subject
            : 'Re: '.$original->subject;

        return $this->deliver($resend, $from, $data, $original);
    }

    protected function deliver(ResendMailService $resend, EmailAddress $from, array $data, ?Email $inReplyTo)
    {
        $data['body'] = Purifier::clean($data['body']);

        $attachments = [];

        foreach ($data['attachments'] ?? [] as $file) {
            $attachments[] = [
                'filename' => $file->getClientOriginalName(),
                'content' => base64_encode(file_get_contents($file->getRealPath())),
                'content_type' => $file->getClientMimeType(),
            ];
        }

        $to = array_map('trim', explode(',', $data['to']));
        $headers = [];

        if ($inReplyTo && $inReplyTo->message_id) {
            $headers['In-Reply-To'] = $inReplyTo->message_id;
            $headers['References'] = trim(($inReplyTo->raw_headers['references'] ?? '').' '.$inReplyTo->message_id);
        }

        // Outgoing copy only -- the admin's own body (unwrapped) is what we store
        // and show in our thread view; recipients get it wrapped in the branded
        // header/footer template. See email.md section 12 for the full plan.
        $wrappedBody = view('backend.email_inbox.mail.wrapper', [
            'subject' => $data['subject'],
            'bodyHtml' => $data['body'],
            'siteLogo' => setting('site_logo', 'global') ? asset(setting('site_logo', 'global')) : null,
            'siteTitle' => setting('site_title', 'global'),
            'siteLink' => route('home'),
            'brandColor' => setting('email_inbox_brand_color', 'email_inbox') ?: '#6c3beb',
            'footerText' => setting('email_inbox_footer_text', 'email_inbox'),
        ])->render();

        $payload = array_filter([
            'from' => $from->email,
            'to' => $to,
            'cc' => ! empty($data['cc']) ? array_map('trim', explode(',', $data['cc'])) : null,
            'bcc' => ! empty($data['bcc']) ? array_map('trim', explode(',', $data['bcc'])) : null,
            'subject' => $data['subject'],
            'html' => $wrappedBody,
            'headers' => ! empty($headers) ? $headers : null,
            'attachments' => ! empty($attachments) ? $attachments : null,
        ], fn ($v) => ! is_null($v));

        $email = Email::create([
            'direction' => 'outbound',
            'email_address_id' => $from->id,
            'from_address' => $from->email,
            'to_addresses' => $to,
            'cc_addresses' => $payload['cc'] ?? null,
            'bcc_addresses' => $payload['bcc'] ?? null,
            'subject' => $data['subject'],
            'html_body' => $data['body'],
            'snippet' => Email::makeSnippet(null, $data['body']),
            'in_reply_to' => $inReplyTo?->message_id,
            'thread_key' => $inReplyTo?->thread_key,
            'status' => 'queued',
            'is_read' => true,
            'admin_id' => auth('admin')->id(),
        ]);

        try {
            $result = $resend->send($payload);
            $sentId = $result['id'] ?? null;

            // POST /emails only returns Resend's own internal id, not the RFC
            // 2822 Message-ID header actually put on the outgoing mail -- and
            // it's *that* header a recipient's mail client echoes back in
            // In-Reply-To when they reply. Storing the send-response id here
            // instead was the bug behind replies coming back in as new,
            // unthreaded messages (email.md section 10): a quick follow-up
            // GET /emails/{id} gets us the real value to match against later.
            $realMessageId = null;

            if ($sentId) {
                try {
                    $realMessageId = $resend->getSentEmail($sentId)['message_id'] ?? null;
                } catch (\Throwable $e) {
                    // Non-fatal -- fall back to the send-response id below
                    // rather than failing a mail that already went out.
                }
            }

            $email->update([
                'resend_id' => $sentId,
                'message_id' => $realMessageId ?: $sentId,
                'thread_key' => $email->thread_key ?: ($realMessageId ?: $sentId ?: (string) $email->id),
                'status' => 'sent',
            ]);

            notify()->success(__('Email sent.'), 'Success');
        } catch (\Throwable $e) {
            $email->update(['status' => 'failed', 'error' => $e->getMessage()]);
            notify()->error(__('Failed to send: ').$e->getMessage(), 'Error');
        }

        return $inReplyTo
            ? redirect()->route('admin.email-inbox.show', $inReplyTo->id)
            : redirect()->route('admin.email-inbox.index');
    }

    public function download($attachmentId)
    {
        $attachment = EmailAttachment::findOrFail($attachmentId);

        return Storage::disk('public')->download($attachment->disk_path, $attachment->filename);
    }
}
