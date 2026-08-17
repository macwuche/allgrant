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
                                        @if ($grant->status->value == 'running')
                                            <div class="type site-badge badge-primary">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @elseif($grant->status->value == 'rejected' || $grant->status->value == 'cancelled')
                                            <div class="type site-badge badge-failed">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @elseif($grant->status->value == 'completed')
                                            <div class="type site-badge badge-success">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @elseif($grant->status->value == 'due')
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
                                    <div class="fw-bold">{{ __('Per Installment:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">
                                        <div class="fw-bold">{{ $grant->perInstallment() . ' ' . $currency }} (Every
                                            {{ $grant->plan->installment_intervel }} Days)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Number Of Installments:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">{{ $grant->plan->total_installment }} {{ __('Times') }}</div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Given Installments:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold"><span
                                            class="type site-badge badge-primary">{{ $grant->givenInstallemnt() ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Next Installment:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">
                                        @if ($grant->status == App\Enums\GrantStatus::Reviewing)
                                            -
                                        @else
                                            {{ nextInstallment($grant->id, \App\Models\GrantTransaction::class, 'grant_id') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Deferment Charge:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="red-color fw-bold">{{ $grant->plan->charge }}
                                        {{ $grant->plan->charge_type == 'percentage' ? '%' : $currency }} /
                                        {{ $grant->plan->delay_days }} Day</div>
                                </div>
                            </div>
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ __('Total Payable Amount:') }}</div>
                                </div>
                                <div class="site-table-col">
                                    <div class="fw-bold">{{ $grant->totalPayableAmount() . ' ' . $currency }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($grant->transactions->count() > 0)
                <div class="site-card">
                    <div class="site-card-header">
                        <div class="title-small">
                            {{ __('Installments List') }}
                        </div>
                        @if ($grant->status == App\Enums\GrantStatus::Running || $grant->status == App\Enums\GrantStatus::Due)
                            <div class="card-header-links d-flex">
                                <button type="button"
                                    data-url="{{ route('user.grant.pay.installment', ['grant_id' => encrypt($grant->id)]) }}"
                                    class="site-btn-sm polis-btn payGrantInstallment">
                                    <i data-lucide="send"></i>
                                    {{ __('Full Installment Pay') }}
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="site-card-body p-0 overflow-x-auto">
                        <div class="site-custom-table">
                            <div class="contents">
                                <div class="site-table-list site-table-head">
                                    <div class="site-table-col">
                                        {{ __('Serial') }}
                                    </div>
                                    <div class="site-table-col">
                                        {{ __('Installment Date') }}
                                    </div>
                                    <div class="site-table-col">
                                        {{ __('Given Date') }}
                                    </div>
                                    <div class="site-table-col">
                                        {{ __('Paid Amount') }}
                                    </div>
                                    <div class="site-table-col">
                                        {{ __('Deferment') }}
                                    </div>
                                    <div class="site-table-col text-center">
                                        {{ __('Action') }}
                                    </div>
                                </div>
                                @foreach ($grant->transactions as $trx)
                                    <div class="site-table-list">
                                        <div class="site-table-col">
                                            <div class="trx fw-bold">{{ $loop->iteration }}</div>
                                        </div>
                                        <div class="site-table-col">
                                            <div class="trx fw-bold">{{ $trx->installment_date->format('d M Y') }}</div>
                                        </div>
                                        <div class="site-table-col">
                                            <div class="trx fw-bold">
                                                {{ $trx->given_date != null ? \Carbon\Carbon::parse($trx->given_date)->format('M d Y') : __('Yet to pay') }}
                                            </div>
                                        </div>
                                        <div class="site-table-col">
                                            <div class="trx fw-bold">
                                                {{ $trx->given_date != null ? $trx->paid_amount . ' ' . $currency : __('Yet to pay') }}
                                            </div>
                                        </div>
                                        <div class="site-table-col">
                                            <div class="trx fw-bold">
                                                {{ $trx->given_date != null ? $trx->deferment : 'None' }}</div>
                                        </div>
                                        <div class="site-table-col text-center">
                                            @if ($trx->given_date == null)
                                                <button type="button"
                                                    data-url="{{ route('user.grant.pay.installment', ['grant_id' => encrypt($grant->id), 'trans_id' => encrypt($trx->id)]) }}"
                                                    class="site-btn-sm polis-btn payGrantInstallment">
                                                    <i data-lucide="send"></i>
                                                    {{ __('Pay Installment') }}
                                                </button>
                                            @else
                                                <div class="type site-badge badge-primary">{{ __('Success') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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

            <div class="modal fade" id="payGrantInstallmentModal" tabindex="-1" aria-labelledby="paygrantModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content site-table-modal">
                        <div class="modal-body popup-body">
                            <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <i data-lucide="x"></i>
                            </button>
                            <div class="popup-body-text centered">
                                <div class="info-icon">
                                    <i data-lucide="alert-triangle"></i>
                                </div>
                                <div class="title">
                                    <h4>{{ __('Are you sure?') }}</h4>
                                </div>
                                <p>
                                    {{ __('You want to pay grant installment?') }}
                                </p>
                                <div class="action-btns">
                                    <a href="" class="site-btn-sm primary-btn me-2 confirm_pay_installment_btn">
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

            $(document).on('click', '.payGrantInstallment', function(e) {
                var url = $(this).data('url');
                $('.confirm_pay_installment_btn').attr('href', url);

                $('#payGrantInstallmentModal').modal('show');
            });
        </script>
    @endpush
@endsection
