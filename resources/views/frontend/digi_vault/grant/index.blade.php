@extends('frontend::layouts.user')
@section('title')
    {{ __('Grant') }}
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('front/css/grant-plan.css') }}?v=1" />
@endpush
@section('content')
    <div class="grant-plans">
        <div class="gp-toolbar">
            <div class="gp-toolbar-title">{{ __('Grant Plans') }}</div>
            <a href="{{ route('user.grant.history') }}" class="gp-history-link"><i
                    data-lucide="archive"></i>{{ __('My Grants') }}</a>
        </div>

        <div class="gp-grid">
            @foreach ($plans as $plan)
                <div class="gp-card">
                    <div class="gp-card-title">{{ __($plan->name) }}</div>
                    <div class="gp-card-desc">{{ Str::limit(strip_tags($plan->instructions), 140) }}</div>
                    <div class="gp-divider"></div>

                    <div class="gp-row">
                        <div class="gp-icon"><i data-lucide="banknote"></i></div>
                        <div class="gp-row-body">
                            <div class="gp-row-label">{{ __('Apply up to:') }}</div>
                            <div class="gp-row-desc">
                                {{ __('Request funding within the range below, subject to eligibility and approval.') }}
                            </div>
                        </div>
                        <div class="gp-row-value">
                            <div class="gp-value-label">{{ __('Minimum Grant:') }}</div>
                            <div class="gp-value">{{ $plan->minimum_amount }} {{ $currency }}</div>
                            <div class="gp-value-label">{{ __('Maximum Grant:') }}</div>
                            <div class="gp-value">{{ $plan->maximum_amount }} {{ $currency }}</div>
                        </div>
                    </div>

                    <div class="gp-row">
                        <div class="gp-icon"><i data-lucide="calendar"></i></div>
                        <div class="gp-row-body">
                            <div class="gp-row-label">{{ __('Approval Days or Date:') }}</div>
                            <div class="gp-row-desc">
                                {{ __('Our team will review your application and respond within the estimated time.') }}
                            </div>
                        </div>
                        <div class="gp-row-value">
                            <div class="gp-value">{{ $plan->approval_days ?? 0 }} {{ __('Business Days') }}</div>
                        </div>
                    </div>

                    <div class="gp-row">
                        <div class="gp-icon"><i data-lucide="file-text"></i></div>
                        <div class="gp-row-body">
                            <div class="gp-row-label">{{ __('Application Charge:') }}</div>
                            <div class="gp-row-desc">
                                {{ __('A non-refundable fee is required to process and review your application. This covers administrative and verification costs.') }}
                            </div>
                        </div>
                        <div class="gp-row-value">
                            <div class="gp-value">{{ $plan->grant_fee }}{{ $plan->grant_fee_type == 'percentage' ? '%' : ' ' . $currency }}</div>
                            <div class="gp-value-sub">
                                {{ $plan->grant_fee_type == 'percentage' ? __('Of Requested Grant Amount') : __('Fixed Fee') }}
                            </div>
                        </div>
                    </div>

                    <div class="gp-row">
                        <div class="gp-icon"><i data-lucide="handshake"></i></div>
                        <div class="gp-row-body">
                            <div class="gp-row-label">{{ __('Commission Charge:') }}</div>
                            <div class="gp-row-desc">
                                {{ __('A fee is applied once your grant is approved. This covers organization fees and administration for managing and supporting your grant process to success.') }}
                            </div>
                        </div>
                        <div class="gp-row-value">
                            <div class="gp-value">{{ $plan->commission_charge }}{{ $plan->commission_charge_type == 'percentage' ? '%' : ' ' . $currency }}</div>
                            <div class="gp-value-sub">{{ __('After Grant Approval') }}</div>
                        </div>
                    </div>

                    <a href="" class="gp-apply-btn subscribeBtn" type="button" data-name="{{ $plan->name }}"
                        data-id="{{ encrypt($plan->id) }}" data-min="{{ $plan->minimum_amount }}"
                        data-max="{{ $plan->maximum_amount }}" data-bs-toggle="modal" data-bs-target="#fdr">
                        <i data-lucide="check"></i>
                        {{ __('Apply Grant') }}
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Modal for Grant Apply-->
        <div class="modal fade" id="fdr" tabindex="-1" aria-labelledby="fdrModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content site-table-modal">
                    <div class="modal-body popup-body">
                        <button type="button" class="modal-btn-close" data-bs-dismiss="modal"
                            aria-label="Close"><i data-lucide="x"></i></button>
                        <div class="popup-body-text">
                            <form action="{{ route('user.grant.subscribe') }}" method="GET">

                                <input type="hidden" name="grant_id" id="grant_id">
                                <div class="title" id="name"></div>
                                <div class="modal-beneficiary-details">

                                    <div class="step-details-form">
                                        <div class="inputs">
                                            <label for=""
                                                class="input-label">{{ __('Enter Amount') }}<span
                                                    class="required">*</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="amount">
                                                <span class="input-group-text">{{ $currency }}</span>
                                            </div>
                                            <div class="input-info-text min-max"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="action-btns mt-3">
                                    <button type="submit" class="site-btn-sm polis-btn me-2 w-100 applyBtn">
                                        <i data-lucide="check"></i>
                                        {{ __('Apply Now') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for Grant Apply end-->
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            "use strict"

            const currency = @json($currency);

            // Click Subscribe Btn
            $(document).on('click', '.subscribeBtn', function(e) {
                e.preventDefault();

                var id = $(this).data('id');
                var name = $(this).data('name');
                var min = $(this).data('min');
                var max = $(this).data('max');

                $('#name').text(name);

                var message = `Minimum ${min} ${currency} and Maximum ${max} ${currency}`;

                $('.min-max').text(message);

                $('#grant_id').val(id);

                var url = "{{ route('user.grant.application', ['id' => ':id']) }}";
                url = url.replace(':id', id);
                $('form').attr('action', url);
            });
        })
    </script>
@endsection
