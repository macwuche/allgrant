@extends('frontend::layouts.user')
@section('title')
    {{ __('My Grant') }}
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('front/css/daterangepicker.css') }}">
@endpush
@section('content')
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-12">
            <div class="site-card">
                <div class="site-card-header">
                    <div class="title-small">{{ __('My Grant List') }}</div>
                    <div class="card-header-links">
                        <a href="{{ route('user.grant.index') }}" class="card-header-link"><i
                                data-lucide="archive"></i>{{ __('Grant Plan List') }}</a>
                    </div>
                </div>
                <div class="site-card-body p-0 overflow-x-auto">
                    <form>
                        <div class="table-filter">
                            <div class="filter">
                                <div class="single-f-box">
                                    <label for="">{{ __('Grant ID') }}</label>
                                    <input class="search" type="text" name="grant_id" value="{{ request('grant_id') }}"
                                        autocomplete="off" />
                                </div>
                                <div class="single-f-box">
                                    <label for="">{{ __('Date') }}</label>
                                    <input type="text" name="daterange" value="{{ request('daterange') }}"
                                        autocomplete="off" />
                                </div>
                                <button class="apply-btn me-2" name="filter">
                                    <i data-lucide="filter"></i>{{ __('Filter') }}
                                </button>
                                @if (request()->has('filter'))
                                    <button type="button" class="apply-btn bg-danger reset-filter">
                                        <i data-lucide="x"></i>{{ __('Reset Filter') }}
                                    </button>
                                @endif
                            </div>

                            <div class="filter">
                                <div class="single-f-box w-auto ms-4 me-0">
                                    <label for="">{{ __('Entries') }}</label>
                                    <select name="limit" class="nice-select page-count" id="limit-select">
                                        <option value="15" @selected(request('limit', 15) == '15')>15</option>
                                        <option value="30" @selected(request('limit') == '30')>30</option>
                                        <option value="50" @selected(request('limit') == '50')>50</option>
                                        <option value="100" @selected(request('limit') == '100')>100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="site-custom-table">
                        <div class="contents">
                            <div class="site-table-list site-table-head">
                                <div class="site-table-col">{{ __('Grant Name') }}</div>
                                <div class="site-table-col">{{ __('Grant ID') }}</div>
                                <div class="site-table-col">{{ __('Amount') }}</div>
                                <div class="site-table-col">{{ __('Application Charge') }}</div>
                                <div class="site-table-col">{{ __('Commission') }}</div>
                                <div class="site-table-col">{{ __('Net Amount') }}</div>
                                <div class="site-table-col">{{ __('Status') }}</div>
                                <div class="site-table-col">{{ __('Action') }}</div>
                            </div>
                            @foreach ($grants as $grant)
                                <div class="site-table-list">
                                    <div class="site-table-col">
                                        <div class="description">
                                            <div class="content">
                                                <div class="title">{{ $grant->plan->name }}</div>
                                                <div class="date">{{ $grant->created_at->format('d M Y h:i A') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="trx fw-bold">{{ $grant->grant_no }}</div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="trx fw-bold">{{ $grant->amount }} {{ $currency }}</div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="fw-bold">{{ $grant->plan->applicationFee($grant->amount) }}
                                            {{ $currency }}</div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="fw-bold">
                                            {{ $grant->status == App\Enums\GrantStatus::Approved ? $grant->commission_amount.' '.$currency : '-' }}
                                        </div>
                                    </div>
                                    <div class="site-table-col">
                                        <div class="fw-bold">
                                            {{ $grant->status == App\Enums\GrantStatus::Approved ? $grant->net_amount.' '.$currency : '-' }}
                                        </div>
                                    </div>
                                    <div class="site-table-col">
                                        @if ($grant->status->value == 'approved')
                                            <div class="type site-badge badge-success">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @elseif($grant->status->value == 'rejected' || $grant->status->value == 'cancelled')
                                            <div class="type site-badge badge-failed">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @elseif($grant->status->value == 'reviewing')
                                            <div class="type site-badge badge-pending">{{ ucfirst($grant->status->value) }}
                                            </div>
                                        @endif

                                    </div>
                                    <div class="site-table-col">
                                        <div class="action">
                                            <a href="{{ route('user.grant.details', $grant->grant_no) }}"
                                                class="icon-btn me-2"><i data-lucide="eye"></i>{{ __('Details') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            {{ $grants->links() }}
                        </div>
                        @if (count($grants) == 0)
                            <div class="no-data-found">{{ __('No Data Found!') }}</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('front/js/moment.min.js') }}"></script>
    <script src="{{ asset('front/js/daterangepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            "use strict"

            const currency = @json($currency)

            // Initialize datepicker
            $('input[name="daterange"]').daterangepicker({
                opens: 'left'
            });

            @if (request('daterange') == null)
                // Set default is empty for date range
                $('input[name=daterange]').val('');
            @endif

            // Reset filter
            $('.reset-filter').on('click', function() {
                window.location.href = "{{ route('user.grant.history') }}";
            });

            $('#limit-select').on('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('limit', $(this).val());
                window.location.href = url.toString();
            });
        })
    </script>
@endsection
