@foreach ($customFieldsData as $key => $field)
    @php
        $fieldName = strtolower(str_replace(' ', '_', $field['name']));
        $isRequired = $field['validation'] == 'required';
    @endphp

    @if ($field['type'] === 'file')
        <div class="col-xl-4 col-lg-6 col-md-6 custom-fields">
            <div class="inputs">
                <label class="form-label">{{ $field['name'] }}@if ($isRequired)
                        <span class="required">*</span>
                    @endif
                </label>
                <div class="wrap-custom-file">
                    <input type="file" name="manual_data[{{ $fieldName }}]" id="{{ $fieldName }}"
                        accept=".gif, .jpg, .png, .jpeg, .svg" @if ($isRequired) required @endif />
                    <label for="{{ $fieldName }}">
                        <img class="upload-icon" src="{{ asset('global/materials/upload.svg') }}" alt="" />
                        <span>{{ __('Select ') . $field['name'] }}</span>
                    </label>
                </div>
            </div>
        </div>
    @elseif($field['type'] === 'textarea')
        <div class="col-xl-12 col-lg-12 col-md-12 custom-fields">
            <div class="inputs">
                <label class="form-label">{{ $field['name'] }}@if ($isRequired)
                        <span class="required">*</span>
                    @endif
                </label>
                <textarea class="box-textarea" name="manual_data[{{ $fieldName }}]"
                    @if ($isRequired) required @endif></textarea>
            </div>
        </div>
    @else
        <div class="col-xl-4 col-lg-6 col-md-6 custom-fields">
            <div class="inputs">
                <label class="input-label">{{ $field['name'] }}@if ($isRequired)
                        <span class="required">*</span>
                    @endif
                </label>
                <div class="input-group">
                    <input type="text" class="form-control" name="manual_data[{{ $fieldName }}]"
                    @if ($isRequired) required @endif>
                </div>
            </div>
        </div>
    @endif
@endforeach
