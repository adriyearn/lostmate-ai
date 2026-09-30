<?php

namespace App\Http\Requests;

use App\Services\ItemImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFoundItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('foundItem'));
    }

    public function rules(): array
    {
        $existingImageCount = $this->route('foundItem')->images()->count();
        $remainingSlots = max(0, ItemImageService::MAX_IMAGES - $existingImageCount);

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'item_name' => ['required', 'string', 'max:150'],
            'color' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'hidden_details' => ['nullable', 'string'],
            'location_found' => ['required', 'string', 'max:255'],
            'date_found' => ['required', 'date', 'before_or_equal:today'],
            'time_found' => ['nullable', 'date_format:H:i'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:'.$remainingSlots],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => [Rule::exists('item_images', 'id')],
        ];
    }
}
