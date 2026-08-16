<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use function getPageSetting;

class RegisterCustomField implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    private $isEdit;

    public function __construct($isEdit = false)
    {
        $this->isEdit = $isEdit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $regiCustomFields = json_decode(getPageSetting('register_custom_fields'), true) ?? [];

        if ($regiCustomFields) {
            foreach ($regiCustomFields as $key => $field) {
                $fieldName = $field['name'] ?? null;

                // Skip if no field name
                if (! $fieldName) {
                    continue;
                }

                if (($field['validation'] ?? null) === 'required' && ! $this->isEdit && empty($value[$fieldName])) {
                    $fail(__('The :attribute field is required.', ['attribute' => $fieldName]));

                    continue;
                }

                if (in_array($field['type'] ?? '', ['file', 'camera']) && isset($value[$fieldName])) {
                    $file = $value[$fieldName];

                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];
                        if (! in_array($file->getMimeType(), $allowedMimeTypes)) {
                            $fail(__('The :attribute field must be a file of type: jpg, jpeg, png, gif.', ['attribute' => $fieldName]));
                        }
                    } else {
                        $fail(__('The :attribute field must be a valid uploaded file.', ['attribute' => $fieldName]));
                    }
                }
            }
        }
    }
}
