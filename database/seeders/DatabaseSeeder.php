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
        ]);

        User::forceCreate([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@lostmate.test',
            'student_id' => '2021-00123',
            'password' => Hash::make('password'),
            'role' => UserRole::StudentStaff,
            'is_active' => true,
        ]);

        User::forceCreate([
            'name' => 'Maria Santos',
            'email' => 'maria@lostmate.test',
            'student_id' => '2021-00456',
            'password' => Hash::make('password'),
            'role' => UserRole::StudentStaff,
            'is_active' => true,
        ]);
    }
}
