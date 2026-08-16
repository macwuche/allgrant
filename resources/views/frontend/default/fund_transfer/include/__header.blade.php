<div class="col-xl-12 col-lg-12 col-md-12 col-12">
    <div class="site-card">
        <div class="site-card-body transfer-top-btns">
            <a href="{{ route('user.fund_transfer.index') }}"
                class="site-btn-sm {{ isActive('user.fund_transfer.index') }}"><i data-lucide="send"></i>
                {{ __('Transfer') }}</a>
            <a href="{{ route('user.fund_transfer.beneficiary.index') }}"
                class="site-btn-sm {{ isActive('user.fund_transfer.beneficiary.index') }}"><i
                    data-lucide="user-check"></i> {{ __('Beneficiary List') }}</a>
            <a href="{{ route('user.fund_transfer.transfer.wire') }}"
                class="site-btn-sm {{ isActive('user.fund_transfer.transfer.wire') }}"><i data-lucide="wifi"></i>
                {{ __('Wire Transfer') }}</a>
            <a href="{{ route('user.fund_transfer.transfer.log') }}"
                class="site-btn-sm {{ isActive('user.fund_transfer.transfer.log') }}"><i
                    data-lucide="alert-circle"></i> {{ __('Transfer History') }}</a>
        </div>
    </div>
</div>
