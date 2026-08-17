@php
    $selectedTemplate = old('template', $slide->template ?? 1);
@endphp

<div class="col-xl-12">
    <div class="site-input-groups">
        <label class="box-input-label" for="">
            {{ __('Template:') }}
            <span class="text-danger">*</span>
        </label>
        <div class="ad-slider-template-grid">
            @foreach ($templates as $id => $tpl)
                <label @class(['ad-slider-template-option', 'selected' => (int) $selectedTemplate === $id])>
                    <input type="radio" name="template" value="{{ $id }}"
                        @checked((int) $selectedTemplate === $id)>
                    <span class="ad-slider-template-swatch"
                        style="background: linear-gradient(135deg, {{ $tpl['gradient'][0] }} 0%, {{ $tpl['gradient'][1] }} 100%);">
                        <i data-lucide="{{ $tpl['icon'] }}" style="color: {{ $tpl['icon_color'] }}"></i>
                    </span>
                    <span class="ad-slider-template-name">{{ $tpl['label'] }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<div class="col-xl-12">
    <div class="site-input-groups">
        <label class="box-input-label" for="">
            {{ __('Headline Text:') }}
            <span class="text-danger">*</span>
        </label>
        <textarea name="title" class="box-input" rows="2" placeholder="{{ __('e.g. Getting a grant is no longer difficult. We offer you the most convenient solution.') }}">{{ old('title', $slide->title ?? '') }}</textarea>
    </div>
</div>

<div class="col-xl-6">
    <div class="site-input-groups">
        <label class="box-input-label" for="">{{ __('Button Text:') }}</label>
        <input type="text" name="button_text" class="box-input" placeholder="{{ __('e.g. Apply Now') }}"
            value="{{ old('button_text', $slide->button_text ?? '') }}" />
    </div>
</div>

<div class="col-xl-6">
    <div class="site-input-groups">
        <label class="box-input-label" for="">{{ __('Button Link:') }}</label>
        <input type="text" name="button_link" class="box-input" placeholder="{{ __('e.g. /user/grant or a full URL') }}"
            value="{{ old('button_link', $slide->button_link ?? '') }}" />
    </div>
</div>

<div class="col-xl-6">
    <div class="site-input-groups">
        <label class="box-input-label" for="">
            {{ __('Duration:') }}
            <span class="text-danger">*</span>
        </label>
        <div class="input-group joint-input">
            <input type="number" name="duration" min="2" max="60" class="form-control"
                value="{{ old('duration', $slide->duration ?? 5) }}" />
            <span class="input-group-text">{{ __('Seconds') }}</span>
        </div>
        <small class="text-muted">{{ __('How long this slide shows before sliding to the next one.') }}</small>
    </div>
</div>

<div class="col-xl-6">
    <div class="site-input-groups">
        <label class="box-input-label" for="">
            {{ __('Position:') }}
        </label>
        <input type="number" name="position" min="0" class="box-input"
            value="{{ old('position', $slide->position ?? 0) }}" />
        <small class="text-muted">{{ __('Lower numbers play first.') }}</small>
    </div>
</div>

<div class="col-xl-6">
    <div class="site-input-groups">
        <label class="box-input-label" for="">{{ __('Status:') }}</label>
        <div class="switch-field same-type">
            <input type="radio" id="radio-ad-slide-status-active" name="status" value="1"
                @checked((int) old('status', $slide->status ?? 1) === 1)>
            <label for="radio-ad-slide-status-active">{{ __('Active') }}</label>
            <input type="radio" id="radio-ad-slide-status-inactive" name="status" value="0"
                @checked((int) old('status', $slide->status ?? 1) === 0)>
            <label for="radio-ad-slide-status-inactive">{{ __('Deactivate') }}</label>
        </div>
    </div>
</div>

<style>
    .ad-slider-template-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        margin-top: 8px;
    }

    @media (max-width: 767px) {
        .ad-slider-template-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .ad-slider-template-option {
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 10px;
        padding: 6px;
        text-align: center;
        transition: border-color 0.15s ease;
    }

    .ad-slider-template-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .ad-slider-template-option:has(input:checked),
    .ad-slider-template-option.selected {
        border-color: #6c3beb;
    }

    .ad-slider-template-swatch {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 52px;
        border-radius: 8px;
        margin-bottom: 6px;
    }

    .ad-slider-template-swatch svg {
        width: 20px;
        height: 20px;
    }

    .ad-slider-template-name {
        display: block;
        font-size: 12px;
    }
</style>
