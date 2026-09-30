<?php

namespace Database\Factories;

use App\Enums\ClaimStatus;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Claim>
 */
class ClaimFactory extends Factory
{
    public function definition(): array
    {
        return [
            'found_item_id' => FoundItem::factory(),
            'claimant_id' => User::factory(),
            'identifying_details' => fake()->paragraph(),
            'status' => ClaimStatus::Pending,
        ];
    }
}
