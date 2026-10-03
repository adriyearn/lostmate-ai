<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoundItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'item_name' => ['required', 'string', 'max:150'],
            'color' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'hidden_details' => ['nullable', 'string'],
            'location_found' => ['required', 'string', 'max:255'],
            'date_found' => ['required', 'date', 'before_or_equal:today'],
            'time_found' => ['nullable', 'date_format:H:i,H:i:s'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
