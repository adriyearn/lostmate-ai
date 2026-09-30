<?php

namespace Database\Factories;

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\LostItem>
 */
class LostItemFactory extends Factory
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
            'location_lost' => fake()->randomElement(['Library', 'Canteen', 'Gym', 'Room 204', 'Parking Lot']),
            'date_lost' => fake()->dateTimeBetween('-30 days', 'now'),
            'time_lost' => fake()->optional()->time(),
            'status' => ItemStatus::Open,
        ];
    }
}
