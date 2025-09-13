<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Agent Fetching Logic...\n\n";

try {
    // Test 1: Check P0101 (should find agent ID 13)
    $player1 = User::where('user_name', 'P0101')->first();
    if ($player1) {
        echo "Test 1 - P0101:\n";
        echo "  Player ID: {$player1->id}\n";
        echo "  Player Username: {$player1->user_name}\n";
        echo "  Agent ID: {$player1->agent_id}\n";
        echo "  Shan Agent Code: {$player1->shan_agent_code}\n";
        echo "  Client Agent Name: {$player1->client_agent_name}\n";
        echo "  Client Agent ID: {$player1->client_agent_id}\n";
        
        // Test agent lookup logic
        $agent = null;
        if ($player1->shan_agent_code) {
            $agent = User::where('shan_agent_code', $player1->shan_agent_code)
                        ->where('type', 20)
                        ->first();
        }
        
        if ($agent) {
            echo "  Found Agent: ID {$agent->id}, Username: {$agent->user_name}\n";
        } else {
            echo "  No agent found!\n";
        }
        echo "\n";
    }

    // Test 2: Check P0102 (should find agent ID 13)
    $player2 = User::where('user_name', 'P0102')->first();
    if ($player2) {
        echo "Test 2 - P0102:\n";
        echo "  Player ID: {$player2->id}\n";
        echo "  Player Username: {$player2->user_name}\n";
        echo "  Agent ID: {$player2->agent_id}\n";
        echo "  Shan Agent Code: {$player2->shan_agent_code}\n";
        echo "  Client Agent Name: {$player2->client_agent_name}\n";
        echo "  Client Agent ID: {$player2->client_agent_id}\n";
        
        // Test agent lookup logic
        $agent = null;
        if ($player2->shan_agent_code) {
            $agent = User::where('shan_agent_code', $player2->shan_agent_code)
                        ->where('type', 20)
                        ->first();
        }
        
        if ($agent) {
            echo "  Found Agent: ID {$agent->id}, Username: {$agent->user_name}\n";
        } else {
            echo "  No agent found!\n";
        }
        echo "\n";
    }

    // Test 3: Check Agent 13 details
    $agent13 = User::find(13);
    if ($agent13) {
        echo "Test 3 - Agent 13:\n";
        echo "  Agent ID: {$agent13->id}\n";
        echo "  Agent Username: {$agent13->user_name}\n";
        echo "  Agent Type: {$agent13->type}\n";
        echo "  Shan Agent Code: {$agent13->shan_agent_code}\n";
        echo "  Shan Agent Name: {$agent13->shan_agent_name}\n";
        echo "  Shan Secret Key: " . (empty($agent13->shan_secret_key) ? 'NULL' : 'SET') . "\n";
        echo "  Shan Callback URL: " . (empty($agent13->shan_callback_url) ? 'NULL' : 'SET') . "\n";
        echo "\n";
    }

    // Test 4: Check Agent 15 details
    $agent15 = User::find(15);
    if ($agent15) {
        echo "Test 4 - Agent 15:\n";
        echo "  Agent ID: {$agent15->id}\n";
        echo "  Agent Username: {$agent15->user_name}\n";
        echo "  Agent Type: {$agent15->type}\n";
        echo "  Shan Agent Code: {$agent15->shan_agent_code}\n";
        echo "  Shan Agent Name: {$agent15->shan_agent_name}\n";
        echo "  Shan Secret Key: " . (empty($agent15->shan_secret_key) ? 'NULL' : 'SET') . "\n";
        echo "  Shan Callback URL: " . (empty($agent15->shan_callback_url) ? 'NULL' : 'SET') . "\n";
        echo "\n";
    }

    echo "Agent fetching test completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 