@extends('backend.layouts.app')
@section('title')
    {{ $email->subject ?: __('(no subject)') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ $email->subject ?: __('(no subject)') }}</h2>
                            <a href="{{ route('admin.email-inbox.index') }}" class="title-btn">
                                <i data-lucide="arrow-left"></i> {{ __('Back to Inbox') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-9 col-lg-12 col-12">

                    @foreach($thread as $message)
                        <div class="site-card mb-3">
                            <div class="site-card-header">
                                <div>
                                    <strong>{{ $message->direction === 'inbound' ? $message->from_address : __('You').' <'.$message->from_address.'>' }}</strong>
                                    <div class="small text-muted">
                                        {{ __('To') }}: {{ implode(', ', $message->to_addresses ?? []) }}
                                        @if(!empty($message->cc_addresses))
                                            &middot; {{ __('Cc') }}: {{ implode(', ', $message->cc_addresses) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="card-header-links">
                                    <span class="small text-muted">{{ $message->created_at->format('M d, Y g:i A') }}</span>
                                    @if($message->status === 'failed')
                                        <span class="site-badge danger">{{ __('Failed') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="site-card-body">
                                {{-- Sanitized with the 'email_inbox' Purifier profile before it ever
                                     reaches this view (ResendInboundWebhookController) -- no script/
                                     style/event-handler survives, so this is safe to render inline
                                     rather than needing a sandboxed iframe. Inline images already
                                     arrive as data: URIs baked into the html by Resend. --}}
                                <div style="overflow-x:auto;">
                                    @if($message->html_body)
                                        {!! $message->html_body !!}
                                    @elseif($message->text_body)
                                        <pre style="white-space:pre-wrap;font-family:inherit;">{{ $message->text_body }}</pre>
                                    @else
                                        <span class="text-muted">{{ __('(no content)') }}</span>
                                    @endif
                                </div>

                                @php($files = $message->attachments->where('is_inline', false))
                                @if($files->count())
                                    <hr/>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($files as $file)
                                            <a href="{{ route('admin.email-inbox.attachment.download', $file->id) }}"
                                               class="site-badge primary">
                                                <i data-lucide="paperclip" style="width:14px;height:14px;"></i>
                                                {{ $file->filename }}
                                                ({{ number_format($file->size / 1024, 1) }} KB)
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @can('email-inbox-send')
                        <div class="site-card">
                            <div class="site-card-header">
                                <h3 class="title">{{ __('Reply') }}</h3>
                            </div>
                            <div class="site-card-body">
                                <form action="{{ route('admin.email-inbox.reply', $email->id) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <p class="small text-muted">
                                        {{ __('Replying to') }} <strong>{{ $email->from_address }}</strong>
                                        {{ __('as') }} <strong>{{ $email->emailAddress->email ?? '' }}</strong>
                                    </p>
                                    <div class="site-input-groups">
                                        <textarea name="body" class="summernote" required></textarea>
                                    </div>
                                    <div class="site-input-groups">
                                        <input type="file" class="box-input" name="attachments[]" multiple/>
                                    </div>
                                    <button type="submit" class="site-btn primary-btn"><i data-lucide="reply"></i> {{ __('Send Reply') }}</button>
                                </form>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
