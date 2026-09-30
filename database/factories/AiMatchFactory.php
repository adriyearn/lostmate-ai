<?php

namespace Database\Factories;

use App\Enums\AiMatchStatus;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AiMatch>
 */
class AiMatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lost_item_id' => LostItem::factory(),
            'found_item_id' => FoundItem::factory(),
            'score' => fake()->numberBetween(50, 100),
            'reason' => fake()->sentence(),
            'status' => AiMatchStatus::Suggested,
            'model_used' => 'fake',
        ];
    }
}
