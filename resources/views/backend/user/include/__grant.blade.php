@if(request('tab') == 'grant')
<div
    @class([
        'tab-pane fade',
        'show active' => request('tab') == 'grant'
    ])
    id="pills-grant"
    role="tabpanel"
    aria-labelledby="pills-grant-tab"
>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h4 class="title">{{ __('Grant') }}</h4>
                </div>
                <div class="site-card-body table-responsive">
                    <div class="site-table">
                        <div class="table-filter">
                            <form action="" method="get">
                                <input type="hidden" name="tab" value="grant">
                                <div class="filter d-flex">
                                    <div class="search">
                                        <label for="">{{ __('Search:') }}</label>
                                        <input type="text" name="query" value="{{ request('query') }}"/>
                                    </div>
                                    <button class="apply-btn" type="submit"><i data-lucide="search"></i>{{ __('Search') }}</button>
                                </div>
                            </form>
                        </div>
                        <table class="table">
                            <thead>
                            <tr>
                                @include('backend.filter.th',['label' => 'Date','field' => 'created_at'])
                                @include('backend.filter.th',['label' => 'Grant','field' => 'grant'])
                                @include('backend.filter.th',['label' => 'Grant ID','field' => 'grant_no'])
                                @include('backend.filter.th',['label' => 'Amount','field' => 'amount'])
                                <th>{{ __('Application Charge') }}</th>
                                <th>{{ __('Commission') }}</th>
                                <th>{{ __('Net Amount') }}</th>
                                @include('backend.filter.th',['label' => 'Status','field' => 'status'])
                            </tr>
                            </thead>
                            <tbody>
                                @forelse ($grants as $grant)
                                <tr>
                                    <td>{{ $grant->created_at->format('d M Y h:i A')  }}</td>
                                    <td>{{ $grant->plan->name }}</td>
                                    <td>{{ $grant->grant_no }}</td>
                                    <td>
                                        {{ $currencySymbol.$grant->amount }}
                                    </td>
                                    <td>{{ $currencySymbol.$grant->plan->applicationFee($grant->amount) }}</td>
                                    <td>
                                        {{ $grant->status == App\Enums\GrantStatus::Approved ? $currencySymbol.$grant->commission_amount : '-' }}
                                    </td>
                                    <td>
                                        {{ $grant->status == App\Enums\GrantStatus::Approved ? $currencySymbol.$grant->net_amount : '-' }}
                                    </td>

                                    <td>
                                        @if($grant->status->value == 'approved')
                                            <div class="type site-badge success">{{ ucfirst($grant->status->value) }}</div>
                                        @elseif($grant->status->value == 'rejected' || $grant->status->value == 'cancelled')
                                            <div class="type site-badge danger">{{ ucfirst($grant->status->value) }}</div>
                                        @elseif($grant->status->value == 'reviewing')
                                            <div class="type site-badge pending">{{ ucfirst($grant->status->value) }}</div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <td colspan="7" class="text-center">{{ __('No Data Found') }}!</td>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $grants->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
