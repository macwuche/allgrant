@php
    $grant_plans = App\Models\GrantPlan::active()->get();
@endphp

<!-- Grant calculator section start -->
<section class="grant-calculator-section bg-sugar-milk section-space">
    <div class="container">
        <div class="row justify-content-center">
        <div class="section-title-wrapper text-center section-title-space">
            <h2 class="section-title mb-15">{{ $data['title_small'] }}</h2>
            <p class="description">{{ $data['title_big'] }}</p>
        </div>
        </div>
        <div class="row gy-50 align-items-center">
        <div class="col-xxl-6 col-xl-6 col-lg-6">
            <div class="grant-calculator-froms">
                <form action="#">
                    <div class="row gy-24">
                        <div class="col-lg-12">
                            <div class="contact-form-input">
                                <label for="plan">{{ __('Grant Plan') }}</label>
                                <select class="form-select select2defult" id="plan" name="grant_plan_id">
                                    <option selected disabled>{{ __('Select a Plan') }}</option>
                                    @foreach ($grant_plans as $grant_plan)
                                        <option
                                            value="{{ $grant_plan->id }}"
                                            data-name="{{ $grant_plan->name }}"
                                            data-min="{{ $grant_plan->minimum_amount }}"
                                            data-max="{{ $grant_plan->maximum_amount }}"
                                            data-application-charge="{{ $grant_plan->grant_fee }}"
                                            data-application-charge-type="{{ $grant_plan->grant_fee_type }}"
                                            data-commission-charge="{{ $grant_plan->commission_charge }}"
                                            data-commission-charge-type="{{ $grant_plan->commission_charge_type }}"
                                            data-approval-days="{{ $grant_plan->approval_days }}"
                                        >
                                            {{ $grant_plan->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="contact-form-input">
                                <label for="grantAmount">{{ __('Grant Amount') }}</label>
                                <div class="input-inner">
                                    <input class="input" type="number" id="grantAmount" name="grant_amount" placeholder="{{ __('Enter Grant Amount') }}" value="0" min="0" step="0.01" disabled>
                                    <div class="input-currency">
                                        {{ setting('site_currency', 'global') }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-danger min-max mt-1 fs-14">{{ __('First select a plan to execute grant amount') }}</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-6 col-lg-6">
            <div class="grant-result-box">
                <h3 class="grant-title">{{ __('Calculation Result') }}</h3>
                <div class="grant-inner">
                    <div class="installment-lists">

                        <div class="installment-item">
                            <div class="icon">
                                <img src="{{ asset('front/theme-2') }}/images/icons/star.png" alt="Grant Icon">
                            </div>
                            <div class="contents">
                                <div class="result-label">{{ __('Grant Amount') }}</div>
                                <h4 class="result-value" id="showGrantAmount">{{ setting('currency_symbol', 'global') }}0.00</h4>
                            </div>
                        </div>

                        <div class="installment-item">
                            <div class="icon">
                                <img src="{{ asset('front/theme-2') }}/images/icons/star.png" alt="Grant Icon">
                            </div>
                            <div class="contents">
                                <div class="result-label">{{ __('Application Charge') }}</div>
                                <h4 class="result-value" id="showApplicationFee">{{ setting('currency_symbol', 'global') }}0.00</h4>
                            </div>
                        </div>
                        </div>
                    <div class="grant-amount mt-4">
                        <div class="result-label">{{ __("Net Amount You'll Receive") }}</div>
                        <h4 class="result-value" id="showNetAmount">{{ setting('currency_symbol', 'global') }}0.00</h4>
                    </div>
                    <div class="installment-lists">

                        <div class="installment-item">
                            <div class="icon">
                                <img src="{{ asset('front/theme-2') }}/images/icons/star.png" alt="Grant Icon">
                            </div>
                            <div class="contents">
                                <div class="result-label">{{ __('Commission Charge') }}</div>
                                <h4 class="result-value" id="showCommissionAmount">{{ setting('currency_symbol', 'global') }}0.00</h4>
                            </div>
                        </div>

                        <div class="installment-item">
                            <div class="icon">
                                <img src="{{ asset('front/theme-2') }}/images/icons/star.png" alt="Grant Icon">
                            </div>
                            <div class="contents">
                                <div class="result-label">{{ __('Approval Time') }}</div>
                                <h4 class="result-value" id="showApprovalDays">0 days</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>
<!-- Grant calculator section end -->

@section('script')
<script>
    let selectedPlanData = null;

    const currency = "{{ setting('site_currency', 'global') ?? 'USD' }}";
    const currencySymbol = "{{ setting('currency_symbol', 'global') ?? '$' }}";

    $('#plan').on('change', function () {
        const selectedOption = $(this).find('option:selected');

        selectedPlanData = {
            min: parseFloat(selectedOption.data('min')),
            max: parseFloat(selectedOption.data('max')),
            applicationChargeRate: parseFloat(selectedOption.data('application-charge')),
            applicationChargeType: selectedOption.data('application-charge-type'),
            commissionRate: parseFloat(selectedOption.data('commission-charge')),
            commissionType: selectedOption.data('commission-charge-type'),
            approvalDays: selectedOption.data('approval-days') || 0,
            name: selectedOption.data('name')
        };

        $('#grantAmount').prop('disabled', false).val('');

        $('.min-max').text(`Minimum ${selectedPlanData.min} ${currency} and Maximum ${selectedPlanData.max} ${currency}`);
        $('#showApprovalDays').text(`${selectedPlanData.approvalDays} days`);

        resetResults();
    });

    $('#grantAmount').on('input', function () {
        if (!selectedPlanData) return;

        const value = $(this).val();
        let grantAmount = parseFloat(value);

        if (isNaN(grantAmount) || grantAmount < selectedPlanData.min || grantAmount > selectedPlanData.max) {
            $(this).css('border-color', 'red');
            resetResults();
            $('.min-max').text(`Minimum ${selectedPlanData.min} ${currency} and Maximum ${selectedPlanData.max} ${currency}`);
            return;
        }

        $(this).css('border-color', '#e2e8f0');
        $('.min-max').text('');
        calculateGrant(grantAmount);
    });

    function calculateGrant(grantAmount) {
        const applicationFee = selectedPlanData.applicationChargeType === 'percentage' ?
            parseFloat(((grantAmount / 100) * selectedPlanData.applicationChargeRate).toFixed(2)) :
            selectedPlanData.applicationChargeRate;

        const commissionAmount = selectedPlanData.commissionType === 'percentage' ?
            parseFloat(((grantAmount / 100) * selectedPlanData.commissionRate).toFixed(2)) :
            selectedPlanData.commissionRate;

        const netAmount = parseFloat((grantAmount - commissionAmount).toFixed(2));

        $('#showGrantAmount').text(`${currencySymbol}${grantAmount.toFixed(2)}`);
        $('#showApplicationFee').text(`${currencySymbol}${applicationFee.toFixed(2)}`);
        $('#showCommissionAmount').text(`${currencySymbol}${commissionAmount.toFixed(2)}`);
        $('#showNetAmount').text(`${currencySymbol}${netAmount.toFixed(2)}`);
    }

    function resetResults() {
        $('#showGrantAmount').text(`${currencySymbol}0.00`);
        $('#showApplicationFee').text(`${currencySymbol}0.00`);
        $('#showCommissionAmount').text(`${currencySymbol}0.00`);
        $('#showNetAmount').text(`${currencySymbol}0.00`);
    }
</script>
@endsection

@push('js')
<script>
    "use strict";
    $(function () {
        // Initialize Select2 select2defult
        $('.select2defult').each(function () {
            $(this).select2({
                dropdownParent: $(this).parent(),
                escapeMarkup: function (markup) {
                    return markup;
                }
            });
        });
    });
</script>
@endpush
