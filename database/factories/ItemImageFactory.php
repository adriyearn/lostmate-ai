<?php

namespace Database\Factories;

use App\Models\LostItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ItemImage>
 */
class ItemImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'imageable_type' => LostItem::class,
            'imageable_id' => LostItem::factory(),
            'path' => 'item-images/'.fake()->uuid().'.jpg',
            'original_name' => fake()->word().'.jpg',
        ];
    }
}
