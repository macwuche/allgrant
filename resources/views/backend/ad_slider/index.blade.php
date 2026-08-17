@extends('backend.layouts.app')

@section('title')
    {{ __('Ads Slider') }}
@endsection

@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Ads Slider') }}</h2>
                            @can('ad-slider-create')
                                <a href="{{ route('admin.ad-slider.create') }}" class="title-btn">
                                    <i data-lucide="plus-circle"></i>
                                    {{ __('Add New Slide') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="site-card">
                        <div class="site-card-body">
                            <div class="site-table table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('Preview') }}</th>
                                            <th scope="col">{{ __('Title') }}</th>
                                            <th scope="col">{{ __('Duration') }}</th>
                                            <th scope="col">{{ __('Position') }}</th>
                                            <th scope="col">{{ __('Status') }}</th>
                                            <th scope="col">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($slides as $slide)
                                            @php($tpl = $templates[$slide->template] ?? $templates[1])
                                            <tr>
                                                <td>
                                                    <div style="width:64px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg, {{ $tpl['gradient'][0] }} 0%, {{ $tpl['gradient'][1] }} 100%);">
                                                        <i data-lucide="{{ $tpl['icon'] }}" style="width:18px;height:18px;color:{{ $tpl['icon_color'] }}"></i>
                                                    </div>
                                                </td>
                                                <td><strong>{{ \Illuminate\Support\Str::limit($slide->title, 60) }}</strong></td>
                                                <td><strong>{{ $slide->duration }}{{ __('s') }}</strong></td>
                                                <td><strong>{{ $slide->position }}</strong></td>
                                                <td>
                                                    <div @class([
                                                        'site-badge',
                                                        'success' => $slide->status,
                                                        'danger' => !$slide->status,
                                                    ])>
                                                        {{ $slide->status ? 'Active' : 'Deactivated' }}</div>
                                                </td>
                                                <td>
                                                    @can('ad-slider-edit')
                                                        <a href="{{ route('admin.ad-slider.edit', $slide->id) }}"
                                                            class="round-icon-btn primary-btn">
                                                            <i data-lucide="edit-3"></i>
                                                        </a>
                                                    @endcan
                                                    @can('ad-slider-delete')
                                                        <span type="button" id="deleteModal" data-id="{{ $slide->id }}"
                                                            data-name="{{ \Illuminate\Support\Str::limit($slide->title, 30) }}">
                                                            <button class="round-icon-btn red-btn" data-bs-toggle="tooltip"
                                                                title="Delete Slide" data-bs-original-title="Delete Slide">
                                                                <i data-lucide="trash-2"></i>
                                                            </button>
                                                        </span>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    {{ __('No slides created yet. The home page will show the default banner until you add one.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @can('ad-slider-delete')
                            <!-- Modal for Delete Slide -->
                            <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="deleteModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-md modal-dialog-centered">
                                    <div class="modal-content site-table-modal">
                                        <div class="modal-body popup-body">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                            <div class="popup-body-text centered">
                                                <div class="info-icon">
                                                    <i data-lucide="alert-triangle"></i>
                                                </div>
                                                <div class="title">
                                                    <h4>{{ __('Are you sure?') }}</h4>
                                                </div>
                                                <p>
                                                    {{ __('You want to delete') }} <strong id="data-name"></strong>
                                                    {{ __('slide?') }}
                                                </p>
                                                <div class="action-btns">
                                                    <form id="deleteForm" method="post">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit" class="site-btn-sm primary-btn me-2">
                                                            <i data-lucide="check"></i>
                                                            Confirm
                                                        </button>
                                                        <a href="" class="site-btn-sm red-btn" type="button"
                                                            data-bs-dismiss="modal" aria-label="Close"><i
                                                                data-lucide="x"></i>{{ __('Cancel') }}</a>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal for Delete Slide End-->
                        @endcan
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

            $('body').on('click', '#deleteModal', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');

                $('#data-name').html(name);
                var url = '{{ route('admin.ad-slider.destroy', ':id') }}';
                url = url.replace(':id', id);
                $('#deleteForm').attr('action', url);
                $('#delete').modal('toggle')

            })

        })(jQuery);
    </script>
@endsection
