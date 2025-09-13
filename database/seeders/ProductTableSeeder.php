<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('products')->insert([
            ['code' => 1138, 'name' => 'SPRIBE', 'short_name' => null, 'order' => 1, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1020, 'name' => 'WM Casino', 'short_name' => null, 'order' => 2, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1038, 'name' => 'King855/CT855 (K9)', 'short_name' => null, 'order' => 3, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1191, 'name' => 'King855/CT855(K0)', 'short_name' => null, 'order' => 4, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1058, 'name' => 'BGaming', 'short_name' => null, 'order' => 5, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1102, 'name' => 'KA Gaming', 'short_name' => null, 'order' => 6, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1070, 'name' => 'Booongo', 'short_name' => null, 'order' => 7, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1097, 'name' => 'Funta Gaming', 'short_name' => null, 'order' => 8, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1111, 'name' => 'Gaming World', 'short_name' => null, 'order' => 9, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1098, 'name' => 'Felix Gaming', 'short_name' => null, 'order' => 10, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1065, 'name' => 'KIRON', 'short_name' => null, 'order' => 11, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1055, 'name' => 'Mr Slotty', 'short_name' => null, 'order' => 12, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1064, 'name' => 'Net Game', 'short_name' => null, 'order' => 13, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1067, 'name' => 'Red Rake', 'short_name' => null, 'order' => 14, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1062, 'name' => 'Fazi', 'short_name' => null, 'order' => 15, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1101, 'name' => 'ZeusPlay', 'short_name' => null, 'order' => 16, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1060, 'name' => 'Volt Entertainment', 'short_name' => null, 'order' => 17, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1148, 'name' => 'WOW Gaming', 'short_name' => null, 'order' => 18, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1149, 'name' => 'AI LIVE CASINO', 'short_name' => null, 'order' => 19, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1022, 'name' => 'Sexy Gaming', 'short_name' => null, 'order' => 20, 'status' => 1, 'game_list_status' => 0],
            ['code' => 1033, 'name' => 'SV388', 'short_name' => null, 'order' => 21, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1139, 'name' => 'FASTSPIN', 'short_name' => null, 'order' => 22, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1050, 'name' => 'PlayStar', 'short_name' => null, 'order' => 23, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1009, 'name' => 'CQ9', 'short_name' => null, 'order' => 24, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1011, 'name' => 'Play Tech', 'short_name' => null, 'order' => 25, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1016, 'name' => 'YEE Bet', 'short_name' => null, 'order' => 26, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1007, 'name' => 'PG Soft (Direct)', 'short_name' => null, 'order' => 27, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1046, 'name' => 'IBC-SABA', 'short_name' => null, 'order' => 28, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1006, 'name' => 'Pragmatic Play', 'short_name' => null, 'order' => 29, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1091, 'name' => 'jili', 'short_name' => null, 'order' => 30, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1161, 'name' => 'Tada', 'short_name' => null, 'order' => 31, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1012, 'name' => 'SBO', 'short_name' => null, 'order' => 32, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1052, 'name' => 'Dream Gaming', 'short_name' => null, 'order' => 33, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1085, 'name' => 'JDB', 'short_name' => null, 'order' => 34, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1049, 'name' => 'Evoplay', 'short_name' => null, 'order' => 35, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1040, 'name' => 'WBET', 'short_name' => null, 'order' => 36, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1153, 'name' => 'Hacksaw', 'short_name' => null, 'order' => 37, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1154, 'name' => 'Bigpot', 'short_name' => null, 'order' => 38, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1018, 'name' => 'Live22', 'short_name' => null, 'order' => 39, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1157, 'name' => 'IMOON', 'short_name' => null, 'order' => 40, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1004, 'name' => 'Big Gaming', 'short_name' => null, 'order' => 41, 'status' => 1, 'game_list_status' => 0],
            ['code' => 1160, 'name' => 'EPICWIN', 'short_name' => null, 'order' => 42, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1163, 'name' => 'NOVOMATIC', 'short_name' => null, 'order' => 43, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1162, 'name' => 'Octoplay', 'short_name' => null, 'order' => 44, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1165, 'name' => 'aviatrix', 'short_name' => null, 'order' => 45, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1164, 'name' => 'DIGITAIN', 'short_name' => null, 'order' => 46, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1079, 'name' => 'Fachai', 'short_name' => null, 'order' => 47, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1170, 'name' => 'smartsoft', 'short_name' => null, 'order' => 48, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1172, 'name' => 'World Entertainment', 'short_name' => null, 'order' => 49, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1183, 'name' => 'FB SPORT', 'short_name' => null, 'order' => 50, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1002, 'name' => 'Evolution（ASIA）', 'short_name' => null, 'order' => 51, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1168, 'name' => 'Netent（ASIA）', 'short_name' => null, 'order' => 52, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1169, 'name' => 'Red Tiger（ASIA）', 'short_name' => null, 'order' => 53, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1166, 'name' => 'no limit city （ASIA）', 'short_name' => null, 'order' => 54, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1167, 'name' => 'big time gaming （ASIA）', 'short_name' => null, 'order' => 55, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1173, 'name' => 'Evolution (LATAM)', 'short_name' => null, 'order' => 56, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1174, 'name' => 'Netent (LATAM)', 'short_name' => null, 'order' => 57, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1175, 'name' => 'Red Tiger (LATAM)', 'short_name' => null, 'order' => 58, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1176, 'name' => 'no limit city (LATAM)', 'short_name' => null, 'order' => 59, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1177, 'name' => 'big time gaming(LATAM)', 'short_name' => null, 'order' => 60, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1184, 'name' => 'Rich88', 'short_name' => null, 'order' => 61, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1185, 'name' => 'SA Gaming', 'short_name' => null, 'order' => 62, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1115, 'name' => 'BOOMING GAMES', 'short_name' => null, 'order' => 63, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1192, 'name' => 'AMIGO GAMING', 'short_name' => null, 'order' => 64, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1193, 'name' => 'FB Games', 'short_name' => null, 'order' => 65, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1197, 'name' => 'Habanero', 'short_name' => null, 'order' => 66, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1203, 'name' => 'PlayAce', 'short_name' => null, 'order' => 67, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1221, 'name' => 'SPADE GAMING', 'short_name' => null, 'order' => 68, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1204, 'name' => 'ADVANTPLAY', 'short_name' => null, 'order' => 69, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1222, 'name' => 'TF Gaming', 'short_name' => null, 'order' => 70, 'status' => 1, 'game_list_status' => 1],
            ['code' => 1220, 'name' => 'ASTAR', 'short_name' => null, 'order' => 71, 'status' => 1, 'game_list_status' => 1],
        ]);
    }
}
