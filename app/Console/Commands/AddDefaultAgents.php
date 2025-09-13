<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Enums\UserType;
use App\Services\WalletService;
use App\Enums\TransactionName;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddDefaultAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agents:add-default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add default agents AG72360789 and AG72361782';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Adding default agents...');

        // Get owner for transfers
        $owner = User::where('type', UserType::Owner->value)->first();
        if (!$owner) {
            $this->error('No owner found. Please run the full seeder first.');
            return;
        }

        $walletService = new WalletService();

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
            // Check if agent already exists
            $existingAgent = User::where('user_name', $agentData['username'])->first();
            
            if ($existingAgent) {
                $this->warn("Agent {$agentData['username']} already exists (ID: {$existingAgent->id})");
                continue;
            }
            
            // Create the agent
            $agent = User::create([
                'name' => $agentData['name'],
                'user_name' => $agentData['username'],
                'phone' => $agentData['phone'],
                'password' => Hash::make('shankomee'),
                'agent_id' => $owner->id,
                'status' => 1,
                'is_changed_password' => 1,
                'type' => UserType::Agent->value,
                'referral_code' => $agentData['shan_agent_code'] . Str::random(3),
                'shan_agent_code' => $agentData['shan_agent_code'],
                'shan_secret_key' => $agentData['shan_secret_key'],
                'shan_callback_url' => $agentData['shan_callback_url'],
            ]);
            
            // Give initial balance
            $initialBalance = 2_000_000; // 2M balance
            $walletService->transfer($owner, $agent, $initialBalance, TransactionName::CreditTransfer);
            
            $this->info("✅ Created agent: {$agentData['username']} (ID: {$agent->id}) with balance: " . number_format($initialBalance));
            $this->line("   - Shan Code: {$agentData['shan_agent_code']}");
            $this->line("   - Secret Key: {$agentData['shan_secret_key']}");
            $this->line("   - Callback URL: {$agentData['shan_callback_url']}");
        }

        $this->info('Default agents creation completed!');

        // Show summary
        $this->newLine();
        $this->line('<fg=green>=== SUMMARY ===</>');
        $allAgents = User::where('type', UserType::Agent->value)
                        ->whereIn('user_name', ['AG72360789', 'AG72361782'])
                        ->get();

        foreach ($allAgents as $agent) {
            $this->line("Agent: {$agent->user_name}");
            $this->line("  - ID: {$agent->id}");
            $this->line("  - Balance: " . number_format($agent->balanceFloat));
            $this->line("  - Shan Code: {$agent->shan_agent_code}");
            $this->line("  - Has Secret Key: " . (!empty($agent->shan_secret_key) ? 'Yes' : 'No'));
            $this->line("  - Has Callback URL: " . (!empty($agent->shan_callback_url) ? 'Yes' : 'No'));
            $this->newLine();
        }
    }
}