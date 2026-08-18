<section class="pricing-section section-space include-bg"
    data-background="{{ asset('front/theme-2') }}/images/bg/price-bg.png">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-6 col-xl-6 col-lg-6">
                <div class="section-title-wrapper text-center section-title-space">
                    <h2 class="section-title white-text mb-15">
                        {{ $data['title_big'] }}
                    </h2>
                    <P class="description white-text">
                        {{ $data['title_small'] }}
                    </P>
                </div>
            </div>
        </div>
        <div class="price-main-wrapper">
            @php
                $grant_plans = App\Models\GrantPlan::active()->get();
            @endphp
            <div class="row">
                @forelse ($grant_plans as $plan)
                    <div class="col-xxl-4 col-xl-4">
                        <div class="price-item">
                            <div class="price-heading">
                                <h3 class="title">{{ $plan->name }}</h3>
                            </div>
                            <div class="price-value">
                                <h4 class="title">{{ $plan->per_installment }}</h4>
                                <sup class="dollar">%</sup>
                                <sub>/{{ $plan->installment_intervel }} {{ __('Days') }}</sub>
                            </div>
                            <div class="price-list">
                                <ul>
                                    <li>
                                        <div class="list-item">
                                            <span class="icon">
                                                <img src="{{ asset('front/theme-2') }}/images/icons/check-black.svg"
                                                    alt="check">
                                            </span>
                                            <span class="title">{{ __('Minimum Grant') }} :
                                                {{ $currencySymbol . $plan->minimum_amount }}</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="list-item">
                                            <span class="icon">
                                                <img src="{{ asset('front/theme-2') }}/images/icons/check-black.svg"
                                                    alt="check">
                                            </span>
                                            <span class="title">{{ __('Miximum Grant') }} :
                                                {{ $currencySymbol . $plan->maximum_amount }}</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="list-item">
                                            <span class="icon">
                                                <img src="{{ asset('front/theme-2') }}/images/icons/check-black.svg"
                                                    alt="check">
                                            </span>
                                            <span class="title">{{ __('Installment Rate') }} :
                                                {{ $plan->per_installment }}%</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="list-item">
                                            <span class="icon">
                                                <img src="{{ asset('front/theme-2') }}/images/icons/check-black.svg"
                                                    alt="check">
                                            </span>
                                            <span class="title">{{ __('Installment Slice') }} :
                                                {{ $plan->installment_intervel }} {{ __('Days') }}</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="list-item">
                                            <span class="icon">
                                                <img src="{{ asset('front/theme-2') }}/images/icons/check-black.svg"
                                                    alt="check">
                                            </span>
                                            <span class="title">{{ __('Total Installment') }} :
                                                {{ $plan->total_installment }}</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="price-link">
                                <a href="{{ route('user.grant.index') }}"
                                    class="site-btn gdt-btn w-100">{{ __('Subscribe') }}</a>
                            </div>
                        </div>
                    </div>

                    @empty
                        <div class="text-center">{{ __('No Data Found!') }}</div>
                    @endforelse
            </div>
        </div>
    </div>
</section>
