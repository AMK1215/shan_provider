<?php

namespace Database\Seeders;

use App\Models\Admin\Bank;
use Illuminate\Database\Seeder;

class BankTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bank = [
            [
                'payment_type_id' => 1,
                'agent_id' => 2,
                'account_number' => '03425879743',
                'account_name' => 'Testing',
            ],
            [
                'payment_type_id' => 2,
                'agent_id' => 2,
                'account_number' => '063425879743',
                'account_name' => 'Testing',
            ],
        ];

        Bank::insert($bank);

    }
}
