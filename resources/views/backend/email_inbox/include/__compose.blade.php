<div class="modal fade" id="composeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content site-table-modal">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('New Email') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.email-inbox.send') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @include('backend.email_inbox.include.__compose_form', ['addresses' => $addresses])
                    <button type="submit" class="site-btn primary-btn w-100"><i data-lucide="send"></i> {{ __('Send') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
