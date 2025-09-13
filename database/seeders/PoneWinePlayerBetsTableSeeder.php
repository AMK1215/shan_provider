<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PoneWinePlayerBetsTableSeeder extends Seeder
{
    public function run(): void
    {
        $betIds = DB::table('pone_wine_bets')->pluck('id')->toArray();
        $userIds = DB::table('users')->pluck('id')->toArray();

        foreach ($betIds as $betId) {
            for ($i = 0; $i < 10; $i++) {
                DB::table('pone_wine_player_bets')->insert([
                    'user_id' => $userIds[array_rand($userIds)] ?? 1, // Fallback to 1 if empty
                    'pone_wine_bet_id' => $betId,
                    'user_name' => 'Player_'.Str::random(5),
                    'win_lose_amt' => rand(-500, 500),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
