<?php

namespace App\Http\Controllers\Api\V1\Shan;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReportTransaction;
use App\Models\User;
use App\Services\WalletService;
use App\Traits\HttpResponses;
use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShanTransactionController extends Controller
{
    use HttpResponses;

    protected WalletService $walletService;
    
    // Provider default player - must always be included in callbacks
    private const PROVIDER_DEFAULT_PLAYER = 'SKP0101';

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create Shan game transaction
     * 
     * CRITICAL: SKP0101 is the provider site's default player and MUST always be included
     * in the callback to prevent the game from stopping. This player represents the system's
     * bank/agent balance and is essential for continuous game operation.
     * 
     * TEMPORARY WORKAROUND: Due to a Java game server bug in banker rotation logic,
     * SKP0101 is forced to always be the banker until the game server is fixed.
     * This prevents NullPointerException crashes during banker changes.
     */
    public function ShanTransactionCreate(Request $request): JsonResponse
    {
        Log::info('ShanTransaction: Request received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        try {
            // Step 1: Enhanced Validation with game_type_id
            $validated = $request->validate([
                'banker' => 'nullable|array',
                'banker.player_id' => 'nullable|string',
                'players' => 'required|array|min:1',
                'players.*.player_id' => 'required|string',
                'players.*.bet_amount' => 'required|numeric|min:0',
                'players.*.win_lose_status' => 'required|integer|in:0,1',
                'players.*.amount_changed' => 'required|numeric',
            ]);

            // Set game_type_id to 15 (always for Shan games)
            $gameTypeId = 15;

            Log::info('ShanTransaction: Validation passed', [
                'game_type_id' => $gameTypeId,
                'player_count' => count($validated['players']),
            ]);

            // Step 2: Check for duplicate transaction using wager_code
            $wagerCode = $request->header('X-Wager-Code');
            if ($wagerCode && ReportTransaction::where('wager_code', $wagerCode)->exists()) {
                Log::warning('ShanTransaction: Duplicate transaction detected', [
                    'wager_code' => $wagerCode,
                ]);
                return $this->error('Duplicate transaction', 'This transaction has already been processed', 409);
            }

            // Step 3: Get first player for agent lookup
            $firstPlayerId = $validated['players'][0]['player_id'];
            $firstPlayer = User::where('user_name', $firstPlayerId)->first();

            if (!$firstPlayer) {
                Log::error('ShanTransaction: First player not found', [
                    'player_id' => $firstPlayerId,
                ]);
                return $this->error('Player not found', 'First player not found in system', 404);
            }

            // Step 4: Get agent information 
            $agent = null;
            
            // Find agent by player's shan_agent_code
            if ($firstPlayer->shan_agent_code) {
                $agent = User::where('shan_agent_code', $firstPlayer->shan_agent_code)
                            ->where('type', 20)
                            ->first();
                
                if ($agent) {
                    Log::info('ShanTransaction: Found agent by shan_agent_code', [
                        'player_id' => $firstPlayer->id,
                        'player_username' => $firstPlayer->user_name,
                        'shan_agent_code' => $firstPlayer->shan_agent_code,
                        'agent_id' => $agent->id,
                        'agent_username' => $agent->user_name,
                    ]);
                }
            }
            
            // Fallback: Find agent by agent_id
            if (!$agent && $firstPlayer->agent_id) {
                $agent = User::find($firstPlayer->agent_id);
                
                if ($agent && $agent->type != 20) {
                    $agent = null; // Not a valid agent
                }
                
                if ($agent) {
                    Log::info('ShanTransaction: Found agent by agent_id', [
                        'player_id' => $firstPlayer->id,
                        'player_username' => $firstPlayer->user_name,
                        'player_agent_id' => $firstPlayer->agent_id,
                        'agent_id' => $agent->id,
                        'agent_username' => $agent->user_name,
                    ]);
                }
            }

            // Fallback: Check banker data for agent
            if (!$agent) {
                $bankerPlayerId = $validated['banker']['player_id'] ?? null;
                if ($bankerPlayerId) {
                    $bankerUser = User::where('user_name', $bankerPlayerId)->first();
                    if ($bankerUser && in_array($bankerUser->type, [10, 20])) {
                        $agent = $bankerUser;
                        Log::info('ShanTransaction: Found agent from banker data', [
                            'banker_player_id' => $bankerPlayerId,
                            'agent_id' => $agent->id,
                            'agent_username' => $agent->user_name,
                            'agent_type' => $agent->type,
                        ]);
                    }
                }
            }

            // Last resort: Get any available agent
            if (!$agent) {
                $agent = User::where('type', 20)->first();
                
                if ($agent) {
                    Log::warning('ShanTransaction: Using fallback agent', [
                        'player_id' => $firstPlayer->id,
                        'player_username' => $firstPlayer->user_name,
                        'fallback_agent_id' => $agent->id,
                        'fallback_agent_username' => $agent->user_name,
                    ]);
                }
            }

            $secretKey = $agent?->shan_secret_key;
            $callbackUrlBase = $agent?->shan_callback_url;

            Log::info('ShanTransaction: Agent information', [
                'agent_id' => $agent?->id,
                'agent_username' => $agent?->user_name,
                'agent_type' => $agent?->type,
                'agent_shan_code' => $agent?->shan_agent_code,
                'has_secret_key' => !empty($secretKey),
                'has_callback_url' => !empty($callbackUrlBase),
            ]);

            // Step 5: Generate unique wager_code if not provided
            if (!$wagerCode) {
                do {
                    $wagerCode = Str::random(12);
                } while (ReportTransaction::where('wager_code', $wagerCode)->exists());
            }

            Log::info('ShanTransaction: Using wager_code', ['wager_code' => $wagerCode]);

                            // Step 6: Process each player's transaction
                $totalPlayerNet = 0;
                $processedPlayers = [];
                $callbackPlayers = [];

                // Initialize results array
                $results = [];

            // Step 7: Determine the actual banker
            $actualBanker = null;
            $bankerBeforeBalance = 0;
            
            // TEMPORARY WORKAROUND: Force SKP0101 to always be the banker
            // This prevents the Java game server from crashing due to banker rotation bug
            $skp0101User = User::where('user_name', 'SKP0101')->first();
            if ($skp0101User) {
                $actualBanker = $skp0101User;
                $bankerBeforeBalance = $skp0101User->balanceFloat;
                
                Log::info('ShanTransaction: Using SKP0101 as permanent banker (workaround for game server bug)', [
                    'banker_id' => $actualBanker->id,
                    'banker_username' => $actualBanker->user_name,
                    'banker_type' => $actualBanker->type,
                    'banker_balance' => $bankerBeforeBalance,
                    'note' => 'This prevents the Java game server from crashing during banker rotation',
                ]);
            } else {
                Log::error('ShanTransaction: SKP0101 not found - cannot continue');
                return $this->error('System error', 'Provider default player not found', 500);
            }
            
            // ORIGINAL LOGIC (commented out until Java game server is fixed)
            /*
            // Check if there's a designated banker in the request
            if (isset($validated['banker']['player_id'])) {
                $designatedBanker = User::where('user_name', $validated['banker']['player_id'])->first();
                
                if ($designatedBanker) {
                    // Use the designated player as banker
                    $actualBanker = $designatedBanker;
                    $bankerBeforeBalance = $designatedBanker->balanceFloat;
                    
                    Log::info('ShanTransaction: Using designated player as banker', [
                        'banker_id' => $actualBanker->id,
                        'banker_username' => $actualBanker->user_name,
                        'banker_type' => $actualBanker->type,
                        'banker_balance' => $bankerBeforeBalance,
                    ]);
                } else {
                    Log::warning('ShanTransaction: Designated banker not found, falling back to agent', [
                        'designated_banker_id' => $validated['banker']['player_id'],
                    ]);
                }
            }
            
            // Fallback to agent if no designated banker or banker not found
            if (!$actualBanker) {
                if (!$agent) {
                    Log::error('ShanTransaction: No agent found for transaction');
                    return $this->error('No agent found', 'No agent available for this transaction', 500);
                }
                
                $actualBanker = $agent;
                $bankerBeforeBalance = $agent->balanceFloat;
                
                Log::error('ShanTransaction: No agent found for transaction');
                return $this->error('No agent found', 'No agent available for this transaction', 500);
            }
            */
            
            $banker = $actualBanker;
            
            // Step 7.5: Check if banker is also a player in this transaction
            $bankerIsPlayer = false;
            $bankerPlayerData = null;
            
            // Check if banker is in the players array
            foreach ($validated['players'] as $playerData) {
                if ($playerData['player_id'] === $banker->user_name) {
                    $bankerIsPlayer = true;
                    $bankerPlayerData = $playerData;
                    Log::info('ShanTransaction: Banker is also a player in this transaction', [
                        'banker_id' => $banker->user_name,
                        'banker_player_data' => $playerData,
                    ]);
                    break;
                }
            }

            try {
                DB::beginTransaction();

                foreach ($validated['players'] as $playerData) {
                    $player = User::where('user_name', $playerData['player_id'])->first();
                    
                    if (!$player) {
                        Log::warning('ShanTransaction: Player not found during processing', [
                            'player_id' => $playerData['player_id'],
                        ]);
                        throw new \RuntimeException("Player not found: {$playerData['player_id']}");
                    }

                    Log::info('ShanTransaction: Processing player', [
                        'player_id' => $player->id,
                        'username' => $player->user_name,
                        'agent_id' => $agent?->id,
                        'agent_name' => $agent?->user_name,
                        'player_data' => $playerData,
                    ]);

                    // Capture balance before transaction
                    $beforeBalance = $player->balanceFloat;
                    
                    // Use amount_changed from request instead of calculating
                    $amountChanged = $playerData['amount_changed'];
                    $betAmount = $playerData['bet_amount'];
                    $winLoseStatus = $playerData['win_lose_status'];
                    
                    // Calculate net amount for this player based on win/lose status
                    $playerNetAmount = $winLoseStatus == 1 ? $amountChanged : -$amountChanged;
                    $totalPlayerNet += $playerNetAmount;

                    // Update wallet based on win/lose status
                    if ($winLoseStatus == 1) {
                        // Player wins - Agent pays the player
                        $this->walletService->forceTransfer(
                            $banker, // Agent pays
                            $player,
                            $amountChanged,
                            TransactionName::Win,
                            [
                                'reason' => 'player_win',
                                'game_type_id' => $gameTypeId,
                                'wager_code' => $wagerCode,
                                'bet_amount' => $betAmount,
                            ]
                        );
                    } else {
                        // Player loses - Player pays the agent
                        $this->walletService->forceTransfer(
                            $player,
                            $banker, // Player pays agent
                            $amountChanged,
                            TransactionName::Loss,
                            [
                                'reason' => 'player_lose',
                                'game_type_id' => $gameTypeId,
                                'wager_code' => $wagerCode,
                                'bet_amount' => $betAmount,
                            ]
                        );
                    }

                    // Refresh player balance
                    $player->refresh();
                    $afterBalance = $player->balanceFloat;

                    // Store transaction history
                    ReportTransaction::create([
                        'user_id' => $player->id,
                        'agent_id' => $agent?->id,
                        'agent_code' => $agent->shan_agent_code,
                        'member_account' => $player->user_name,
                        'transaction_amount' => $amountChanged,
                        'status' => $winLoseStatus,
                        'bet_amount' => $betAmount,
                        'valid_amount' => $betAmount,
                        'before_balance' => $beforeBalance,
                        'after_balance' => $afterBalance,
                        'banker' => 0,
                        'wager_code' => $wagerCode,
                        'settled_status' => $winLoseStatus == 1 ? 'settled_win' : 'settled_loss',
                    ]);

                    // Add to results for API response
                    $results[] = [
                        'player_id' => $player->user_name,
                        'balance' => $afterBalance,
                    ];

                    // Add to callback players
                    $callbackPlayers[] = [
                        'player_id' => $player->user_name,
                        'balance' => $afterBalance,
                    ];
                    
                    Log::info('ShanTransaction: Added player to callback players', [
                        'player_id' => $player->user_name,
                        'balance' => $afterBalance,
                        'callback_players_count' => count($callbackPlayers),
                        'is_banker' => $player->id === $banker->id,
                    ]);
                    
                    // If this player is also the banker, make sure they're included in callback
                    if ($player->id === $banker->id) {
                        Log::info('ShanTransaction: Player is also banker, ensuring callback inclusion', [
                            'player_id' => $player->user_name,
                            'banker_id' => $banker->user_name,
                            'balance' => $afterBalance,
                        ]);
                    }

                    $processedPlayers[] = array_merge($playerData, [
                        'current_balance' => $afterBalance,
                    ]);

                    Log::info('ShanTransaction: Player transaction completed', [
                        'player_id' => $player->id,
                        'before_balance' => $beforeBalance,
                        'after_balance' => $afterBalance,
                        'amount_changed' => $amountChanged,
                    ]);
                }
                
                // Calculate banker amount change correctly
                $bankerAmountChange = -$totalPlayerNet;

                Log::info('ShanTransaction: Processing banker transaction', [
                    'banker_id' => $banker->id,
                    'banker_username' => $banker->user_name,
                    'total_player_net' => $totalPlayerNet,
                    'banker_amount_change' => $bankerAmountChange,
                    'banker_gains' => $bankerAmountChange > 0 ? 'Yes' : 'No',
                    'banker_before_player_transactions' => $bankerBeforeBalance,
                    'banker_is_player' => $bankerIsPlayer,
                ]);

                // Banker transaction processing
                Log::info('ShanTransaction: Processing banker transaction', [
                    'banker_id' => $banker->id,
                    'total_player_net' => $totalPlayerNet,
                    'banker_amount_change' => $bankerAmountChange,
                ]);

                // Refresh banker balance
                $banker->refresh();
                $bankerAfterBalance = $banker->balanceFloat;
                
                // If banker is also a player, their balance was already updated in the player loop
                // We need to ensure their final balance is correct
                if ($bankerIsPlayer && $bankerPlayerData) {
                    Log::info('ShanTransaction: Banker is also a player, ensuring balance consistency', [
                        'banker_id' => $banker->user_name,
                        'banker_balance_after_player_processing' => $bankerAfterBalance,
                        'expected_banker_balance' => $bankerBeforeBalance + $bankerAmountChange,
                    ]);
                    
                    // The banker's balance should already be correct from the player processing
                    // Just verify it matches our expectations
                    $expectedBalance = $bankerBeforeBalance + $bankerAmountChange;
                    if (abs($bankerAfterBalance - $expectedBalance) > 0.01) {
                        Log::warning('ShanTransaction: Banker balance mismatch detected', [
                            'banker_id' => $banker->user_name,
                            'expected_balance' => $expectedBalance,
                            'actual_balance' => $bankerAfterBalance,
                            'difference' => $bankerAfterBalance - $expectedBalance,
                        ]);
                    }
                }

                // Store banker transaction
                ReportTransaction::create([
                    'user_id' => $banker->id,
                    'agent_id' => $agent->id,
                    'agent_code' => $agent->shan_agent_code,
                    'member_account' => $banker->user_name,
                    'transaction_amount' => abs($bankerAmountChange),
                    'status' => $bankerAmountChange >= 0 ? 1 : 0,
                    'bet_amount' => null,
                    'valid_amount' => null,
                    'before_balance' => $bankerBeforeBalance,
                    'after_balance' => $bankerAfterBalance,
                    'banker' => 1,
                    'wager_code' => $wagerCode,
                    'settled_status' => $bankerAmountChange >= 0 ? 'settled_win' : 'settled_loss',
                ]);

                // Add banker to results
                $results[] = [
                    'player_id' => $banker->user_name,
                    'balance' => $bankerAfterBalance,
                ];

                // Ensure banker is included in callback players (even if not in request players)
                $bankerInCallbackPlayers = false;
                $bankerCallbackIndex = -1;
                
                foreach ($callbackPlayers as $index => $callbackPlayer) {
                    if ($callbackPlayer['player_id'] === $banker->user_name) {
                        $bankerInCallbackPlayers = true;
                        $bankerCallbackIndex = $index;
                        break;
                    }
                }
                
                if (!$bankerInCallbackPlayers) {
                    // Banker not in callback players, add them
                    $callbackPlayers[] = [
                        'player_id' => $banker->user_name,
                        'balance' => $bankerAfterBalance,
                    ];
                    Log::info('ShanTransaction: Added banker to callback players', [
                        'banker_id' => $banker->user_name,
                        'banker_balance' => $bankerAfterBalance,
                        'callback_players_count' => count($callbackPlayers),
                    ]);
                } else {
                    // Banker is in callback players, but we need to ensure their balance is correct
                    // Update the existing entry with the final banker balance
                    $callbackPlayers[$bankerCallbackIndex]['balance'] = $bankerAfterBalance;
                    Log::info('ShanTransaction: Updated existing banker balance in callback players', [
                        'banker_id' => $banker->user_name,
                        'old_balance' => $callbackPlayers[$bankerCallbackIndex]['balance'],
                        'new_balance' => $bankerAfterBalance,
                        'callback_players_count' => count($callbackPlayers),
                    ]);
                }

                Log::info('ShanTransaction: Banker transaction completed', [
                    'banker_id' => $banker->id,
                    'before_balance' => $bankerBeforeBalance,
                    'after_balance' => $bankerAfterBalance,
                    'amount_changed' => $bankerAmountChange,
                    'actual_balance_change' => $bankerAfterBalance - $bankerBeforeBalance,
                ]);

                DB::commit();

                Log::info('ShanTransaction: All transactions committed successfully', [
                    'wager_code' => $wagerCode,
                    'total_player_net' => $totalPlayerNet,
                    'banker_amount_change' => $bankerAmountChange,
                    'processed_players_count' => count($processedPlayers),
                    'banker_balance' => $banker->balanceFloat,
                ]);

                                // Step 8: Send callback to client site
                if ($callbackUrlBase && $secretKey) {
                    // Final check: ensure all players are in callback
                    $finalCallbackPlayers = $callbackPlayers;
                    
                    // CRITICAL FIX: Always ensure SKP0101 (provider default player) is included
                    $skp0101InCallback = false;
                    $skp0101Index = -1;
                    foreach ($finalCallbackPlayers as $index => $player) {
                        if ($player['player_id'] === self::PROVIDER_DEFAULT_PLAYER) {
                            $skp0101InCallback = true;
                            $skp0101Index = $index;
                            break;
                        }
                    }
                    
                    // If SKP0101 is not in callback, add it with current balance
                    if (!$skp0101InCallback) {
                        $skp0101User = User::where('user_name', self::PROVIDER_DEFAULT_PLAYER)->first();
                        if ($skp0101User) {
                            $finalCallbackPlayers[] = [
                                'player_id' => self::PROVIDER_DEFAULT_PLAYER,
                                'balance' => $skp0101User->balanceFloat,
                            ];
                            Log::info('ShanTransaction: Added SKP0101 (provider default player) to callback', [
                                'skp0101_balance' => $skp0101User->balanceFloat,
                                'callback_players_count' => count($finalCallbackPlayers),
                            ]);
                        }
                    } else {
                        // Update SKP0101 balance to ensure it's current
                        $skp0101User = User::where('user_name', self::PROVIDER_DEFAULT_PLAYER)->first();
                        if ($skp0101User) {
                            $finalCallbackPlayers[$skp0101Index]['balance'] = $skp0101User->balanceFloat;
                            Log::info('ShanTransaction: Updated SKP0101 balance in callback', [
                                'skp0101_balance' => $skp0101User->balanceFloat,
                                'callback_index' => $skp0101Index,
                            ]);
                        }
                    }
                    
                    // Double-check that banker is included
                    $bankerInFinalCallback = false;
                    $bankerFinalIndex = -1;
                    foreach ($finalCallbackPlayers as $index => $player) {
                        if ($player['player_id'] === $banker->user_name) {
                            $bankerInFinalCallback = true;
                            $bankerFinalIndex = $index;
                            break;
                        }
                    }
                    
                    if (!$bankerInFinalCallback) {
                        $finalCallbackPlayers[] = [
                            'player_id' => $banker->user_name,
                            'balance' => $bankerAfterBalance,
                        ];
                        Log::warning('ShanTransaction: Banker was missing from final callback, added', [
                            'banker_id' => $banker->user_name,
                            'banker_balance' => $bankerAfterBalance,
                        ]);
                    } else {
                        // Ensure the banker's balance is correct in the final callback
                        $finalCallbackPlayers[$bankerFinalIndex]['balance'] = $bankerAfterBalance;
                        Log::info('ShanTransaction: Final callback banker balance verified', [
                            'banker_id' => $banker->user_name,
                            'final_balance' => $bankerAfterBalance,
                            'callback_index' => $bankerFinalIndex,
                        ]);
                    }
                    
                    Log::info('ShanTransaction: Preparing callback with SKP0101 guarantee', [
                        'callback_players' => $finalCallbackPlayers,
                        'banker_balance' => $bankerAfterBalance,
                        'total_player_net' => $totalPlayerNet,
                        'banker_amount_change' => $bankerAmountChange,
                        'callback_players_count' => count($finalCallbackPlayers),
                        'banker_included' => $bankerInFinalCallback,
                        'banker_is_player' => $bankerIsPlayer,
                        'skp0101_included' => $skp0101InCallback || !$skp0101InCallback, // Always true after our fix
                    ]);
                    
                    $this->sendCallbackToClient(
                        $callbackUrlBase,
                        $wagerCode,
                        $gameTypeId,
                        $finalCallbackPlayers,
                        $bankerAfterBalance,
                        $totalPlayerNet,
                        $bankerAmountChange,
                        $secretKey
                    );
                } else {
                    Log::warning('ShanTransaction: Skipping callback - missing URL or secret key', [
                        'has_callback_url' => !empty($callbackUrlBase),
                        'has_secret_key' => !empty($secretKey),
                    ]);
                }

                Log::info('ShanTransaction: Transaction completed successfully', [
                    'wager_code' => $wagerCode,
                    'results' => $results,
                ]);

                return $this->success([
                    'status' => 'success',
                    'wager_code' => $wagerCode,
                    'players' => $processedPlayers,
                    'banker' => [
                        'player_id' => $banker->user_name,
                        'balance' => $bankerAfterBalance,
                    ],
                    'agent' => [
                        'player_id' => $agent->user_name,
                        'balance' => $agent->balanceFloat,
                    ],
                ], 'Transaction Successful');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('ShanTransaction: Transaction failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'wager_code' => $wagerCode ?? 'not_generated',
                ]);
                return $this->error('Transaction failed', $e->getMessage(), 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ShanTransaction: Validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);
            return $this->error('Validation failed', $e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('ShanTransaction: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Unexpected error', $e->getMessage(), 500);
        }
    }

    /**
     * Send callback to client site
     */
    private function sendCallbackToClient(
        string $callbackUrlBase,
        string $wagerCode,
        int $gameTypeId,
        array $callbackPlayers,
        float $bankerBalance,
        float $totalPlayerNet,
        float $bankerAmountChange,
        string $secretKey
    ): void {
        $callbackUrl = $callbackUrlBase . '/api/shan/client/balance-update';

        $callbackPayload = [
            'wager_code' => $wagerCode,
            'game_type_id' => $gameTypeId,
            'players' => $callbackPlayers,
            'banker_balance' => $bankerBalance,
            'agent_balance' => $bankerBalance,
            'timestamp' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ISO8601),
            'total_player_net' => $totalPlayerNet,
            'banker_amount_change' => $bankerAmountChange,
        ];

        // Generate signature for security
        ksort($callbackPayload);
        $signature = hash_hmac('md5', json_encode($callbackPayload), $secretKey);
        $callbackPayload['signature'] = $signature;

        try {
            $client = new Client();
            $response = $client->post($callbackUrl, [
                'json' => $callbackPayload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Transaction-Key' => 'yYpfrVcWmkwxWx7um0TErYHj4YcHOOWr',
                ],
                'timeout' => 10,
                'connect_timeout' => 5,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('ShanTransaction: Callback successful', [
                    'callback_url' => $callbackUrl,
                    'status_code' => $statusCode,
                    'response_body' => $responseBody,
                    'wager_code' => $wagerCode,
                ]);
            } else {
                Log::error('ShanTransaction: Callback failed with non-2xx status', [
                    'callback_url' => $callbackUrl,
                    'status_code' => $statusCode,
                    'response_body' => $responseBody,
                    'wager_code' => $wagerCode,
                ]);
            }

        } catch (RequestException $e) {
            Log::error('ShanTransaction: Callback failed (RequestException)', [
                'callback_url' => $callbackUrl,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response',
                'wager_code' => $wagerCode,
            ]);
        } catch (\Exception $e) {
            Log::error('ShanTransaction: Callback failed (General Exception)', [
                'callback_url' => $callbackUrl,
                'error' => $e->getMessage(),
                'wager_code' => $wagerCode,
            ]);
        }
    }
}