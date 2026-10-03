<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Re-typing the password proves it's really the owner, not
            // someone using a phone that was left logged in.
            'delete_password' => ['required', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return ['delete_password.current_password' => 'That password is not correct.'];
    }
}
