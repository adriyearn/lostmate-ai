<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseUnclaimedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            // What happened to the item, kept in the admin log, e.g. "Donated to the school clinic".
            'note' => ['required', 'string', 'max:500'],
        ];
    }
}
