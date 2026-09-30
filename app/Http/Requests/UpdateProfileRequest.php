<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department' => ['nullable', 'string', 'max:150'],
            'course_or_position' => ['nullable', 'string', 'max:150'],
            'year_level' => ['nullable', 'string', 'max:20'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
