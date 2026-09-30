<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Wallets',
            'IDs',
            'Phones',
            'Gadgets',
            'Books & Notebooks',
            'Bags',
            'Keys',
            'Accessories',
            'Clothing',
            'Others',
        ];

        foreach ($names as $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'is_active' => true,
            ]);
        }
    }
}
