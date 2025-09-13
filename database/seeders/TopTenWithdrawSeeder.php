<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopTenWithdrawSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users (if you have an is_admin flag, adjust this accordingly)
        $adminIds = User::pluck('id')->toArray();

        for ($i = 0; $i < 10; $i++) {
            DB::table('top_ten_withdraws')->insert([
                'player_id' => 'P'.str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'amount' => mt_rand(1000, 1000000) / 100, // random amount between 10.00 and 10000.00
                'admin_id' => 1, // $adminIds[array_rand($adminIds)] ?? null, // Assign random admin or null
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
