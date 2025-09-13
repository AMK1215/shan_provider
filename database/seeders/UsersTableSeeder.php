<?php

namespace Database\Seeders;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $walletService = new WalletService;

        // Create owner with large initial capital
        $owner = $this->createUser(
            UserType::Owner,
            'ShanKomee',
            'ShanKomeeProvider',
            '09123456789',
            null,
            'ShanKomeeProvider'.Str::random(6)
        );
        $walletService->deposit($owner, 500_000_00000000, TransactionName::CapitalDeposit);

        // Create system wallet
        $systemWallet = $this->createUser(
            UserType::SystemWallet,
            'System Wallet',
            'SYS001',
            '09222222222',
            null,
            'SYS'.Str::random(6)
        );
        $walletService->deposit($systemWallet, 500 * 100_0000, TransactionName::CapitalDeposit);

        // Create specific default agents
        $defaultAgents = [
            [
                'name' => 'AG72360789',
                'username' => 'AG72360789',
                'phone' => '09972360789',
                'shan_agent_code' => 'AG72',
                'shan_secret_key' => 'AG72_' . Str::random(10),
                'shan_callback_url' => 'https://moonstar543.pro'
            ],
            [
                'name' => 'AG72361782',
                'username' => 'AG72361782', 
                'phone' => '09972361782',
                'shan_agent_code' => 'AG73',
                'shan_secret_key' => 'AG73_' . Str::random(10),
                'shan_callback_url' => 'https://luckymillion.pro'
            ]
        ];

        foreach ($defaultAgents as $agentData) {
            $agent = $this->createAgentWithShanData(
                $agentData['name'],
                $agentData['username'],
                $agentData['phone'],
                $owner->id,
                $agentData['shan_agent_code'],
                $agentData['shan_secret_key'],
                $agentData['shan_callback_url']
            );
            
            // Large initial balance for default agents
            $initialBalance = 2_000_000; // 2M balance
            $walletService->transfer($owner, $agent, $initialBalance, TransactionName::CreditTransfer);
        }

        // Create additional regular agents
        for ($i = 1; $i <= 2; $i++) {
            $agent = $this->createUser(
                UserType::Agent,
                "ShanKomee Agent $i",
                'SKA'.str_pad($i, 3, '0', STR_PAD_LEFT),
                '091123456'.str_pad($i, 2, '0', STR_PAD_LEFT),
                $owner->id,
                'SKA'.Str::random(6)
            );
            // Random initial balance between 1.5M to 2.5M
            $initialBalance = rand(150, 250) * 1000;
            $walletService->transfer($owner, $agent, $initialBalance, TransactionName::CreditTransfer);

            // Create players directly under each agent (no sub-agents)
            for ($k = 1; $k <= 4; $k++) {
                $player = $this->createUser(
                    UserType::Player,
                    "ShanKomee Player $i-$k",
                    'SKP'.str_pad($i, 2, '0', STR_PAD_LEFT).str_pad($k, 2, '0', STR_PAD_LEFT),
                    '091111111'.str_pad($i, 1, '0', STR_PAD_LEFT).str_pad($k, 2, '0', STR_PAD_LEFT),
                    $agent->id,
                    'SKP'.Str::random(6)
                );
                // Fixed initial balance of 10,000
                $initialBalance = 10000;
                $walletService->transfer($agent, $player, $initialBalance, TransactionName::CreditTransfer);
            }
        }
    }

    private function createUser(
        UserType $type,
        string $name,
        string $user_name,
        string $phone,
        ?int $parent_id = null,
        ?string $referral_code = null
    ): User {
        return User::create([
            'name' => $name,
            'user_name' => $user_name,
            'phone' => $phone,
            'password' => Hash::make('shankomee'),
            'agent_id' => $parent_id,
            'status' => 1,
            'is_changed_password' => 1,
            'type' => $type->value,
            'referral_code' => $referral_code,
        ]);
    }

    /**
     * Create agent with Shan-specific data
     */
    private function createAgentWithShanData(
        string $name,
        string $user_name,
        string $phone,
        int $parent_id,
        string $shan_agent_code,
        string $shan_secret_key,
        string $shan_callback_url
    ): User {
        return User::create([
            'name' => $name,
            'user_name' => $user_name,
            'phone' => $phone,
            'password' => Hash::make('shankomee'),
            'agent_id' => $parent_id,
            'status' => 1,
            'is_changed_password' => 1,
            'type' => UserType::Agent->value, // Always agent type
            'referral_code' => $shan_agent_code . Str::random(3),
            'shan_agent_code' => $shan_agent_code,
            'shan_secret_key' => $shan_secret_key,
            'shan_callback_url' => $shan_callback_url,
        ]);
    }
}
