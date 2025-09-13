<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TwoDLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('two_d_limits')->insert([
            'two_d_limit' => 50000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
