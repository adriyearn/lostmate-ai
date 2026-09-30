<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class LostFoundItemSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::where('email', 'juan@lostmate.test')->firstOrFail();
        $finder = User::where('email', 'maria@lostmate.test')->firstOrFail();

        $categoryId = fn (string $name) => Category::where('name', $name)->value('id');

        // The intended AI match pair from the schema's seed data section.
        LostItem::create([
            'user_id' => $reporter->id,
            'category_id' => $categoryId('Wallets'),
            'item_name' => 'Black leather wallet with a red logo',
            'color' => 'Black',
            'brand' => null,
            'description' => 'Lost my black leather wallet, has a small red logo embossed on the front.',
            'location_lost' => 'Library',
            'date_lost' => now()->subDays(2)->toDateString(),
        ]);

        FoundItem::create([
            'user_id' => $finder->id,
            'category_id' => $categoryId('Wallets'),
            'item_name' => 'Black wallet with a red mark',
            'color' => 'Black',
            'brand' => null,
            'description' => 'Found a black wallet with a red mark near the library entrance.',
            'hidden_details' => 'Contains a school ID and a jeepney card',
            'location_found' => 'Library entrance',
            'date_found' => now()->subDays(1)->toDateString(),
            'current_location' => 'With finder',
        ]);

        $otherLostItems = [
            ['name' => 'iPhone 13 with cracked screen protector', 'category' => 'Phones', 'color' => 'Blue', 'location' => 'Canteen'],
            ['name' => 'Silver house keys with a keychain', 'category' => 'Keys', 'color' => 'Silver', 'location' => 'Gym'],
            ['name' => 'Calculus notebook, spiral bound', 'category' => 'Books & Notebooks', 'color' => 'Green', 'location' => 'Room 204'],
            ['name' => 'Gray backpack with laptop inside', 'category' => 'Bags', 'color' => 'Gray', 'location' => 'Parking Lot'],
            ['name' => 'Prescription eyeglasses in a black case', 'category' => 'Accessories', 'color' => 'Black', 'location' => 'Canteen'],
            ['name' => 'School ID card', 'category' => 'IDs', 'color' => null, 'location' => 'Gym'],
            ['name' => 'Wireless earbuds case', 'category' => 'Gadgets', 'color' => 'White', 'location' => 'Library'],
            ['name' => 'Blue hoodie jacket', 'category' => 'Clothing', 'color' => 'Blue', 'location' => 'Room 204'],
            ['name' => 'Umbrella, foldable', 'category' => 'Others', 'color' => 'Black', 'location' => 'Parking Lot'],
        ];

        foreach ($otherLostItems as $item) {
            LostItem::create([
                'user_id' => $reporter->id,
                'category_id' => $categoryId($item['category']),
                'item_name' => $item['name'],
                'color' => $item['color'],
                'description' => "Lost my {$item['name']} somewhere near the {$item['location']}.",
                'location_lost' => $item['location'],
                'date_lost' => now()->subDays(random_int(1, 25))->toDateString(),
            ]);
        }

        $otherFoundItems = [
            ['name' => 'Samsung Galaxy phone, black case', 'category' => 'Phones', 'color' => 'Black', 'location' => 'Canteen', 'hidden' => 'Lock screen wallpaper is a dog photo'],
            ['name' => 'Set of keys with a red ribbon', 'category' => 'Keys', 'color' => 'Red', 'location' => 'Gym entrance', 'hidden' => 'Has a small bottle opener attached'],
            ['name' => 'Chemistry notebook', 'category' => 'Books & Notebooks', 'color' => 'Yellow', 'location' => 'Room 204', 'hidden' => 'Name written inside as "J.D.C."'],
            ['name' => 'Black backpack', 'category' => 'Bags', 'color' => 'Black', 'location' => 'Parking Lot', 'hidden' => 'Contains a water bottle and a charger'],
            ['name' => 'Sunglasses in a brown case', 'category' => 'Accessories', 'color' => 'Brown', 'location' => 'Canteen', 'hidden' => 'Case has a small scratch on the lid'],
            ['name' => 'Student ID card', 'category' => 'IDs', 'color' => null, 'location' => 'Library', 'hidden' => 'ID number ends in 4521'],
            ['name' => 'Bluetooth speaker', 'category' => 'Gadgets', 'color' => 'Red', 'location' => 'Gym', 'hidden' => 'Small dent on the bottom'],
            ['name' => 'Green jacket', 'category' => 'Clothing', 'color' => 'Green', 'location' => 'Room 204', 'hidden' => 'Has a pin badge on the collar'],
            ['name' => 'Water bottle, stainless steel', 'category' => 'Others', 'color' => 'Silver', 'location' => 'Gym', 'hidden' => 'Has stickers on one side'],
        ];

        foreach ($otherFoundItems as $item) {
            FoundItem::create([
                'user_id' => $finder->id,
                'category_id' => $categoryId($item['category']),
                'item_name' => $item['name'],
                'color' => $item['color'],
                'description' => "Found a {$item['name']} near the {$item['location']}.",
                'hidden_details' => $item['hidden'],
                'location_found' => $item['location'],
                'date_found' => now()->subDays(random_int(1, 25))->toDateString(),
                'current_location' => 'With finder',
            ]);
        }
    }
}
