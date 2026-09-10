@extends('backend.setting.index')
@section('setting-title')
    {{ __('Email Inbox') }}
@endsection
@section('title')
    {{ __('Email Inbox') }}
@endsection
@section('setting-content')

    <div class="col-xl-8 col-lg-12 col-md-12 col-12">
        <div class="site-card">
            <div class="site-card-header">
                <h3 class="title">{{ __('Email Inbox Settings') }}</h3>
            </div>
            <div class="site-card-body">
                <p class="paragraph mb-4">
                    {{ __('Turns on a Gmail-style inbox on the admin dashboard, relayed through') }}
                    <strong>Resend</strong>. {{ __('Verify a domain and enable receiving in Resend first (see the runbook in email.md), then paste its details below.') }}
                </p>

                <form action="{{ route('admin.settings.update') }}" method="post">
                    @csrf
                    <input type="hidden" name="section" value="email_inbox">

                    <div class="site-input-groups row">
                        <div class="col-sm-4 col-label pt-0">{{ __('Email Inbox System') }}</div>
                        <div class="col-sm-8">
                            <div class="form-switch ps-0">
                                <input type="hidden" value="0" name="email_inbox_enabled"/>
                                <div class="switch-field same-type m-0">
                                    <input type="radio" id="email_inbox_enabled" name="email_inbox_enabled" value="1"
                                           @if(setting('email_inbox_enabled','email_inbox')) checked @endif/>
                                    <label for="email_inbox_enabled">{{ __('Enable') }}</label>
                                    <input type="radio" id="disable-email_inbox_enabled" name="email_inbox_enabled" value="0"
                                           @if(!setting('email_inbox_enabled','email_inbox')) checked @endif/>
                                    <label for="disable-email_inbox_enabled">{{ __('Disabled') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="site-input-groups row">
                        <div class="col-sm-4 col-label">{{ __('Verified Receiving Domain') }}</div>
                        <div class="col-sm-8">
                            <input type="text" class="box-input" name="email_inbox_domain"
                                   placeholder="mail.{{ request()->getHost() }}"
                                   value="{{ old('email_inbox_domain', setting('email_inbox_domain','email_inbox')) }}"/>
                        </div>
                    </div>

                    <div class="site-input-groups row">
                        <div class="col-sm-4 col-label">{{ __('Resend API Key') }}</div>
                        <div class="col-sm-8">
                            <input type="password" class="box-input" name="email_inbox_api_key" autocomplete="new-password"
                                   value="{{ !config('app.demo') ? old('email_inbox_api_key', setting('email_inbox_api_key','email_inbox')) : 'demo-mode' }}"/>
                        </div>
                    </div>

                    <div class="site-input-groups row">
                        <div class="col-sm-4 col-label">{{ __('Resend Webhook Signing Secret') }}</div>
                        <div class="col-sm-8">
                            <input type="password" class="box-input" name="email_inbox_webhook_secret" autocomplete="new-password"
                                   value="{{ !config('app.demo') ? old('email_inbox_webhook_secret', setting('email_inbox_webhook_secret','email_inbox')) : 'demo-mode' }}"/>
                        </div>
                    </div>

                    <div class="site-input-groups row">
                        <div class="col-sm-4 col-label">{{ __('Webhook URL') }}
                            <i data-lucide="info" data-bs-toggle="tooltip" title=""
                               data-bs-original-title="Register this exact URL in Resend's Dashboard → Webhooks → Add Webhook, event: email.received"></i>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="box-input" value="{{ route('webhook.resend.inbound') }}" readonly onclick="this.select()"/>
                        </div>
                    </div>

                    <div class="offset-sm-4 col-sm-8 col-12">
                        <button type="submit" class="site-btn-sm primary-btn w-100">
                            {{ __(' Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-12 col-md-12 col-12">
        <div class="site-card">
            <div class="site-card-header">
                <h3 class="title">{{ __('Email Addresses') }}</h3>
                <div class="card-header-links">
                    <a href="javascript:void(0);" class="card-header-link" data-bs-toggle="modal"
                       data-bs-target="#addEmailAddress"><i data-lucide="plus-circle"></i> {{ __('Add Address') }}</a>
                </div>
            </div>
            <div class="site-card-body">
                <p class="paragraph mb-4">
                    {{ __('Every address you add here is a mailbox under your verified domain (e.g. support@') }}{{ setting('email_inbox_domain','email_inbox') ?: 'mail.yourdomain.com' }}{{ __('). All of them share one inbox, filterable by address — like Gmail\'s "Send mail as" aliases.') }}
                </p>
                <div class="site-table table-responsive mb-0">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th scope="col">{{ __('Address') }}</th>
                            <th scope="col">{{ __('Label') }}</th>
                            <th scope="col">{{ __('Default') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($addresses as $address)
                            <tr>
                                <td><strong>{{ $address->email }}</strong></td>
                                <td>{{ $address->label ?: '-' }}</td>
                                <td>
                                    @if($address->is_default)
                                        <div class="site-badge success">{{ __('Default') }}</div>
                                    @else
                                        <form action="{{ route('admin.email-addresses.default', $address->id) }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="site-btn-sm primary-btn">{{ __('Set Default') }}</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.email-addresses.toggle-status', $address->id) }}" method="post" class="d-inline">
                                        @csrf
                                        <button type="submit" @class(['site-badge', 'success' => $address->status, 'danger' => !$address->status]) style="border:none;">
                                            {{ $address->status ? __('Active') : __('Inactive') }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('admin.email-addresses.destroy', $address->id) }}" method="post" class="d-inline"
                                          onsubmit="return confirm('{{ __('Remove this address?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="round-icon-btn red-btn"><i data-lucide="trash-2"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ __('No addresses added yet.') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEmailAddress" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content site-table-modal">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Email Address') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.email-addresses.store') }}" method="post">
                        @csrf
                        <div class="site-input-groups">
                            <label class="box-input-label">{{ __('Address') }}</label>
                            <input type="text" class="box-input" name="email"
                                   placeholder="support@{{ setting('email_inbox_domain','email_inbox') ?: 'mail.yourdomain.com' }}" required/>
                        </div>
                        <div class="site-input-groups">
                            <label class="box-input-label">{{ __('Label (optional)') }}</label>
                            <input type="text" class="box-input" name="label" placeholder="{{ __('e.g. Support') }}"/>
                        </div>
                        <button type="submit" class="site-btn primary-btn w-100">{{ __('Add Address') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
