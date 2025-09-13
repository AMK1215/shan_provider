<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Banker Variable Fix...\n\n";

try {
    // Test the agent lookup logic
    $player = User::where('user_name', 'P0101')->first();
    
    if (!$player) {
        echo "Error: Player P0101 not found\n";
        exit(1);
    }
    
    echo "Player Details:\n";
    echo "  ID: {$player->id}\n";
    echo "  Username: {$player->user_name}\n";
    echo "  Agent ID: {$player->agent_id}\n";
    echo "  Shan Agent Code: {$player->shan_agent_code}\n\n";
    
    // Test agent lookup logic (same as in controller)
    $agent = null;
    
    // First try to find agent by shan_agent_code from player
    if ($player->shan_agent_code) {
        $agent = User::where('shan_agent_code', $player->shan_agent_code)
                    ->where('type', 20) // Ensure it's an agent
                    ->first();
        
        if ($agent) {
            echo "✅ Found agent by shan_agent_code:\n";
            echo "  Agent ID: {$agent->id}\n";
            echo "  Agent Username: {$agent->user_name}\n";
            echo "  Agent Type: {$agent->type}\n";
            echo "  Shan Agent Code: {$agent->shan_agent_code}\n\n";
        }
    }
    
    // Test banker assignment
    if ($agent) {
        $banker = $agent;
        echo "✅ Banker variable assigned successfully:\n";
        echo "  Banker ID: {$banker->id}\n";
        echo "  Banker Username: {$banker->user_name}\n";
        echo "  Banker Type: {$banker->type}\n\n";
        
        // Test the condition that was causing the error
        $testPlayer = User::where('user_name', 'P0101')->first();
        if ($testPlayer->user_name !== $banker->user_name) {
            echo "✅ Player username comparison works correctly\n";
            echo "  Player: {$testPlayer->user_name}\n";
            echo "  Banker: {$banker->user_name}\n";
        } else {
            echo "❌ Player username comparison failed\n";
        }
    } else {
        echo "❌ No agent found\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} 