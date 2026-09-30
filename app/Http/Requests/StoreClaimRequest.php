<?php

namespace App\Http\Requests;

use App\Models\Claim;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Claim::class, $this->route('foundItem')]);
    }

    public function rules(): array
    {
        return [
            'identifying_details' => ['required', 'string', 'max:2000'],
            'proof_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'lost_item_id' => [
                'nullable',
                Rule::exists('lost_items', 'id')->where('user_id', $this->user()->id),
            ],
            'ai_match_id' => [
                'nullable',
                Rule::exists('ai_matches', 'id')->where('found_item_id', $this->route('foundItem')->id),
            ],
        ];
    }
}
