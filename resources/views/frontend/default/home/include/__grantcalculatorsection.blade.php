@php
    $grant_plans = App\Models\GrantPlan::activeCached();
@endphp

<!-- Grant calculator area start -->
<section class="grant-calculator-area position-relative z-index-11 section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-title-wrapper section-title-space text-center">
                    <span data-aos="fade-up" data-aos-duration="1000" class="section-subtitle">{{ $data['title_small'] }}</span>
                    <h2 data-aos="fade-up" data-aos-duration="1500" class="section-title">
                        {{ $data['title_big'] }}
                    </h2>
                </div>
            </div>
        </div>

        <div class="calculator-main-wrapper" data-aos="fade-up" data-aos-duration="2000">
            <div class="form-section">
                <div class="form-group">
                    <label for="plan">{{ __('Grant Plan') }}</label>
                    <div class="input-item">
                        <select id="plan" name="grant_plan_id" class="box-input select2defult">
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

                <div class="form-group">
                    <label for="grantAmount">{{ __('Grant Amount') }}</label>
                    <div class="input-item">
                        <input type="number" id="grantAmount" name="grant_amount" placeholder="{{ __('Enter Grant Amount') }}" value="0" min="0" step="0.01" disabled>
                        <div class="input-currency">
                            {{ setting('site_currency', 'global') }}
                        </div>
                    </div>
                    <div class="input-text danger min-max mt-1 fs-14">{{ __('First select a plan to execute grant amount') }}</div>
                </div>
            </div>

            <div class="divider-line"></div>

            <div class="grant-result-wrapper" id="grantResultWrapper">
                <div class="result-card">
                    <div class="result-label">{{ __('Grant Amount') }}</div>
                    <h3 class="result-value" id="showGrantAmount">{{ setting('currency_symbol', 'global') }}0.00</h3>
                </div>

                <div class="result-card">
                    <div class="result-label">{{ __('Application Charge') }}</div>
                    <h3 class="result-value" id="showApplicationFee">{{ setting('currency_symbol', 'global') }}0.00</h3>
                </div>

                <div class="result-amount">
                    <div class="result-label">{{ __("Net Amount You'll Receive") }}</div>
                    <h3 class="result-value" id="showNetAmount">{{ setting('currency_symbol', 'global') }}0.00</h3>
                </div>

                <div class="installment-lists">
                    <h4 class="installment-title">{{ __('Grant Details') }}</h4>
                    <div class="installment-grid">
                        <div class="installment-item">
                            <div class="result-label">{{ __('Commission Charge') }}</div>
                            <h5 class="result-value" id="showCommissionAmount">{{ setting('currency_symbol', 'global') }}0.00</h5>
                        </div>

                        <div class="installment-item">
                            <div class="result-label">{{ __('Approval Time') }}</div>
                            <h5 class="result-value" id="showApprovalDays">0 {{ __('days') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Grant calculator area end -->

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
