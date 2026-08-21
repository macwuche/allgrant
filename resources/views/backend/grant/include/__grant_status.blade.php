@switch($status)
    @case('reviewing')
        <div class="site-badge pending">{{ __('Reviewing') }}</div>
        @break
    @case('approved')
        <div class="site-badge success">{{ __('Approved') }}</div>
        @break
    @case('rejected')
        <div class="site-badge danger">{{ __('Rejected') }}</div>
        @break
    @case('cancelled')
        <div class="site-badge danger">{{ __('Cancelled') }}</div>
        @break
@endswitch
