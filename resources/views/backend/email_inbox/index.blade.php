@extends('backend.layouts.app')
@section('title')
    {{ __('Emails') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Emails') }}</h2>
                            <div class="d-flex gap-2">
                                <a href="javascript:void(0);" id="refreshInbox" class="title-btn">
                                    <i data-lucide="refresh-cw"></i> {{ __('Refresh') }}
                                </a>
                                @can('email-inbox-send')
                                    <a href="javascript:void(0);" class="title-btn" data-bs-toggle="modal" data-bs-target="#composeModal">
                                        <i data-lucide="pen-square"></i> {{ __('Compose') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-12 mb-3">
                    <div class="site-card">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Mailboxes') }}</h3>
                        </div>
                        <div class="site-card-body">
                            <ul class="list-unstyled mb-0" id="addressFilterList">
                                <li class="mb-2">
                                    <a href="{{ route('admin.email-inbox.index') }}"
                                       class="d-flex justify-content-between {{ request('address_id') ? '' : 'fw-bold' }}">
                                        {{ __('All Addresses') }}
                                        <span class="site-badge {{ request('address_id') ? '' : 'primary' }}">{{ $unreadCount }}</span>
                                    </a>
                                </li>
                                @foreach($addresses as $address)
                                    <li class="mb-2">
                                        <a href="{{ route('admin.email-inbox.index', ['address_id' => $address->id]) }}"
                                           class="{{ (int) request('address_id') === $address->id ? 'fw-bold' : '' }}">
                                            {{ $address->label ?: $address->email }}
                                            @if($address->is_default)
                                                <span class="site-badge success">{{ __('Default') }}</span>
                                            @endif
                                            <div class="small text-muted">{{ $address->email }}</div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8 col-12">
                    <div class="site-card">
                        <div class="site-card-header">
                            <form action="{{ route('admin.email-inbox.index') }}" method="get" class="w-100 d-flex gap-2">
                                @if(request('address_id'))
                                    <input type="hidden" name="address_id" value="{{ request('address_id') }}"/>
                                @endif
                                <input type="text" class="box-input" name="q" placeholder="{{ __('Search subject, sender...') }}"
                                       value="{{ request('q') }}"/>
                                <button type="submit" class="site-btn-sm primary-btn"><i data-lucide="search"></i></button>
                            </form>
                        </div>
                        <div class="site-card-body">
                            <div class="notification-list" id="emailList">
                                @forelse($emails as $email)
                                    <div @class(['single-list', 'read' => $email->thread_unread_count === 0])
                                         data-email-id="{{ $email->id }}" data-thread-key="{{ $email->thread_key }}">
                                        <a href="{{ route('admin.email-inbox.show', $email->id) }}" class="cont text-decoration-none text-reset">
                                            <div class="icon">
                                                <i data-lucide="{{ $email->direction === 'inbound' ? 'mail' : 'send' }}"></i>
                                            </div>
                                            <div class="contents">
                                                <strong>{{ $email->otherParty() }}</strong>
                                                — {{ $email->subject ?: __('(no subject)') }}
                                                @if($email->thread_message_count > 1)
                                                    <span class="site-badge">{{ $email->thread_message_count }}</span>
                                                @endif
                                                @if($email->attachments_count > 0)
                                                    <i data-lucide="paperclip" style="width:14px;height:14px;"></i>
                                                @endif
                                                <div class="text-muted small">{{ $email->snippet }}</div>
                                                <div class="time">{{ $email->created_at->diffForHumans() }}</div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="text-center py-4">{{ __('No emails yet.') }}</div>
                                @endforelse
                            </div>

                            {{ $emails->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('email-inbox-send')
        @include('backend.email_inbox.include.__compose', ['addresses' => $addresses])
    @endcan
@endsection

@section('script')
    <script>
        (function ($) {
            "use strict";

            var addressId = '{{ request('address_id') }}';
            var pollUrl = '{{ route('admin.email-inbox.poll') }}';
            var highestId = $('#emailList .single-list').first().data('email-id') || 0;

            function poll() {
                $.get(pollUrl, {address_id: addressId, after_id: highestId}, function (res) {
                    if (res.emails && res.emails.length) {
                        res.emails.slice().reverse().forEach(function (email) {
                            if (email.id > highestId) {
                                highestId = email.id;
                            }
                            // Each row here is a whole thread's latest message, not a lone
                            // email -- if that thread is already showing (e.g. it just got a
                            // new reply), drop the old row so the fresh one replaces it at
                            // the top instead of appearing twice.
                            $('#emailList .single-list[data-thread-key="' + email.thread_key + '"]').remove();

                            var attachmentIcon = email.has_attachments ? '<i data-lucide="paperclip" style="width:14px;height:14px;"></i>' : '';
                            var countBadge = email.message_count > 1 ? ' <span class="site-badge">' + email.message_count + '</span>' : '';
                            var readClass = email.unread ? '' : ' read';
                            var row = '<div class="single-list' + readClass + '" data-email-id="' + email.id + '" data-thread-key="' + email.thread_key + '">' +
                                '<a href="' + email.url + '" class="cont text-decoration-none text-reset">' +
                                '<div class="icon"><i data-lucide="' + (email.direction === 'inbound' ? 'mail' : 'send') + '"></i></div>' +
                                '<div class="contents"><strong>' + $('<div>').text(email.from).html() + '</strong> — ' +
                                $('<div>').text(email.subject || '{{ __('(no subject)') }}').html() + countBadge + ' ' + attachmentIcon +
                                '<div class="text-muted small">' + $('<div>').text(email.snippet || '').html() + '</div>' +
                                '<div class="time">' + email.created_at + '</div></div></a></div>';
                            $('#emailList').prepend(row);
                        });
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    }
                });
            }

            $('#refreshInbox').on('click', poll);
            setInterval(poll, 20000);

        })(jQuery);
    </script>
@endsection
