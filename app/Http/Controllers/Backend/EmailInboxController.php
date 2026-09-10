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

        $emails = Email::query()
            ->withCount('attachments')
            ->forAddress($request->get('address_id'))
            ->when($request->get('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%'.$request->get('q').'%')
                    ->orWhere('from_address', 'like', '%'.$request->get('q').'%')
                    ->orWhere('snippet', 'like', '%'.$request->get('q').'%');
            }))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = Email::inbound()->where('is_read', false)
            ->forAddress($request->get('address_id'))
            ->count();

        return view('backend.email_inbox.index', compact('emails', 'addresses', 'unreadCount'));
    }

    /**
     * Lightweight JSON feed the index page polls (and the Refresh button
     * calls on demand) to surface newly arrived mail without a full page
     * reload. Returns anything with an id greater than `after_id`.
     */
    public function poll(Request $request)
    {
        $addressId = $request->get('address_id');

        $new = Email::query()
            ->withCount('attachments')
            ->forAddress($addressId)
            ->when($request->get('after_id'), fn ($q) => $q->where('id', '>', $request->integer('after_id')))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Email $email) => [
                'id' => $email->id,
                'direction' => $email->direction,
                'from' => $email->from_address,
                'subject' => $email->subject,
                'snippet' => $email->snippet,
                'is_read' => $email->is_read,
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

        $data['to'] = $original->from_address;
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

        $payload = array_filter([
            'from' => $from->email,
            'to' => $to,
            'cc' => ! empty($data['cc']) ? array_map('trim', explode(',', $data['cc'])) : null,
            'bcc' => ! empty($data['bcc']) ? array_map('trim', explode(',', $data['bcc'])) : null,
            'subject' => $data['subject'],
            'html' => $data['body'],
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

            $email->update([
                'resend_id' => $result['id'] ?? null,
                'message_id' => $result['id'] ?? null,
                'thread_key' => $email->thread_key ?: ($result['id'] ?? (string) $email->id),
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
