<?php

namespace App\Http\Requests;

use App\Models\OthersBank;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'bank_id' => 'required_if:beneficiary_id,null',
            'beneficiary_id' => 'nullable',
            'manual_data.account_name' => 'required_if:beneficiary_id,null',
            'manual_data.account_number' => 'required_if:beneficiary_id,null',
            'manual_data.branch_name' => 'nullable',
            'amount' => ['required', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'wallet_type' => 'nullable',
            'purpose' => 'nullable',
        ], $this->getDynamicFieldRules());
    }

    private function getDynamicFieldRules(): array
    {
        return $this->mapDynamicFields(fn ($field) => $field['validation']);
    }

    private function mapDynamicFields(callable $callback): array
    {
        $result = [];

        foreach ($this->getDynamicFields() as $field) {
            $key = 'manual_data.'.strtolower(str_replace(' ', '_', $field['name']));
            $result[$key] = $callback($field);
        }

        return $result;
    }

    private function getDynamicFields(): array
    {
        $bank = OthersBank::find($this->input('bank_id'));
        $fields = json_decode(optional($bank)->field_options, true);

        return is_array($fields) ? $fields : [];
    }

    public function attributes(): array
    {
        return $this->mapDynamicFields(fn ($field) => $field['name']);
    }

    public function messages(): array
    {
        return [
            'manual_data.*.required_if' => __('Select beneficiary or fill up account name, number, branch name.'),
        ];
    }
}
