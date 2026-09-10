{{-- Shared compose form fields, used by both the compose modal (index page) and
     the standalone compose page. --}}
<div class="site-input-groups">
    <label class="box-input-label">{{ __('From') }}</label>
    <select name="email_address_id" class="box-input" required>
        @foreach($addresses as $address)
            <option value="{{ $address->id }}" @selected($address->is_default)>
                {{ $address->label ? $address->label.' <'.$address->email.'>' : $address->email }}
            </option>
        @endforeach
    </select>
</div>
<div class="site-input-groups">
    <label class="box-input-label">{{ __('To') }}</label>
    <input type="text" class="box-input" name="to" placeholder="{{ __('name@example.com, another@example.com') }}" required/>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="site-input-groups">
            <label class="box-input-label">{{ __('Cc') }}</label>
            <input type="text" class="box-input" name="cc"/>
        </div>
    </div>
    <div class="col-md-6">
        <div class="site-input-groups">
            <label class="box-input-label">{{ __('Bcc') }}</label>
            <input type="text" class="box-input" name="bcc"/>
        </div>
    </div>
</div>
<div class="site-input-groups">
    <label class="box-input-label">{{ __('Subject') }}</label>
    <input type="text" class="box-input" name="subject" required/>
</div>
<div class="site-input-groups">
    <label class="box-input-label">{{ __('Message') }}</label>
    <textarea name="body" class="summernote" required></textarea>
</div>
<div class="site-input-groups">
    <label class="box-input-label">{{ __('Attachments') }}</label>
    <input type="file" class="box-input" name="attachments[]" multiple/>
</div>
