<?php

namespace Database\Factories;

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\FoundItem>
 */
class FoundItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'item_name' => fake()->words(3, true),
            'color' => fake()->safeColorName(),
            'brand' => fake()->optional()->company(),
            'description' => fake()->paragraph(),
            'hidden_details' => fake()->sentence(),
            'location_found' => fake()->randomElement(['Library', 'Canteen', 'Gym', 'Room 204', 'Parking Lot']),
            'date_found' => fake()->dateTimeBetween('-30 days', 'now'),
            'time_found' => fake()->optional()->time(),
            'current_location' => fake()->randomElement(['With finder', 'Guidance Office', 'Security Office']),
            'status' => ItemStatus::Open,
        ];
    }
}
