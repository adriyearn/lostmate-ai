<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ConfirmReturnedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('claim'));
    }

    public function rules(): array
    {
        return [
            'pickup_code' => ['required', 'digits:6'],
        ];
    }

    /**
     * After the basic format check, compare the typed code with the claim's
     * real pickup code. hash_equals() compares safely without leaking timing.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! hash_equals($this->route('claim')->pickupCode(), (string) $this->input('pickup_code'))) {
                    $validator->errors()->add('pickup_code', 'That pickup code is not correct. Ask the claimant to check "My Claims".');
                }
            },
        ];
    }
}
