<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('claim'));
    }

    public function rules(): array
    {
        return [
            'response' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
