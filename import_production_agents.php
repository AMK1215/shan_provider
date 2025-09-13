<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Importing production agent data...\n";

// Agent ID 13 - AG61735374 (TestShanAgent)
$agent13 = User::find(13);
if (!$agent13) {
    $agent13 = User::create([
        'id' => 13,
        'user_name' => 'AG61735374',
        'name' => 'TestShanAgent',
        'phone' => '12034567890',
        'password' => Hash::make('password'),
        'status' => 1,
        'is_changed_password' => 1,
        'agent_id' => 1, // Parent agent
        'type' => 20, // Agent type
        'shan_agent_code' => 'A3H4',
        'shan_agent_name' => 'ShanAMK',
        'shan_secret_key' => 'HyrmLxMg4rvOoTZ',
        'shan_callback_url' => 'https://ponewine20x.xyz/api',
        'main_balance' => 0.00,
        'limit' => 50000.00,
        'limit3' => 50000.00,
        'cor' => 0.10,
        'cor3' => 0.10,
    ]);
    echo "Created Agent ID 13: AG61735374\n";
} else {
    echo "Agent ID 13 already exists: " . $agent13->user_name . "\n";
}

// Agent ID 17 - AG48262041 (ShanA3H1)
$agent17 = User::find(17);
if (!$agent17) {
    $agent17 = User::create([
        'id' => 17,
        'user_name' => 'AG48262041',
        'name' => 'ShanA3H1',
        'phone' => '09683412571',
        'password' => Hash::make('password'),
        'status' => 1,
        'is_changed_password' => 1,
        'agent_id' => 1, // Parent agent
        'type' => 20, // Agent type
        'shan_agent_code' => 'A3H2',
        'shan_agent_name' => 'ShanJ6Y1',
        'shan_secret_key' => 'NOebnAVhneABci6',
        'shan_callback_url' => 'https://ponewine20x.pro',
        'main_balance' => 0.00,
        'limit' => 50000.00,
        'limit3' => 50000.00,
        'cor' => 0.10,
        'cor3' => 0.10,
    ]);
    echo "Created Agent ID 17: AG48262041\n";
} else {
    echo "Agent ID 17 already exists: " . $agent17->user_name . "\n";
}

// Create test players P0101 and P0104 if they don't exist
$player1 = User::where('user_name', 'P0101')->first();
if (!$player1) {
    $player1 = User::create([
        'user_name' => 'P0101',
        'name' => 'P0101',
        'password' => Hash::make('password'),
        'status' => 1,
        'is_changed_password' => 1,
        'agent_id' => 13, // Link to Agent ID 13
        'type' => 40, // Player type
        'shan_agent_code' => 'A3H4',
        'main_balance' => 0.00,
        'limit' => 50000.00,
        'limit3' => 50000.00,
        'cor' => 0.10,
        'cor3' => 0.10,
    ]);
    echo "Created Player P0101 linked to Agent ID 13\n";
} else {
    // Update existing player to link to correct agent
    $player1->agent_id = 13;
    $player1->shan_agent_code = 'A3H4';
    $player1->save();
    echo "Updated Player P0101 to link to Agent ID 13\n";
}

$player2 = User::where('user_name', 'P0104')->first();
if (!$player2) {
    $player2 = User::create([
        'user_name' => 'P0104',
        'name' => 'P0104',
        'password' => Hash::make('password'),
        'status' => 1,
        'is_changed_password' => 1,
        'agent_id' => 13, // Link to Agent ID 13
        'type' => 40, // Player type
        'shan_agent_code' => 'A3H4',
        'main_balance' => 0.00,
        'limit' => 50000.00,
        'limit3' => 50000.00,
        'cor' => 0.10,
        'cor3' => 0.10,
    ]);
    echo "Created Player P0104 linked to Agent ID 13\n";
} else {
    // Update existing player to link to correct agent
    $player2->agent_id = 13;
    $player2->shan_agent_code = 'A3H4';
    $player2->save();
    echo "Updated Player P0104 to link to Agent ID 13\n";
}

echo "\nVerification:\n";
echo "Agent ID 13: " . ($agent13 ? $agent13->user_name : 'Not found') . "\n";
echo "Agent ID 17: " . ($agent17 ? $agent17->user_name : 'Not found') . "\n";
echo "Player P0101 Agent: " . ($player1 ? $player1->agent_id : 'Not found') . "\n";
echo "Player P0104 Agent: " . ($player2 ? $player2->agent_id : 'Not found') . "\n"; 