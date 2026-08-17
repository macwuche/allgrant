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
                                    data-total-installment="{{ $grant_plan->total_installment }}"
                                    data-per-installment="{{ $grant_plan->per_installment }}"
                                    data-installment-interval="{{ $grant_plan->installment_intervel }}"
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
                    <div class="result-label">{{ __('Interest Amount') }}</div>
                    <h3 class="result-value" id="showInterestAmount">{{ setting('currency_symbol', 'global') }}0.00</h3>
                </div>

                <div class="result-amount">
                    <div class="result-label">{{ __('Total Payable Amount') }}</div>
                    <h3 class="result-value" id="showTotalPayableAmount">{{ setting('currency_symbol', 'global') }}0.00</h3>
                </div>

                <div class="installment-lists">
                    <h4 class="installment-title">{{ __('Installment Details') }}</h4>
                    <div class="installment-grid">
                        <div class="installment-item">
                            <div class="result-label">{{ __('Per Installment') }}</div>
                            <h5 class="result-value" id="showPerInstallMent">{{ setting('currency_symbol', 'global') }}0.00</h5>
                        </div>

                        <div class="installment-item">
                            <div class="result-label">{{ __('Total Installments') }}</div>
                            <h5 class="result-value" id="showTotalInstallments">0 time</h5>
                        </div>

                        <div class="installment-item">
                            <div class="result-label">{{ __('Installment Interval') }}</div>
                            <h5 class="result-value" id="showInstallmentInterval">0 day</h5>
                        </div>

                        <div class="installment-item">
                            <div class="result-label">{{ __('Grant Duration') }}</div>
                            <h5 class="result-value" id="showGrantDuration">0 day</h5>
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
            perInstallment: parseFloat(selectedOption.data('per-installment')),
            totalInstallment: parseInt(selectedOption.data('total-installment')),
            name: selectedOption.data('name'),
            installmentInterval: selectedOption.data('installment-interval')
        };

        $('#grantAmount').prop('disabled', false).val('');

        $('.min-max').text(`Minimum ${selectedPlanData.min} ${currency} and Maximum ${selectedPlanData.max} ${currency}`);
        $('#showTotalInstallments').text(`${selectedPlanData.totalInstallment} times`);
        $('#showInstallmentInterval').text(`${selectedPlanData.installmentInterval} days`);
        $('#showGrantDuration').text(`${selectedPlanData.totalInstallment * selectedPlanData.installmentInterval} days`);

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
        const rate = selectedPlanData.perInstallment;
        const totalInstallments = selectedPlanData.totalInstallment;

        const interestAmount = parseFloat(((grantAmount / 100) * rate).toFixed(2));
        const totalPayable = parseFloat((interestAmount + grantAmount).toFixed(2));
        const perInstallmentFee = parseFloat((totalPayable / totalInstallments).toFixed(2));

        $('#showGrantAmount').text(`${currencySymbol}${grantAmount.toFixed(2)}`);
        $('#showPerInstallMent').text(`${currencySymbol}${perInstallmentFee.toFixed(2)}`);
        $('#showInterestAmount').text(`${currencySymbol}${interestAmount.toFixed(2)}`);
        $('#showTotalPayableAmount').text(`${currencySymbol}${totalPayable.toFixed(2)}`);
    }

    function resetResults() {
        $('#showGrantAmount').text(`${currencySymbol}0.00`);
        $('#showPerInstallMent').text(`${currencySymbol}0.00`);
        $('#showInterestAmount').text(`${currencySymbol}0.00`);
        $('#showTotalPayableAmount').text(`${currencySymbol}0.00`);
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
