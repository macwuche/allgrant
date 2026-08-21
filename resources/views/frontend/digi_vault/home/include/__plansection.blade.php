<!-- Pricing section start -->
<section class="td-pricing-section include-bg p-relative z-index-11 section_space"
    data-background="{{ asset('front/digi_vault/images/bg/pricing-bg.png') }}">
    <div class="container">
        <div class="row gy-30">
            <div class="col-xxl-12">
                <div class="row align-items-center justify-content-between">
                    <div class="col-xxl-6 col-xl-6 col-lg-6">
                        <div class="section-title-wrapper section_title_space">
                            <span class="section-subtitle has_fade_anim">{{ $data['title_small'] }}</span>
                            <h2 class="section-title has_fade_anim">{{ $data['title_big'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            @php
                $grant_plans = App\Models\GrantPlan::active()->get();
            @endphp

            <div class="col-xxl-12">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-grant" role="tabpanel" aria-labelledby="nav-grant-tab">
                        <div class="row gy-30">
                            @forelse ($grant_plans as $plan)
                                <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6">
                                    <div class="has_fade_anim" data-delay="0.15">
                                        <div class="pricing-item">
                                            <div class="inner">
                                                <span class="info-badge">{{ $plan->badge }}</span>
                                                <div class="plan-heading">
                                                    <strong>{{ $plan->commission_charge }}{{ $plan->commission_charge_type == 'percentage' ? '%' : ' ' . setting('currency_symbol', 'global') }}</strong>
                                                    <sub> {{ __('Commission') }}</sub>
                                                    <h5 class="title">{{ $plan->name }}</h5>
                                                </div>
                                                <div class="plan-lists">
                                                    <ul>
                                                        <li>
                                                            <div class="list-info">
                                                                <img src="{{ asset('front/digi_vault/images/icons/check.svg') }}"
                                                                    alt="check">
                                                                <span>{{ __('Approval Days') }} :
                                                                    {{ $plan->approval_days ?? 0 }}</span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="list-info">
                                                                <img src="{{ asset('front/digi_vault/images/icons/check.svg') }}"
                                                                    alt="check">
                                                                <span>{{ __('Application Charge') }} :
                                                                    {{ $plan->grant_fee }}{{ $plan->grant_fee_type == 'percentage' ? '%' : ' ' . $currencySymbol }}</span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="list-info">
                                                                <img src="{{ asset('front/digi_vault/images/icons/check.svg') }}"
                                                                    alt="check">
                                                                <span>{{ __('Commission Charge') }} :
                                                                    {{ $plan->commission_charge }}{{ $plan->commission_charge_type == 'percentage' ? '%' : ' ' . $currencySymbol }}</span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="list-info">
                                                                <img src="{{ asset('front/digi_vault/images/icons/check.svg') }}"
                                                                    alt="check">
                                                                <span>{{ __('All Deposits') }} :
                                                                    {{ $currencySymbol . $plan->total_deposit }}</span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="plan-content-list">
                                                                <div class="list-info">
                                                                    <img src="{{ asset('front/digi_vault/images/icons/check.svg') }}"
                                                                        alt="check">
                                                                    <span>{{ __('Final Maturity') }} :
                                                                        {{ $currencySymbol . $plan->total_mature_amount }}</span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="btn-inner">
                                                <a class="td-btn gradient-btn radius-8 w-100"
                                                    href="{{ route('user.grant.index') }}">
                                                    <span class="btn-text">{{ __('Subscribe') }}</span>
                                                    <span class="btn-icon">
                                                        <svg width="19" height="19" viewBox="0 0 19 19"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M2.9017 9.6748C2.9017 9.36414 3.15355 9.1123 3.4642 9.1123H15.8392C16.1499 9.1123 16.4017 9.36414 16.4017 9.6748C16.4017 9.98547 16.1499 10.2373 15.8392 10.2373H3.4642C3.15355 10.2373 2.9017 9.98547 2.9017 9.6748Z"
                                                                fill="white" />
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M10.3789 4.21456C10.5986 3.99489 10.9548 3.99489 11.1744 4.21456L16.2369 9.27706C16.4566 9.49672 16.4566 9.85289 16.2369 10.0725L11.1744 15.135C10.9548 15.3547 10.5986 15.3547 10.3789 15.135C10.1593 14.9154 10.1593 14.5592 10.3789 14.3396L15.0437 9.67481L10.3789 5.01005C10.1593 4.79038 10.1593 4.43423 10.3789 4.21456Z"
                                                                fill="white" />
                                                        </svg>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @empty
                                    <div class="text-center">{{ __('No Data Found!') }}</div>
                                @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Pricing section end -->
