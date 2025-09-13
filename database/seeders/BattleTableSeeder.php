<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Optional, for more robust time handling if needed

class BattleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Clear existing data (optional, but good for idempotent seeding)
        DB::table('battles')->truncate(); // Or delete() if you don't want to reset IDs

        DB::table('battles')->insert([
            [
                'battle_name' => 'Morning Battle',
                'start_time' => '00:00:00', // 12 AM
                'end_time' => '12:00:00',   // 12 PM
                'status' => true, // or 1
                'open_date' => null, // Or Carbon::now()->toDateString() if you want a specific date
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'battle_name' => 'Evening Battle',
                'start_time' => '12:01:00', // 12:01 PM
                'end_time' => '16:30:00',   // 4:30 PM
                'status' => true, // or 1
                'open_date' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Add more battle periods if needed
        ]);
    }
}
