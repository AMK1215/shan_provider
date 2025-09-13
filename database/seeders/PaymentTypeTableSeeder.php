<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'AYA Banking',
                'image' => 'aya_banking.png',
            ],
            [
                'name' => 'AYA Pay',
                'image' => 'aya_pay.png',
            ],
            [
                'name' => 'CB Banking',
                'image' => 'cb_banking.png',
            ],
            [
                'name' => 'CB Pay',
                'image' => 'cb_pay.png',
            ],
            [
                'name' => 'KBZ Banking',
                'image' => 'kbz_banking.png',
            ],
            [
                'name' => 'KBZ Pay',
                'image' => 'kpay.png',
            ],
            [
                'name' => 'MAB Banking',
                'image' => 'mab_banking.png',
            ],
            [
                'name' => 'UAB Pay',
                'image' => 'uab_pay.png',
            ],
            [
                'name' => 'Wave Pay',
                'image' => 'wave.png',
            ],
            [
                'name' => 'Yoma Banking',
                'image' => 'yoma_banking.png',
            ],
        ];

        DB::table('payment_types')->insert($types);
    }
}
