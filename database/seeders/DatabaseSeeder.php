<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@lostmate.test',
            'student_id' => null,
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $studentStaff = [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan@lostmate.test', 'student_id' => '2021-00123'],
            ['name' => 'Maria Santos', 'email' => 'maria@lostmate.test', 'student_id' => '2021-00456'],
            ['name' => 'Pedro Reyes', 'email' => 'pedro@lostmate.test', 'student_id' => '2021-00789'],
            ['name' => 'Ana Garcia', 'email' => 'ana@lostmate.test', 'student_id' => '2022-00234'],
            ['name' => 'Liza Mendoza', 'email' => 'liza@lostmate.test', 'student_id' => '2022-00567'],
            ['name' => 'Carlo Ramos', 'email' => 'carlo@lostmate.test', 'student_id' => null],
        ];

        foreach ($studentStaff as $user) {
            User::forceCreate([
                'name' => $user['name'],
                'email' => $user['email'],
                'student_id' => $user['student_id'],
                'password' => Hash::make('password'),
                'role' => UserRole::StudentStaff,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $this->call([
            CategorySeeder::class,
            LostFoundItemSeeder::class,
        ]);
    }
}
