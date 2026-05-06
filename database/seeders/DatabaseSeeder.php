<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_verified' => true,
        ]);

        User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'password',
            'role' => User::ROLE_USER,
            'is_verified' => true,
        ]);
    }
}
