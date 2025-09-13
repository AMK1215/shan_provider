<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use Illuminate\Database\Seeder;

class SubAgentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // View Only Group
            'view_only' => [
                'view_player_list',
                'view_player_report',
                'view_transaction_log',
            ],

            // Player Creation Group
            'player_creation' => [
                'view_player_list',
                'create_player',
                'edit_player',
                'change_player_password',
                'view_player_report',
                'view_transaction_log',
            ],

            // Deposit/Withdraw Group
            'deposit_withdraw' => [
                'view_player_list',
                'view_player_report',
                'view_transaction_log',
                'view_withdraw_requests',
                'view_deposit_requests',
                'process_withdraw',
                'process_deposit',
            ],
        ];

        foreach ($permissions as $group => $permissionList) {
            foreach ($permissionList as $permission) {
                Permission::firstOrCreate([
                    'title' => $permission,
                    'group' => $group,
                ]);
            }
        }
    }
}
