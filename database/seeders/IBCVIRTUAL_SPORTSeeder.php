<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class IBCVIRTUAL_SPORTSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $jsonPath = base_path('app/Console/Commands/data/ibc_VIRTUAL_SPORT.json');
        $data = json_decode(File::get($jsonPath), true);
        $now = Carbon::now();

        if (isset($data['provider_games']) && is_array($data['provider_games'])) {
            foreach ($data['provider_games'] as $game) {
                if (isset($game['status']) && $game['status'] === 'ACTIVATED') {
                    DB::table('game_lists')->insert([
                        'game_code' => $game['game_code'],
                        'game_name' => $game['game_name'],
                        'game_type' => $game['game_type'],
                        'image_url' => $game['image_url'],
                        'provider_product_id' => $game['product_id'],
                        'game_type_id' => 4,
                        'product_id' => 31,
                        'product_code' => $game['product_code'],
                        'support_currency' => $game['support_currency'],
                        'status' => $game['status'],
                        'provider' => 'JDB',
                        'game_list_status' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
