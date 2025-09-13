<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WinnerTextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch all user IDs for assigning owner_id
        $userIds = User::pluck('id')->toArray();

        // Sample realistic messages
        $sampleTexts = [
            'Congratulations to our lucky winner of the week!',
            'You’ve just won big – enjoy your reward!',
            'Another winner joins the hall of fame!',
            'Massive win just landed – stay tuned for more!',
            'Cheers to our winner – well played!',
            "What a streak! You've hit the jackpot!",
            'Winner alert – you made it to the top!',
            'That’s a wrap – another big win secured!',
            'Our latest winner is celebrating now!',
            'Big congratulations – you earned it!',
        ];

        foreach ($sampleTexts as $text) {
            DB::table('winner_texts')->insert([
                'text' => $text,
                'owner_id' => 1, // $userIds[array_rand($userIds)] ?? null, // Assign random user or null
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
