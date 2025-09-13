<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdsVedioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all user IDs to use as possible admins
        $adminIds = User::pluck('id')->toArray();

        $videos = [
            'promo_intro.mp4',
            'bonus_offer.mp4',
            'cashback_ad.mp4',
            'spin_win.mp4',
            'weekend_fun.mp4',
        ];

        foreach ($videos as $video) {
            DB::table('ads_vedios')->insert([
                'video_ads' => $video,
                'admin_id' => 1, // $adminIds[array_rand($adminIds)] ?? null, // Assign random admin or null
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
