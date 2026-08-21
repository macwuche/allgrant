@extends('backend.layouts.app')

@section('title')
    {{ __('Subscribe Grant') }}
@endsection

@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="title-content">
                            <h2 class="title">{{ __('Subscribe Grant') }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <form action="{{ route('admin.grant.subscribe') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="site-card">
                            <div class="site-card-body">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="site-input-groups">
                                            <label class="box-input-label">
                                                {{ __('Select Grant Plan') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="grant_plan_id" class="form-select"
                                                onchange="getGrantPlanInfo(this)">
                                                <option selected disabled>
                                                    {{ __('Select Grant Plan') }}
                                                </option>
                                                @foreach ($grantPlans as $grantPlan)
                                                    <option value="{{ $grantPlan->id }}"
                                                        @if (request('grant_plan_id') == $grantPlan->id) selected @endif>
                                                        {{ $grantPlan->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="col-xl-12">
                                            <div class="site-input-groups">
                                                <label class="box-input-label" for="">
                                                    {{ __('Amount:') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group joint-input">
                                                    <input type="number" name="grant_amount" id="grantAmount" step="0.01"
                                                        class="form-control">
                                                    <span class="input-group-text">
                                                        {{ setting('site_currency', 'global') }}
                                                    </span>
                                                </div>
                                                <div class="text-danger min-max"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (isset($selectGrantPlan) && $selectGrantPlan)
                        <div class="col-xl-6">
                            <div class="site-card">
                                <div class="site-card-header">
                                    <h3 class="title">{{ __('Application Form') }}</h3>
                                </div>
                                <div class="site-card-body">
                                    <div class="row">
                                        @foreach (json_decode($selectGrantPlan->field_options, true) as $key => $value)
                                            @if (data_get($value, 'type') == 'text')
                                                <div class="site-input-groups">
                                                    <label class="box-input-label">
                                                        {{ data_get($value, 'name') }}
                                                        {!! data_get($value, 'validation') == 'required' ? '<span class="required">*</span>' : '' !!}
                                                    </label>
                                                    <input type="text" class="box-input"
                                                        name="submitted_data[{{ data_get($value, 'name') }}]"
                                                        @required(data_get($value, 'validation') == 'required')>
                                                </div>
                                            @elseif(data_get($value, 'type') == 'select')
                                                <div class="site-input-groups">
                                                    <label class="box-input-label">
                                                        {{ data_get($value, 'name') }}
                                                        {!! data_get($value, 'validation') == 'required' ? '<span class="required">*</span>' : '' !!}
                                                    </label>
                                                    <select name="submitted_data[{{ data_get($value, 'name') }}]"
                                                        class="form-select">
                                                        <option value="" disabled selected>
                                                            {{ __('Select ' . data_get($value, 'name')) }}
                                                        </option>
                                                        @foreach (data_get($value, 'values', []) as $option)
                                                            <option value="{{ $option }}">
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @elseif (data_get($value, 'type') == 'file')
                                                <div class="site-input-groups">
                                                    <label class="box-input-label">
                                                        {{ data_get($value, 'name') }}
                                                        {!! data_get($value, 'validation') == 'required' ? '<span class="required">*</span>' : '' !!}
                                                    </label>
                                                    <div class="wrap-custom-file">
                                                        <input type="file"
                                                            name="submitted_data[{{ data_get($value, 'name') }}]"
                                                            id="{{ data_get($value, 'name') }}" />
                                                        <label for="{{ data_get($value, 'name') }}">
                                                            <img class="upload-icon"
                                                                src="{{ asset('front/images/icons/upload.svg') }}"
                                                                alt="" />
                                                            <span>
                                                                {{ __('Upload') }} {{ data_get($value, 'name') }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @elseif (data_get($value, 'type') == 'textarea')
                                                <div class="site-input-groups">
                                                    <label class="box-input-label">
                                                        {{ data_get($value, 'name') }}
                                                        {!! data_get($value, 'validation') == 'required' ? '<span class="required">*</span>' : '' !!}
                                                    </label>
                                                    <textarea name="submitted_data[{{ data_get($value, 'name') }}]" cols="10" rows="5" class="form-textarea"
                                                        @required(data_get($value, 'validation') == 'required')></textarea>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="site-card">
                                <div class="site-card-header">
                                    <h3 class="title">{{ __('Grant Plan Information') }}</h3>
                                </div>
                                <div class="site-card-body">
                                    <div class="profile-text-data">
                                        <div class="attribute">{{ __('Plan Name:') }}</div>
                                        <div class="value">
                                            {{ $selectGrantPlan->name }}
                                        </div>
                                    </div>
                                    <div class="profile-text-data">
                                        <div class="attribute">{{ __('Grant Amount:') }}</div>
                                        <div class="value" id="showGrantAmount"></div>
                                    </div>
                                    <div class="profile-text-data">
                                        <div class="attribute">{{ __('Application Charge:') }}</div>
                                        <div class="value" id="showApplicationFee"></div>
                                    </div>
                                    <div class="profile-text-data">
                                        <div class="attribute">{{ __('Commission Charge:') }}</div>
                                        <div class="value" id="showCommissionAmount"></div>
                                    </div>
                                    <div class="profile-text-data">
                                        <div class="attribute">{{ __('Net Amount Disbursed:') }}</div>
                                        <div class="value">
                                            <div class="site-badge success" id="showNetAmount"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <button type="submit" class="site-btn-sm primary-btn w-100">
                                {{ __('Subscribe Grant') }}
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const userId = "{{ request('user_id') }}";

        function getGrantPlanInfo(select) {

            const grantPlanId = select.value;

            if (grantPlanId) {
                const newUrl =
                    `${window.location.origin}${window.location.pathname}?grant_plan_id=${grantPlanId}&user_id=${userId}`;
                window.location.href = newUrl;
            }
        }
    </script>
    @if (isset($selectGrantPlan) && $selectGrantPlan)
        <script>
            const currency = @json($currency);

            const min = "{{ $selectGrantPlan->minimum_amount }}";
            const max = "{{ $selectGrantPlan->maximum_amount }}";

            var message = `Minimum ${min} ${currency} and Maximum ${max} ${currency}`;

            $('.min-max').text(message);

            $('#grantAmount').on('change', function(event) {

                var grantAmount = parseFloat($("#grantAmount").val());
                var applicationChargeRate = parseFloat("{{ $selectGrantPlan->grant_fee }}");
                var applicationChargeType = "{{ $selectGrantPlan->grant_fee_type }}";
                var commissionRate = parseFloat("{{ $selectGrantPlan->commission_charge }}");
                var commissionType = "{{ $selectGrantPlan->commission_charge_type }}";

                if (grantAmount < min) {
                    grantAmount = min;
                    $("#grantAmount").val(grantAmount);
                } else if (grantAmount > max) {
                    grantAmount = max;
                    $("#grantAmount").val(grantAmount);
                }

                var applicationFee = applicationChargeType === 'percentage' ?
                    Math.round((applicationChargeRate / 100) * grantAmount * 100) / 100 :
                    applicationChargeRate;
                var commissionAmount = commissionType === 'percentage' ?
                    Math.round((commissionRate / 100) * grantAmount * 100) / 100 :
                    commissionRate;
                var netAmount = Math.round((grantAmount - commissionAmount) * 100) / 100;

                $("#showGrantAmount").text(`${Math.floor(grantAmount * 10) / 10} ${currency}`);
                $("#showApplicationFee").text(`${applicationFee} ${currency}`);
                $("#showCommissionAmount").text(`${commissionAmount} ${currency}`);
                $("#showNetAmount").text(`${netAmount} ${currency}`);
            });
        </script>
    @endif
@endsection
