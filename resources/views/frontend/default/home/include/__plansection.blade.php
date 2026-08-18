@php
$grant_permission = setting('user_grant', 'permission');
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
        </div>

        @if($grant_permission)
        @php
        $grant_plans = App\Models\GrantPlan::activeCached();
        @endphp

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
        @endif
    </div>
</section>
