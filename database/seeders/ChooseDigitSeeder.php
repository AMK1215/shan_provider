<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChooseDigitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('choose_digits')->truncate();

        // Insert choose close digits from 00 to 99
        for ($i = 0; $i <= 99; $i++) {
            DB::table('choose_digits')->insert([
                'choose_close_digit' => str_pad($i, 2, '0', STR_PAD_LEFT), // Format as 00, 01, 02, ..., 99
                'status' => true, // Default status is true (matching your HeadCloseSeeder)
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Choose close digits (00-99) seeded successfully!');
    }
}
