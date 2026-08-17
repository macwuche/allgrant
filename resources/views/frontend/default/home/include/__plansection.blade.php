@php
$dps_permission = setting('user_dps', 'permission');
$fdr_permission = setting('user_fdr', 'permission');
$grant_permission = setting('user_grant', 'permission');

// Determine the first active tab based on permissions
$active_tab = null;
if ($dps_permission) {
$active_tab = 'dps';
} elseif ($fdr_permission) {
$active_tab = 'fdr';
} elseif ($grant_permission) {
$active_tab = 'grant';
}
@endphp

<section class="pricing-area theme-bg-1 section-space">
    <div class="container">
        <div class="row gy-3 align-items-center justify-content-between section-title-space">
            <div class="col-xxl-6 col-xl-6 col-lg-6">
                <div class="section-title-wrapper">
                    <span data-aos="fade-up" data-aos-duration="1000" class="section-subtitle">
                        {{ $data['title_small'] }}
                    </span>
                    <h2 data-aos="fade-up" data-aos-duration="1500" class="section-title">
                        {{ $data['title_big'] }}
                    </h2>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6">
                <div data-aos="fade-up" data-aos-duration="1500" class="pricing-tab customize-tab ">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        @if($dps_permission)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $active_tab == 'dps' ? 'active' : '' }}" id="price-tab-one" data-bs-toggle="tab"
                                data-bs-target="#price-tab-one-pane" type="button" role="tab"
                                aria-controls="price-tab-one-pane" aria-selected="{{ $active_tab == 'dps' ? 'true' : 'false' }}">
                                <span><i class="fa-regular fa-archive"></i></span>{{ __('DPS') }}
                            </button>
                        </li>
                        @endif
                        @if($fdr_permission)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $active_tab == 'fdr' ? 'active' : '' }}" id="price-tab-two" data-bs-toggle="tab"
                                data-bs-target="#price-tab-two-pane" type="button" role="tab"
                                aria-controls="price-tab-two-pane" aria-selected="{{ $active_tab == 'fdr' ? 'true' : 'false' }}">
                                <i class="fa-regular fa-book-blank"></i>{{ __('FDR') }}
                            </button>
                        </li>
                        @endif
                        @if($grant_permission)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $active_tab == 'grant' ? 'active' : '' }}" id="price-tab-three" data-bs-toggle="tab"
                                data-bs-target="#price-tab-three-pane" type="button" role="tab"
                                aria-controls="price-tab-three-pane" aria-selected="{{ $active_tab == 'grant' ? 'true' : 'false' }}">
                                <span><i class="fa-regular fa-triangle-exclamation"></i></span>{{ __('Grant') }}
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @php
        $dps_plans = cache()->remember('dps_plans_active', 300, fn() => App\Models\DpsPlan::active()->get());
        $fdr_plans = cache()->remember('fdr_plans_active', 300, fn() => App\Models\FdrPlan::active()->get());
        $grant_plans = App\Models\GrantPlan::activeCached();
        @endphp

        <div class="tab-content" id="myTabContent">
            @if($dps_permission)
            <div class="tab-pane fade {{ $active_tab == 'dps' ? 'show active' : '' }}" id="price-tab-one-pane" role="tabpanel" aria-labelledby="price-tab-one" tabindex="0">
                <div class="row gy-30">
                    @forelse ($dps_plans as $plan)
                    <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="price-item" data-aos="fade-up" data-aos-duration="6000">
                            @if($plan->featured)
                            <span class="price-badge">{{ $plan->badge }}</span>
                            @endif
                            <div class="price-top">
                                <div class="price-title">
                                    <h4>{{ $plan->name }}</h4>
                                </div>
                                <div class="price-value">
                                    <strong>{{ setting('currency_symbol', 'global') }}{{ $plan->per_installment }}</strong>
                                    <sub>/ {{ $plan->interval }} {{ __('Days') }}</sub>
                                </div>
                            </div>
                            <div class="info-list">
                                <ul>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Interest Rate') }} : {{ $plan->interest_rate }}%</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Number of Installments') }} : {{ $plan->total_installment }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Per Installment') }} : {{ $currencySymbol.$plan->per_installment }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Installment Slice') }} : {{ $plan->interval }} {{ __('Days') }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('All Deposits') }} : {{ $currencySymbol.$plan->total_deposit }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Final Maturity') }} : {{ $currencySymbol.$plan->total_mature_amount }}</li>
                                </ul>
                            </div>
                            <div class="price-btn">
                                <a class="pricing-btn w-100" href="{{ route('user.dps.index') }}">{{ __('Subscribe') }}</a>
                            </div>
                        </div>
                    </div>

                    @empty
                        <div class="text-center">{{ __('No Data Found!') }}</div>
                    @endforelse
                </div>
            </div>
            @endif
            @if($fdr_permission)
            <div class="tab-pane fade {{ $active_tab == 'fdr' ? 'show active' : '' }}" id="price-tab-two-pane" role="tabpanel" aria-labelledby="price-tab-two" tabindex="0">
                <div class="row gy-30">
                    @forelse ($fdr_plans as $plan)
                    <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="price-item" data-aos="fade-up" data-aos-duration="6000">
                            @if($plan->featured)
                            <span class="price-badge">{{ $plan->badge }}</span>
                            @endif
                            <div class="price-top">
                                <div class="price-title">
                                    <h4>{{ $plan->name }}</h4>
                                </div>
                                <div class="price-value">
                                    <strong>{{ $plan->interest_rate }}%</strong>
                                    <sub>/ {{ $plan->intervel }} {{ __('Days') }}</sub>
                                </div>
                            </div>
                            <div class="info-list">
                                <ul>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Lock In Period') }} : {{ $plan->locked }} {{ __('Days') }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Get Profit Every') }} : {{ $plan->intervel }} {{ __('Days') }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Profit Rate') }} : {{ $plan->interest_rate }}%</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Minimum FDR') }} : {{ $currencySymbol.$plan->minimum_amount }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Maximum FDR') }} : {{ $currencySymbol.$plan->maximum_amount }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Compounding') }} : {{ $plan->is_compounding ? "Yes" : 'No' }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Cancel In') }} : {{ $plan->can_cancel ? ($plan->cancel_type == 'anytime' ? 'Anytime' : $plan->cancel_days.' Days') : __('No') }}</li>
                                </ul>
                            </div>
                            <div class="price-btn">
                                <a class="pricing-btn w-100" href="{{ route('user.fdr.index') }}">{{ __('Subscribe') }}</a>
                            </div>
                        </div>
                    </div>

                    @empty
                        <div class="text-center">{{ __('No Data Found!') }}</div>
                    @endforelse
                </div>
            </div>
            @endif
            @if($grant_permission)
            <div class="tab-pane fade {{ $active_tab == 'grant' ? 'show active' : '' }}" id="price-tab-three-pane" role="tabpanel" aria-labelledby="price-tab-three" tabindex="0">
                <div class="row gy-30">
                    @forelse ($grant_plans as $plan)
                    <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="price-item" data-aos="fade-up" data-aos-duration="6000">
                            <div class="price-top">
                                <div class="price-title">
                                    <h4>{{ $plan->name }}</h4>
                                </div>
                                <div class="price-value">
                                    <strong>{{ $plan->per_installment }}%</strong>
                                    <sub>/ {{ $plan->installment_intervel }} {{ __('Days') }}</sub>
                                </div>
                            </div>
                            <div class="info-list">
                                <ul>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Minimum Grant') }} : {{ $currencySymbol.$plan->minimum_amount }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Miximum Grant') }} : {{ $currencySymbol.$plan->maximum_amount }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Installment Rate') }} : {{ $plan->per_installment }}%</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Installment Slice') }} : {{ $plan->installment_intervel }} {{ __('Days') }}</li>
                                    <li><span><i class="fa-regular fa-check"></i></span>{{ __('Total Installment') }} : {{ $plan->total_installment }}</li>
                                </ul>
                            </div>
                            <div class="price-btn">
                                <a class="pricing-btn w-100" href="{{ route('user.grant.index') }}">{{ __('Apply Now') }}</a>
                            </div>
                        </div>
                    </div>

                    @empty
                        <div class="text-center">{{ __('No Data Found!') }}</div>
                    @endforelse
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
