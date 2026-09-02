@extends('frontend::layouts.user')
@section('title')
    {{ __('Withdraw Now') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-12">
            <div class="site-card">
                <div class="site-card-header">
                    <div class="title">{{ __('Withdraw Money') }}</div>
                    <div class="card-header-links">
                        <div class="d-flex">
                            <a href="{{ route('user.withdraw.account.index') }}" class="card-header-link"
                            ><i data-lucide="alert-circle"></i>{{ __('Withdraw Account') }}</a
                            >
                            <a href="{{ route('user.withdraw.log') }}" class="card-header-link">
                                <i data-lucide="alert-circle"></i>
                                {{ __('Withdraw History') }}
                            </a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('user.withdraw.now') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="withdraw_account" id="withdrawAccountInput" value="">
                    <input type="hidden" name="withdraw_method_id" id="withdrawMethodInput" value="">
                    <div class="site-card-body">
                        <div class="step-details-form mb-4">

                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6">
                                    <div class="inputs">
                                        <label for="" class="input-label">
                                            {{ __('Select') }}
                                            <span class="required">*</span>
                                        </label>
                                        <select
                                            class="box-input select2-basic-active"
                                            id="withdrawAccountId"
                                        >
                                            <option selected disabled>{{ __('Select Account') }}</option>
                                            @if($accounts->count())
                                                <optgroup label="{{ __('Saved Accounts') }}">
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" data-type="account">{{ $account->method_name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            <optgroup label="{{ __('Withdraw To A New Account') }}">
                                                @foreach($withdrawMethods as $method)
                                                    <option value="{{ $method->id }}" data-type="method">{{ $method->name }} ({{ ucwords($method->type) }})</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                        <div class="input-info-text processing-time">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 amountCol">
                                    <div class="inputs">
                                        <label for="" class="input-label">
                                            {{ __('Enter Amount') }}
                                            <span class="required">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control withdrawAmount" name="amount"/>
                                            <span class="input-group-text">{{ $currency }}</span>
                                        </div>
                                        <div class="input-info-text withdrawAmountRange">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="site-card">
                            <div class="site-card-header">
                                <div class="title-small">{{ __('Preview:') }}</div>
                            </div>
                            <div class="site-card-body p-0 overflow-x-auto">
                                <div class="site-custom-table site-custom-table-sm">
                                    <div class="contents">
                                        <div class="site-table-list">
                                            <div class="site-table-col">
                                                <div class="trx fw-bold">{{ __('Amount:') }}</div>
                                            </div>
                                            <div class="site-table-col">
                                                <div class="fw-bold withdrawAmount"></div>
                                            </div>
                                        </div>
                                        <div class="site-table-list">
                                            <div class="site-table-col">
                                                <div class="trx fw-bold">{{ __('Charge:') }}</div>
                                            </div>
                                            <div class="site-table-col">
                                                <div class="red-color fw-bold withdrawFee"></div>
                                            </div>
                                        </div>
                                        <div class="site-table-list">
                                            <div class="site-table-col">
                                                <div class="trx fw-bold">{{ __('Payment Method:') }}</div>
                                            </div>
                                            <div class="site-table-col">
                                                <div class="fw-bold method">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="site-table-list">
                                            <div class="site-table-col">
                                                <div class="trx fw-bold">
                                                    {{ __('Payment Method Logo:') }}
                                                </div>
                                            </div>
                                            <div class="site-table-col">
                                                <div class="fw-bold" id="logo">
                                                    <img class="table-icon" src="" alt=""/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="site-table-list">
                                            <div class="site-table-col">
                                                <div class="fw-bold">{{ __('Conversion Rate:') }}</div>
                                            </div>
                                            <div class="site-table-col">
                                                <div class="fw-bold conversion-rate"></div>
                                            </div>
                                        </div>
                                        <div class="site-table-list">
                                            <div class="site-table-col">
                                                <div class="trx fw-bold">{{ __('Total:') }}</div>
                                            </div>
                                            <div class="site-table-col">
                                                <div class="fw-bold pay-amount"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button
                            @if(auth()->user()->passcode !== null && setting('withdraw_passcode_status'))
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#passcode"
                            @else
                            type="submit"
                            @endif
                            class="site-btn polis-btn"
                        >
                            <i data-lucide="download"></i>{{ __('Withdraw Money') }}
                        </button>
                    </div>
                    @if(auth()->user()->passcode !== null && setting('withdraw_passcode_status'))
                    <div class="modal fade" id="passcode" tabindex="-1" aria-labelledby="passcodeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered">
                            <div class="modal-content site-table-modal">
                                <div class="modal-body popup-body">
                                    <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <i data-lucide="x"></i>
                                    </button>
                                    <div class="popup-body-text">
                                        <div class="title">{{ __('Confirm Your Passcode') }}</div>
                                        <div class="step-details-form">
                                            <div class="row">
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="inputs">
                                                        <label for="" class="input-label">{{ __('Passcode') }}<span class="required">*</span></label>
                                                        <input type="password" class="box-input" name="passcode" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="action-btns">
                                            <button type="submit" class="site-btn-sm primary-btn me-2">
                                                <i data-lucide="check"></i>
                                                {{ __('Confirm') }}
                                            </button>
                                            <button type="button" class="site-btn-sm red-btn" data-bs-dismiss="modal" aria-label="Close">
                                                <i data-lucide="x"></i>
                                                {{ __('Close') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')

    <script>
        "use strict";

        // Select 2 activation
        $(".select2-basic-active").select2({
            minimumResultsForSearch: Infinity,
        });

        // nice select
        $(".add-beneficiary").niceSelect();
        $(".edit-beneficiary").niceSelect();

        var currency = @json($currency);
        var info = [];

        $('.method').hide();

        function showMethodPreview(info) {
            $('.withdrawAmountRange').text(info.range)
            $('.processing-time').text(info.processing_time);
            $('.method').html('<span class="type site-badge badge-primary">'+info.name+'</span>');
            $('.method').show();
            $('#logo').html(info.logo);
        }

        $("#withdrawAccountId").on('change', function (e) {
            e.preventDefault();

            $('.dynamic-fields').remove();
            $('#withdrawAccountInput').val('');
            $('#withdrawMethodInput').val('');

            var selected = $(this).find(':selected');
            var type = selected.data('type');
            var id = $(this).val();
            var amount = $('.withdrawAmount').val();

            if (isNaN(id)) {
                return;
            }

            if (type === 'account') {
                // Existing saved withdraw account — nothing more to fill in, just preview it.
                $('#withdrawAccountInput').val(id);

                var url = '{{ route("user.withdraw.details",['accountId' => ':accountId', 'amount' => ':amount']) }}';
                url = url.replace(':accountId', id);
                url = url.replace(':amount', amount);

                $.get(url, function (data) {
                    info = data.info;
                    showMethodPreview(info);
                })
            } else if (type === 'method') {
                // No saved account for this method yet — load its credential fields inline so
                // the account gets created and the withdrawal submitted together, in one go.
                $('#withdrawMethodInput').val(id);

                var url = '{{ route("user.withdraw.method.details", ':id') }}';
                url = url.replace(':id', id);

                $.get(url, function (data) {
                    $(data.html).addClass('dynamic-fields').insertAfter(".amountCol");
                    imagePreview();
                    info = data.info;
                    showMethodPreview(info);
                })
            }

        })

        $(".withdrawAmount").on('keyup', function (e) {
            "use strict"
            e.preventDefault();
            var amount = $(this).val()
            var charge = info.charge_type === 'percentage' ? calPercentage(amount, info.charge) : info.charge
            $('.withdrawAmount').text(amount + ' ' + currency)
            $('.withdrawFee').text('-' + charge + ' ' + currency)
            $('.processing-time').text(info.processing_time)
            $('.conversion-rate').text('1' + ' ' + currency + ' = ' + info.rate + ' ' + info.pay_currency)
            $('.withdrawAmountRange').text(info.range)
            $('.pay-amount').text(amount * info.rate + ' ' + info.pay_currency)
        });

    </script>
@endsection
