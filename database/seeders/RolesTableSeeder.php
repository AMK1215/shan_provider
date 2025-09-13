<?php

namespace Database\Seeders;

use App\Models\Admin\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['title' => 'Owner',         'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Agent',         'created_at' => now(), 'updated_at' => now()],
            ['title' => 'SubAgent',      'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Player',        'created_at' => now(), 'updated_at' => now()],
            ['title' => 'SystemWallet',  'created_at' => now(), 'updated_at' => now()],
        ];

        Role::insert($roles);
    }
}
