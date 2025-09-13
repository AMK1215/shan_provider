<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user PLAYER0101
        User::updateOrCreate(
            ['user_name' => 'PLAYER0101'],
            [
                'name' => 'Test Player 1',
                'user_name' => 'PLAYER0101',
                'password' => bcrypt('password123'),
                'max_score' => 1000.00, // Initial balance
                'status' => 1,
                'is_changed_password' => 1,
                'type' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create test user PLAYER0102
        User::updateOrCreate(
            ['user_name' => 'PLAYER0102'],
            [
                'name' => 'Test Player 2',
                'user_name' => 'PLAYER0102',
                'password' => bcrypt('password123'),
                'max_score' => 1500.00, // Initial balance
                'status' => 1,
                'is_changed_password' => 1,
                'type' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('Test users PLAYER0101 and PLAYER0102 created successfully.');
    }
}
