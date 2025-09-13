<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadCloseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('head_closes')->truncate();

        // Insert head close digits from 0 to 9
        for ($i = 0; $i <= 9; $i++) {
            DB::table('head_closes')->insert([
                'head_close_digit' => $i,
                'status' => true, // Default status is false
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Head close digits (0-9) seeded successfully!');
    }
}
