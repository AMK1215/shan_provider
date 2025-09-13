<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PoneWineBetsTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            DB::table('pone_wine_bets')->insert([
                'room_id' => 'ROOM-'.rand(100, 999),
                'match_id' => 'MATCH-'.rand(1000, 9999),
                'win_number' => (bool) rand(0, 1),
                'status' => (bool) rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
