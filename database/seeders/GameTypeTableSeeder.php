<?php

namespace Database\Seeders;

use App\Models\GameType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $gameTypes = [
            ['code' => 'SLOT', 'name' => 'Slot', 'name_mm' => 'Slot', 'img' => 'jackpot.png', 'status' => 1, 'order' => '1'],
            ['code' => 'LIVE_CASINO', 'name' => 'Live Casino', 'name_mm' => 'Live Casino', 'img' => 'live_casino.png', 'status' => 1, 'order' => '2'],
            ['code' => 'SPORT_BOOK', 'name' => 'Sport Book', 'name_mm' => 'Sport Book', 'img' => 'sportbook.png', 'status' => 1, 'order' => '3'],
            ['code' => 'VIRTUAL_SPORT', 'name' => 'Virtual Sport', 'name_mm' => 'Virtual Sport', 'img' => 'virtual_sport.png', 'status' => 1, 'order' => '4'],
            ['code' => 'LOTTERY', 'name' => 'Lottery', 'name_mm' => 'Lottery', 'img' => 'lottery.png', 'status' => 1, 'order' => '5'],
            ['code' => 'QIPAI', 'name' => 'Qipai', 'name_mm' => 'Qipai', 'img' => 'qipia.png', 'status' => 0, 'order' => '6'],
            ['code' => 'P2P', 'name' => 'P2P', 'name_mm' => 'P2P', 'img' => 'p2p.png', 'status' => 0, 'order' => '7'],
            ['code' => 'FISHING', 'name' => 'Fishing', 'name_mm' => 'Fishing', 'img' => 'fishing.png', 'status' => 1, 'order' => '8'],
            ['code' => 'COCK_FIGHTING', 'name' => 'Cock Fighting', 'name_mm' => 'Cock Fighting', 'img' => 'fishing.png', 'status' => 0, 'order' => '9'],
            ['code' => 'BONUS', 'name' => 'Bonus', 'name_mm' => 'Bonus', 'img' => 'bonus.png', 'status' => 0, 'order' => '10'],
            ['code' => 'ESPORT', 'name' => 'ESport', 'name_mm' => 'ESport', 'img' => 'esport.png', 'status' => 0, 'order' => '11'],
            ['code' => 'POKER', 'name' => 'Poker', 'name_mm' => 'Poker', 'img' => 'poker.jpg', 'status' => 1, 'order' => '12'],
            ['code' => 'OTHERS', 'name' => 'Others', 'name_mm' => 'Others', 'img' => 'other.png', 'status' => 1, 'order' => '13'],
            ['code' => 'LIVE_CASINO_PREMIUM', 'name' => 'Live Casino Premium', 'name_mm' => 'Live Casino Premium', 'img' => 'live_casino.png', 'status' => 0, 'order' => '14'],
        ];

        foreach ($gameTypes as $gameTypeData) {
            GameType::create($gameTypeData);
        }
    }
}
