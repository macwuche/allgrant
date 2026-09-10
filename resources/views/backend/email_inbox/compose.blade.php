@extends('backend.layouts.app')
@section('title')
    {{ __('New Email') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('New Email') }}</h2>
                            <a href="{{ route('admin.email-inbox.index') }}" class="title-btn">
                                <i data-lucide="arrow-left"></i> {{ __('Back to Inbox') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-8 col-lg-12 col-12">
                    <div class="site-card">
                        <div class="site-card-body">
                            <form action="{{ route('admin.email-inbox.send') }}" method="post" enctype="multipart/form-data" class="js-single-submit">
                                @csrf
                                @include('backend.email_inbox.include.__compose_form', ['addresses' => $addresses])
                                <button type="submit" class="site-btn primary-btn w-100"><i data-lucide="send"></i> {{ __('Send') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
