<?php

/*
MANUAL SCRIPT TO ADD DEFAULT AGENTS
===================================

Run this script if you need to add the default agents without running full seeder.

Usage:
php add_default_agents.php

This will create:
- AG72360789 (with shan_agent_code: AG72)
- AG72361782 (with shan_agent_code: AG73)
*/

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Enums\UserType;
use App\Services\WalletService;
use App\Enums\TransactionName;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Adding default agents...\n";

// Get owner for transfers
$owner = User::where('type', UserType::Owner->value)->first();
if (!$owner) {
    echo "Error: No owner found. Please run the full seeder first.\n";
    exit(1);
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
        echo "Agent {$agentData['username']} already exists (ID: {$existingAgent->id})\n";
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
    
    echo "✅ Created agent: {$agentData['username']} (ID: {$agent->id}) with balance: " . number_format($initialBalance) . "\n";
    echo "   - Shan Code: {$agentData['shan_agent_code']}\n";
    echo "   - Secret Key: {$agentData['shan_secret_key']}\n";
    echo "   - Callback URL: {$agentData['shan_callback_url']}\n\n";
}

echo "Default agents creation completed!\n";

// Show summary
echo "\n=== SUMMARY ===\n";
$allAgents = User::where('type', UserType::Agent->value)
                ->whereIn('user_name', ['AG72360789', 'AG72361782'])
                ->get();

foreach ($allAgents as $agent) {
    echo "Agent: {$agent->user_name}\n";
    echo "  - ID: {$agent->id}\n";
    echo "  - Balance: " . number_format($agent->balanceFloat) . "\n";
    echo "  - Shan Code: {$agent->shan_agent_code}\n";
    echo "  - Has Secret Key: " . (!empty($agent->shan_secret_key) ? 'Yes' : 'No') . "\n";
    echo "  - Has Callback URL: " . (!empty($agent->shan_callback_url) ? 'Yes' : 'No') . "\n\n";
}
