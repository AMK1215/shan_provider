<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            [
                'title' => 'owner_access',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title' => 'agent_access',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_index',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_create',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_edit',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_delete',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_index',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_create',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_edit',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_delete',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_change_password_access',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'transfer_log',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'make_transfer',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'bank',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'withdraw',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'deposit',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'contact',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_index',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_create',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_edit',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_delete',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title' => 'system_wallet',
                'group' => 'systemwallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'system_wallet_access',
                'group' => 'systemwallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'report_check',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'game_type_access',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_access',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_create',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_edit',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_delete',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_index',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'subagent_access',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'subagent_index',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'subagent_create',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'subagent_edit',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'subagent_delete',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_access',
                'group' => 'agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // subagent permission
            [
                'title' => 'subagent_player_create',
                'group' => 'subagent',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title' => 'subagent_deposit',
                'group' => 'subagent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'subagent_withdraw',
                'group' => 'subagent',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title' => 'player_view',
                'group' => 'subagent',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title' => 'subagent_access',
                'group' => 'subagent',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ];

        Permission::insert($permissions);
    }
}
