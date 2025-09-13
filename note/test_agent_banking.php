<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Services\WalletService;
use App\Enums\TransactionName;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Agent-Based Banking System...\n\n";

try {
    // Get agent and player
    $agent = User::find(13); // AG61735374
    $player = User::where('user_name', 'P0101')->first();
    
    if (!$agent || !$player) {
        echo "Error: Agent or player not found\n";
        exit(1);
    }
    
    echo "Agent Details:\n";
    echo "  ID: {$agent->id}\n";
    echo "  Username: {$agent->user_name}\n";
    echo "  Type: {$agent->type}\n";
    echo "  Shan Code: {$agent->shan_agent_code}\n";
    echo "  Balance: {$agent->balanceFloat}\n\n";
    
    echo "Player Details:\n";
    echo "  ID: {$player->id}\n";
    echo "  Username: {$player->user_name}\n";
    echo "  Type: {$player->type}\n";
    echo "  Agent ID: {$player->agent_id}\n";
    echo "  Balance: {$player->balanceFloat}\n\n";
    
    // Test transaction logic
    $walletService = new WalletService();
    
    // Simulate player loss (player pays agent)
    $betAmount = 500;
    $amountChanged = 500;
    $winLoseStatus = 0; // Loss
        
    echo "Simulating Player Loss Transaction:\n";
    echo "  Bet Amount: {$betAmount}\n";
    echo "  Amount Changed: {$amountChanged}\n";
    echo "  Win/Lose Status: {$winLoseStatus} (Loss)\n\n";
    
    // Capture balances before
    $agentBeforeBalance = $agent->balanceFloat;
    $playerBeforeBalance = $player->balanceFloat;
    
    echo "Balances Before Transaction:\n";
    echo "  Agent: {$agentBeforeBalance}\n";
    echo "  Player: {$playerBeforeBalance}\n\n";
    
    // Simulate the transaction
    if ($winLoseStatus == 0) {
        // Player loses - Player pays the agent
        $walletService->forceTransfer(
            $player,
            $agent,
            $amountChanged,
            TransactionName::Loss,
            [
                'reason' => 'player_lose_test',
                'game_type_id' => 15,
                'wager_code' => 'TEST123',
                'bet_amount' => $betAmount,
            ]
        );
    }
    
    // Refresh balances
    $agent->refresh();
    $player->refresh();
    
    $agentAfterBalance = $agent->balanceFloat;
    $playerAfterBalance = $player->balanceFloat;
    
    echo "Balances After Transaction:\n";
    echo "  Agent: {$agentAfterBalance} (Change: " . ($agentAfterBalance - $agentBeforeBalance) . ")\n";
    echo "  Player: {$playerAfterBalance} (Change: " . ($playerAfterBalance - $playerBeforeBalance) . ")\n\n";
    
    // Verify the transaction worked correctly
    $expectedAgentChange = $amountChanged; // Agent gains what player loses
    $expectedPlayerChange = -$amountChanged; // Player loses the amount
    
    $actualAgentChange = $agentAfterBalance - $agentBeforeBalance;
    $actualPlayerChange = $playerAfterBalance - $playerBeforeBalance;
    
    echo "Transaction Verification:\n";
    echo "  Expected Agent Change: {$expectedAgentChange}\n";
    echo "  Actual Agent Change: {$actualAgentChange}\n";
    echo "  Expected Player Change: {$expectedPlayerChange}\n";
    echo "  Actual Player Change: {$actualPlayerChange}\n\n";
    
    if ($actualAgentChange == $expectedAgentChange && $actualPlayerChange == $expectedPlayerChange) {
        echo "✅ Transaction successful! Agent-based banking system working correctly.\n";
    } else {
        echo "❌ Transaction failed! Balances don't match expected values.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 