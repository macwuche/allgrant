@extends('frontend::layouts.user')
@section('title')
    {{ __('Dashboard') }}
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('front/css/grant-home.css') }}?v=12" />
@endpush
@section('content')

    <div class="grant-home">

        <div class="gh-hero-row">
            <div class="gh-banner-slider" id="ghBannerSlider">
                @if ($adSlides->isEmpty())
                    <div class="gh-banner gh-banner-slide is-active" data-duration="5000"
                        style="background: linear-gradient(135deg, #dfe3fb 0%, #c7cff6 100%);">
                        <div class="gh-banner-text">
                            {{ __('Getting a grant is no longer difficult. We offer you the most convenient solution.') }}
                        </div>
                        <div class="gh-banner-icon" style="background: rgba(255, 255, 255, 0.55); color: #4a3aeb">
                            <i data-lucide="hand-coins"></i>
                        </div>
                    </div>
                @else
                    @foreach ($adSlides as $index => $slide)
                        @php($tpl = $slide->templateData())
                        <div class="gh-banner gh-banner-slide @if ($index === 0) is-active @endif"
                            data-duration="{{ $slide->duration * 1000 }}"
                            style="background: linear-gradient(135deg, {{ $tpl['gradient'][0] }} 0%, {{ $tpl['gradient'][1] }} 100%);">
                            <div class="gh-banner-text" @if ($tpl['dark']) style="color:#ffffff" @endif>
                                {{ $slide->title }}
                            </div>
                            @if ($slide->button_text)
                                <a href="{{ $slide->button_link ?: '#' }}"
                                    class="gh-banner-cta @if ($tpl['dark']) is-dark @endif">{{ $slide->button_text }}</a>
                            @endif
                            <div class="gh-banner-icon"
                                style="background: {{ $tpl['dark'] ? 'rgba(255, 255, 255, 0.15)' : 'rgba(255, 255, 255, 0.55)' }}; color: {{ $tpl['icon_color'] }}">
                                <i data-lucide="{{ $tpl['icon'] }}"></i>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if ($adSlides->count() > 1)
                    <div class="gh-banner-dots" id="ghBannerDots">
                        @for ($i = 0; $i < $adSlides->count(); $i++)
                            <span class="@if ($i === 0) is-active @endif" data-index="{{ $i }}"></span>
                        @endfor
                    </div>
                @endif
            </div>

            <div class="gh-amount-card">
                <div>
                    <div class="gh-amount-label">{{ __('Current maximum amount') }}</div>
                    <div class="gh-amount-value">
                        {{ $currencySymbol . number_format($user->balance, 2) }}</div>
                    <div class="gh-total-grant">{{ __('Total Grant') }}:
                        {{ $currencySymbol . number_format($total_grant_amount, 2) }}</div>
                    <div class="gh-chip-row">
                        <div class="gh-chip">
                            <i data-lucide="calendar"></i>
                            <span>
                                {{ $heroGrantPlan->approval_days ?? 0 }} {{ __('days') }}
                                <span class="gh-chip-sub">{{ __('Approval') }}</span>
                            </span>
                        </div>
                        <div class="gh-chip">
                            <i data-lucide="percent"></i>
                            <span>
                                {{ $heroGrantPlan->commission_charge ?? 0 }}%
                                <span class="gh-chip-sub">{{ __('Commission') }}</span>
                            </span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('user.grant.index') }}" class="gh-btn-black">
                    {{ __('Apply Now') }}
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="gh-section-head">
            <div class="gh-section-title">{{ __('Quick Action') }}</div>
        </div>

        <div class="gh-quick-grid">
            <div class="gh-quick-card">
                <div class="gh-quick-watermark" style="background: rgba(108, 59, 235, 0.12); color: #6c3beb">
                    <i data-lucide="box"></i>
                </div>
                <div class="gh-quick-top">
                    <div class="gh-quick-identity">
                        <div class="gh-quick-logo" style="background: #6c3beb">
                            <i data-lucide="box"></i>
                        </div>
                        <div class="gh-quick-name">{{ __('Withdrawal') }}</div>
                    </div>
                    <a href="{{ route('user.withdraw.view') }}" class="gh-btn-black-sm">{{ __('Withdraw') }}</a>
                </div>
                <div class="gh-quick-amount">{{ $currencySymbol . number_format($total_withdraw, 2) }}</div>
                <div class="gh-quick-amount-label">{{ __('Total Withdrawn') }}</div>
            </div>

            <div class="gh-quick-card">
                <div class="gh-quick-watermark" style="background: rgba(21, 114, 64, 0.12); color: #157240">
                    <i data-lucide="chevrons-down"></i>
                </div>
                <div class="gh-quick-top">
                    <div class="gh-quick-identity">
                        <div class="gh-quick-logo" style="background: #157240">
                            <i data-lucide="chevrons-down"></i>
                        </div>
                        <div class="gh-quick-name">{{ __('Deposit') }}</div>
                    </div>
                    <a href="{{ route('user.deposit.amount') }}" class="gh-btn-black-sm">{{ __('Deposit') }}</a>
                </div>
                <div class="gh-quick-amount">{{ $currencySymbol . number_format($total_deposit, 2) }}</div>
                <div class="gh-quick-amount-label">{{ __('Successful Deposit') }}</div>
            </div>

            <div class="gh-quick-card">
                <div class="gh-quick-watermark" style="background: rgba(11, 110, 224, 0.12); color: #0b6ee0">
                    <i data-lucide="send"></i>
                </div>
                <div class="gh-quick-top">
                    <div class="gh-quick-identity">
                        <div class="gh-quick-logo" style="background: #0b6ee0">
                            <i data-lucide="send"></i>
                        </div>
                        <div class="gh-quick-name">{{ __('Total Transfer') }}</div>
                    </div>
                    <a href="{{ route('user.fund_transfer.index') }}" class="gh-btn-black-sm">{{ __('Transfer') }}</a>
                </div>
                <div class="gh-quick-amount">{{ $currencySymbol . number_format($total_transfer, 2) }}</div>
                <div class="gh-quick-amount-label">{{ __('Total Transferred') }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                <div class="site-card">
                    <div class="site-card-header">
                        <div class="title-small">{{ __('Recent Transactions') }}</div>
                        <div class="card-header-links">
                            <a href="{{ route('user.transactions') }}" class="card-header-link"><i
                                    data-lucide="eye"></i>{{ __('See All') }}</a>
                        </div>
                    </div>
                    <div class="site-card-body p-0 overflow-x-auto">
                        <div class="site-custom-table">
                            <div class="contents">
                                <div class="site-table-list site-table-head">
                                    <div class="site-table-col">{{ __('Description') }}</div>
                                    <div class="site-table-col">{{ __('Transactions ID') }}</div>
                                    <div class="site-table-col">{{ __('Type') }}</div>
                                    <div class="site-table-col">{{ __('Amount') }}</div>
                                    <div class="site-table-col">{{ __('Charge') }}</div>
                                    <div class="site-table-col">{{ __('Status') }}</div>
                                    <div class="site-table-col">{{ __('Method') }}</div>
                                </div>
                                @foreach ($recentTransactions as $transaction)
                                    <div class="site-table-list">
                                        <div class="site-table-col">
                                            <div class="description">
                                                <div class="event-icon">
                                                    @if ($transaction->type->value == 'deposit' || $transaction->type->value == 'manual_deposit')
                                                        <i data-lucide="chevrons-down"></i>
                                                    @elseif(Str::startsWith($transaction->type->value, 'grant'))
                                                        <i data-lucide="hand-coins"></i>
                                                    @elseif($transaction->type->value == 'subtract')
                                                        <i data-lucide="minus-circle"></i>
                                                    @elseif($transaction->type->value == 'receive_money')
                                                        <i data-lucide="arrow-down-left"></i>
                                                    @else
                                                        <i data-lucide="send"></i>
                                                    @endif
                                                </div>
                                                <div class="content">
                                                    <div class="title">
                                                        {{ $transaction->description }}
                                                        @if (!in_array($transaction->approval_cause, ['none', '']))
                                                            <span class="msg" data-bs-toggle="tooltip"
                                                                data-bs-custom-class="custom-tooltip" data-bs-placement="top"
                                                                data-bs-title="{{ $transaction->approval_cause }}"><i
                                                                    data-lucide="message-square"></i>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="date">{{ $transaction->created_at }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="site-table-col">
                                            <div class="trx fw-bold">{{ $transaction->tnx }}</div>
                                        </div>
                                        <div class="site-table-col">
                                            <div class="type site-badge badge-primary">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->type->value)) }}</div>
                                        </div>
                                        <div class="site-table-col">
                                            <div @class([
                                                'fw-bold',
                                                'green-color' => isPlusTransaction($transaction->type) == true,
                                                'red-color' => isPlusTransaction($transaction->type) == false,
                                            ])>
                                                {{ isPlusTransaction($transaction->type) == true ? '+' : '-' }}{{ $transaction->amount . ' ' . transaction_currency($transaction) }}
                                            </div>
                                        </div>
                                        <div class="site-table-col">
                                            <div class="fw-bold red-color">
                                                -{{ $transaction->charge . ' ' . transaction_currency($transaction) }}</div>
                                        </div>
                                        <div class="site-table-col">
                                            @if ($transaction->status->value == 'failed')
                                                <div class="type site-badge badge-failed">{{ $transaction->status->value }}
                                                </div>
                                            @elseif($transaction->status->value == 'success')
                                                <div class="type site-badge badge-success">{{ $transaction->status->value }}
                                                </div>
                                            @elseif($transaction->status->value == 'pending')
                                                <div class="type site-badge badge-pending">{{ $transaction->status->value }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="site-table-col">
                                            <div class="fw-bold">
                                                {{ $transaction->method !== '' ? ucfirst(str_replace('-', ' ', $transaction->method)) : __('System') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if (count($recentTransactions) == 0)
                                <div class="no-data-found">{{ __('No Data Found') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        (function() {
            var slider = document.getElementById('ghBannerSlider');
            if (!slider) return;

            var slides = slider.querySelectorAll('.gh-banner-slide');
            var dots = document.querySelectorAll('#ghBannerDots span');
            if (slides.length < 2) return;

            var current = 0;
            var timer = null;

            function goTo(index) {
                slides[current].classList.remove('is-active');
                if (dots[current]) dots[current].classList.remove('is-active');
                current = index;
                slides[current].classList.add('is-active');
                if (dots[current]) dots[current].classList.add('is-active');
            }

            function scheduleNext() {
                clearTimeout(timer);
                var duration = parseInt(slides[current].dataset.duration, 10) || 5000;
                timer = setTimeout(function() {
                    goTo((current + 1) % slides.length);
                    scheduleNext();
                }, duration);
            }

            dots.forEach(function(dot, index) {
                dot.addEventListener('click', function() {
                    goTo(index);
                    scheduleNext();
                });
            });

            scheduleNext();
        })();
    </script>
@endsection
