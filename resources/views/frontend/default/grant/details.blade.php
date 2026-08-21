@extends('frontend::layouts.user')
@section('title')
    {{ __('Grant Details') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-12">
            <div class="site-card">
                <div class="site-card-header">
                    <div class="title-small">{{ __('Grant Details:') }}</div>
                    @if ($grant->status == App\Enums\GrantStatus::Reviewing)
                        <div class="card-header-links d-flex">
                            <a href="#" data-id="{{ $grant->grant_no }}" class="bg-danger card-header-link cancelBtn">
                                <i data-lucide="x"></i>
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    @endif
                </div>
                <div class="site-card-body p-0 overflow-x-auto">
                    <div class="site-custom-table site-custom-table-sm">
                        <div class="contents">
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Plan Name:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">{{ $grant->plan->name }}</div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Grant ID:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">{{ $grant->grant_no }}</div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Status:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">
                                        @if ($grant->status->value == 'approved')
                                            <div class="type site-badge badge-success">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @elseif($grant->status->value == 'rejected' || $grant->status->value == 'cancelled')
                                            <div class="type site-badge badge-failed">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @elseif($grant->status->value == 'reviewing')
                                            <div class="type site-badge badge-pending">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Amount:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">{{ $grant->amount . ' ' . $currency }}</div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Application Charge Paid:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">{{ $grant->plan->applicationFee($grant->amount) . ' ' . $currency }}</div>
                                </div>
                            </div>
                            @if ($grant->status == App\Enums\GrantStatus::Approved)
                                <div class="site-table-list">
                                    <div class="site-table-col">
                                        <div class="trx fw-bold">{{ __('Commission Deducted:') }}</div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="red-color fw-bold">{{ $grant->commission_amount . ' ' . $currency }}</div>
                                    </div>
                                </div>
                                <div class="site-table-list">
                                    <div class="site-table-col">
                                        <div class="trx fw-bold">{{ __('Net Amount Received:') }}</div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="fw-bold">{{ $grant->net_amount . ' ' . $currency }}</div>
                                    </div>
                                </div>
                                <div class="site-table-list">
                                    <div class="site-table-col">
                                        <div class="trx fw-bold">{{ __('Approved At:') }}</div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="fw-bold">{{ $grant->approved_at?->format('d M Y h:i A') }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($grant->status == App\Enums\GrantStatus::Reviewing)
                <!-- Modal for Delete Box -->
                <div class="modal fade" id="cancelGrant" tabindex="-1" aria-labelledby="cancelGrantModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content site-table-modal">
                            <div class="modal-body popup-body">
                                <button type="button" class="modal-btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"><i data-lucide="x"></i></button>
                                <div class="popup-body-text centered">
                                    <div class="info-icon">
                                        <i data-lucide="alert-triangle"></i>
                                    </div>
                                    <div class="title">
                                        <h4>{{ __('Are you sure?') }}</h4>
                                    </div>
                                    <p>
                                        {{ __('You want to Cancel this Grant?') }}
                                    </p>
                                    <div class="action-btns">
                                        <a href="" class="site-btn-sm primary-btn me-2 confirm_btn">
                                            <i data-lucide="check"></i>
                                            {{ __('Confirm') }}
                                        </a>
                                        <a href="" class="site-btn-sm red-btn" data-bs-dismiss="modal"
                                            aria-label="Close">
                                            <i data-lucide="x"></i>
                                            {{ __('Cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal for Delete Box End-->
            @endif
        </div>
    </div>
    @push('js')
        <script>
            "use strict";

            $(document).on('click', '.cancelBtn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var fee = $(this).data('fee');

                $('.cancel_fee').text(fee)

                var url = "{{ route('user.grant.cancel', ['id' => ':id']) }}";
                url = url.replace(':id', id);
                $('.confirm_btn').attr('href', url);

                $('#cancelGrant').modal('show');
            });
        </script>
    @endpush
@endsection
