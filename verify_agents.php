<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "Verifying agent data...\n";

// Check Agent ID 13
$agent13 = User::find(13);
if ($agent13) {
    echo "Agent ID 13 found:\n";
    echo "Username: " . $agent13->user_name . "\n";
    echo "Type: " . $agent13->type . "\n";
    echo "Shan Agent Code: " . $agent13->shan_agent_code . "\n";
    echo "Shan Secret Key: " . ($agent13->shan_secret_key ? 'Set' : 'Not Set') . "\n";
    echo "Shan Callback URL: " . ($agent13->shan_callback_url ? 'Set' : 'Not Set') . "\n";
    echo "---\n";
} else {
    echo "Agent ID 13 not found\n";
}

// Check Agent ID 17
$agent17 = User::find(17);
if ($agent17) {
    echo "Agent ID 17 found:\n";
    echo "Username: " . $agent17->user_name . "\n";
    echo "Type: " . $agent17->type . "\n";
    echo "Shan Agent Code: " . $agent17->shan_agent_code . "\n";
    echo "Shan Secret Key: " . ($agent17->shan_secret_key ? 'Set' : 'Not Set') . "\n";
    echo "Shan Callback URL: " . ($agent17->shan_callback_url ? 'Set' : 'Not Set') . "\n";
    echo "---\n";
} else {
    echo "Agent ID 17 not found\n";
}

// Check Player P0101
$player1 = User::where('user_name', 'P0101')->first();
if ($player1) {
    echo "Player P0101 found:\n";
    echo "ID: " . $player1->id . "\n";
    echo "Username: " . $player1->user_name . "\n";
    echo "Agent ID: " . $player1->agent_id . "\n";
    echo "Shan Agent Code: " . $player1->shan_agent_code . "\n";
    
    $agent = User::find($player1->agent_id);
    echo "Agent Username: " . ($agent ? $agent->user_name : 'NOT FOUND') . "\n";
    echo "Agent Type: " . ($agent ? $agent->type : 'N/A') . "\n";
    echo "---\n";
} else {
    echo "Player P0101 not found\n";
}

// Test agent lookup logic
echo "Testing agent lookup logic:\n";

// Test 1: Find agent by shan_agent_code
$agentByCode = User::where('shan_agent_code', 'A3H4')->where('type', 20)->first();
echo "Agent by code A3H4: " . ($agentByCode ? $agentByCode->user_name : 'Not found') . "\n";

// Test 2: Find agent by agent_id
$agentById = User::find(13);
echo "Agent by ID 13: " . ($agentById ? $agentById->user_name : 'Not found') . "\n";

// Test 3: Find agent by common codes
$commonCodes = ['A3H4', 'A3H2'];
foreach ($commonCodes as $code) {
    $agent = User::where('shan_agent_code', $code)->where('type', 20)->first();
    echo "Agent by code $code: " . ($agent ? $agent->user_name : 'Not found') . "\n";
}

// Test 4: Get first available agent
$firstAgent = User::where('type', 20)->first();
echo "First available agent: " . ($firstAgent ? $firstAgent->user_name : 'Not found') . "\n"; 