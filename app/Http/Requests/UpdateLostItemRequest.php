<?php

namespace App\Http\Requests;

use App\Services\ItemImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLostItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lostItem'));
    }

    public function rules(): array
    {
        $existingImageCount = $this->route('lostItem')->images()->count();
        $remainingSlots = max(0, ItemImageService::MAX_IMAGES - $existingImageCount);

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'item_name' => ['required', 'string', 'max:150'],
            'color' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'location_lost' => ['required', 'string', 'max:255'],
            'date_lost' => ['required', 'date', 'before_or_equal:today'],
            'time_lost' => ['nullable', 'date_format:H:i,H:i:s'],
            'images' => ['nullable', 'array', 'max:'.$remainingSlots],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => [Rule::exists('item_images', 'id')],
        ];
    }
}
