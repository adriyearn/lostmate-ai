<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AdminLog>
 */
class AdminLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->admin(),
            'action' => fake()->randomElement(['user.deactivated', 'user.activated', 'claim.overridden', 'category.created']),
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
