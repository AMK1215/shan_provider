<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Database State...\n\n";

try {
    // Check all users with P0101 or P0102 in username
    $players = User::whereIn('user_name', ['P0101', 'P0102'])->get();
    
    echo "Players found:\n";
    foreach ($players as $player) {
        echo "  ID: {$player->id}, Username: {$player->user_name}, Type: {$player->type}, Agent ID: {$player->agent_id}, Shan Code: {$player->shan_agent_code}\n";
    }
    echo "\n";

    // Check all agents (type 20)
    $agents = User::where('type', 20)->get();
    
    echo "Agents found:\n";
    foreach ($agents as $agent) {
        echo "  ID: {$agent->id}, Username: {$agent->user_name}, Type: {$agent->type}, Shan Code: {$agent->shan_agent_code}, Shan Name: {$agent->shan_agent_name}\n";
    }
    echo "\n";

    // Check specific IDs that should be agents
    $agent13 = User::find(13);
    $agent15 = User::find(15);
    
    echo "Agent 13:\n";
    if ($agent13) {
        echo "  ID: {$agent13->id}, Username: {$agent13->user_name}, Type: {$agent13->type}, Shan Code: {$agent13->shan_agent_code}\n";
    } else {
        echo "  Not found\n";
    }
    
    echo "Agent 15:\n";
    if ($agent15) {
        echo "  ID: {$agent15->id}, Username: {$agent15->user_name}, Type: {$agent15->type}, Shan Code: {$agent15->shan_agent_code}\n";
    } else {
        echo "  Not found\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 