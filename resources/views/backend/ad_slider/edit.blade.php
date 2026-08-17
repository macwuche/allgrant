@extends('backend.layouts.app')
@section('title')
    {{ __('Edit Slide') }}
@endsection

@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-xl-8">
                        <div class="title-content">
                            <h2 class="title">{{ __('Edit Slide') }}</h2>
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
                            <form action="{{ route('admin.ad-slider.update', $slide->id) }}" method="post" class="row">
                                @csrf
                                @include('backend.ad_slider.include.__form', ['slide' => $slide])
                                <div class="col-xl-12">
                                    <button type="submit" class="site-btn-sm primary-btn w-100">
                                        {{ __('Update Slide') }}
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
