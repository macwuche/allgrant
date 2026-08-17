@extends('backend.layouts.app')
@section('title')
    {{ __('Add New Slide') }}
@endsection

@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-xl-8">
                        <div class="title-content">
                            <h2 class="title">{{ __('Add New Slide') }}</h2>
                            <a href="{{ url()->previous() }}" class="title-btn"><i
                                    data-lucide="corner-down-left"></i>{{ __('Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <div class="site-card">
                        <div class="site-card-body">
                            <form action="{{ route('admin.ad-slider.store') }}" method="post" class="row">
                                @csrf
                                @include('backend.ad_slider.include.__form', ['slide' => null])
                                <div class="col-xl-12">
                                    <button type="submit" class="site-btn-sm primary-btn w-100">
                                        {{ __('Add New Slide') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        (function($) {
            "use strict";
            $('body').on('change', '.ad-slider-template-option input[name="template"]', function() {
                $('.ad-slider-template-option').removeClass('selected');
                $(this).closest('.ad-slider-template-option').addClass('selected');
            });
        })(jQuery);
    </script>
@endsection
