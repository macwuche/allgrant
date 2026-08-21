@extends('backend.layouts.app')
@section('title')
    {{ __('Grant Details') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Grant Details') }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-6">
                    <div class="site-card">
                        <div class="site-card-header">
                            <h4 class="title-small">{{ __('Plan Information') }}</h4>
                        </div>
                        <div class="site-card-body">
                            <div class="profile-text-data">
                                <div class="attribute">{{ __('Name') }}</div>
                                <div class="value">{{ $grant->plan->name }}</div>
                            </div>
                            <div class="profile-text-data">
                                <div class="attribute">{{ __('Approval Days') }}</div>
                                <div class="value">{{ $grant->plan->approval_days ?? 0 }} {{ __('Business Days') }}</div>
                            </div>
                            <div class="profile-text-data">
                                <div class="attribute">{{ __('Grant Amount') }}</div>
                                <div class="value">
                                    <div class="site-badge success">{{ $currencySymbol . $grant->amount }}</div>
                                </div>
                            </div>
                            <div class="profile-text-data">
                                <div class="attribute">{{ __('Application Charge Paid') }}</div>
                                <div class="value">{{ $currencySymbol . $grant->plan->applicationFee($grant->amount) }}</div>
                            </div>
                            <div class="profile-text-data">
                                <div class="attribute">{{ __('Status') }}</div>
                                <div class="value">
                                    @include('backend.grant.include.__grant_status', [
                                        'status' => $grant->status->value,
                                    ])
                                </div>
                            </div>
                            @if ($grant->status == App\Enums\GrantStatus::Approved)
                                <div class="profile-text-data">
                                    <div class="attribute">{{ __('Commission Deducted') }}</div>
                                    <div class="value">{{ $currencySymbol . $grant->commission_amount }}</div>
                                </div>
                                <div class="profile-text-data">
                                    <div class="attribute">{{ __('Net Amount Disbursed') }}</div>
                                    <div class="value">
                                        <div class="site-badge success">{{ $currencySymbol . $grant->net_amount }}</div>
                                    </div>
                                </div>
                                <div class="profile-text-data">
                                    <div class="attribute">{{ __('Approved At') }}</div>
                                    <div class="value">{{ $grant->approved_at?->format('d M Y h:i A') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="site-card">
                        <div class="site-card-header">
                            <h4 class="title-small">{{ __('Grant Request Information') }}</h4>
                        </div>
                        <div class="site-card-body">
                            @foreach (json_decode($grant->submitted_data) as $key => $value)
                                <li class="profile-text-data">
                                    <div class="attribute"> {{ $key }}</div>
                                    <div class="value">
                                        @if ($value != new stdClass())
                                            @if (file_exists(base_path('assets/' . $value)))
                                                {{-- <img src="{{ asset($value) }}" alt=""/> --}}
                                                <a href="{{ asset($value) }}" class="nav-link p-0"
                                                    target="_blank">{{ __('Click here to view') }}</a>
                                            @else
                                                <strong>{{ $value }}</strong>
                                            @endif
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if ($grant->status == App\Enums\GrantStatus::Reviewing)
                    @can('grant-approval')
                        <form action="{{ route('admin.grant.approval.action', $grant->id) }}" method="post">
                            @csrf

                            <div class="action-btns">
                                <button type="submit" name="status" value="approved" class="site-btn-sm primary-btn me-2">
                                    <i data-lucide="check"></i>
                                    {{ __('Approve') }}
                                </button>
                                <button type="submit" name="status" value="rejected" class="site-btn-sm red-btn">
                                    <i data-lucide="x"></i>
                                    {{ __('Reject') }}
                                </button>
                            </div>

                        </form>
                    @endcan
                @endif
            </div>
        </div>


    </div>
@endsection
