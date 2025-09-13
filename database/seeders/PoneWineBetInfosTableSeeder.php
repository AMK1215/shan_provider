<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PoneWineBetInfosTableSeeder extends Seeder
{
    public function run(): void
    {
        $playerBetIds = DB::table('pone_wine_player_bets')->pluck('id')->toArray();

        foreach ($playerBetIds as $playerBetId) {
            DB::table('pone_wine_bet_infos')->insert([
                'bet_no' => 'BET-'.Str::random(6),
                'bet_amount' => rand(100, 1000),
                'pone_wine_player_bet_id' => $playerBetId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
