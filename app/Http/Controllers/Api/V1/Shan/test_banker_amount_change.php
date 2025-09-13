class ShanTransactionController extends Controller
{
    use HttpResponses;

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function ShanTransactionCreate(Request $request): JsonResponse
    {
        Log::info('ShanTransaction: Request received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        try {
            // Step 1: Enhanced Validation with game_type_id
            $validated = $request->validate([
                'banker' => 'required|array',
                'banker.player_id' => 'required|string',
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

            // Step 4: Get agent information and system wallet
            $agent = null;
            
            // First try to find agent by shan_agent_code from player
            // if ($firstPlayer->shan_agent_code) {
            //     $agent = User::where('shan_agent_code', $firstPlayer->shan_agent_code)
            //                 ->where('type', 20) // Ensure it's an agent
            //                 ->first();
                
            //     if ($agent) {
            //         Log::info('ShanTransaction: Found agent by shan_agent_code', [
            //             'player_id' => $firstPlayer->id,
            //             'player_username' => $firstPlayer->user_name,
            //             'shan_agent_code' => $firstPlayer->shan_agent_code,
            //             'agent_id' => $agent->id,
            //             'agent_username' => $agent->user_name,
            //         ]);
            //     }
            // }
            
            // If no agent found by shan_agent_code, try by agent_id
            // if (!$agent && $firstPlayer->agent_id) {
            //     $agent = User::find($firstPlayer->agent_id);
                
            //     // Verify it's actually an agent
            //     if ($agent && $agent->type != 20) {
            //         $agent = null; // Not a valid agent
            //     }
                
            //     if ($agent) {
            //         Log::info('ShanTransaction: Found agent by agent_id', [
            //             'player_id' => $firstPlayer->id,
            //             'player_username' => $firstPlayer->user_name,
            //             'agent_id' => $agent->id,
            //             'agent_username' => $agent->user_name,
            //         ]);
            //     }
            // }

            // If still no agent found, try to find by common agent codes
            // if (!$agent) {
            //     $commonAgentCodes = ['A3H4', 'A3H2']; // Common agent codes from production
            //     foreach ($commonAgentCodes as $code) {
            //         $agent = User::where('shan_agent_code', $code)
            //                     ->where('type', 20)
            //                     ->first();
            //         if ($agent) {
            //             Log::warning('ShanTransaction: Using default agent by common code', [
            //                 'player_id' => $firstPlayer->id,
            //                 'player_username' => $firstPlayer->user_name,
            //                 'agent_code' => $code,
            //                 'agent_id' => $agent->id,
            //                 'agent_username' => $agent->user_name,
            //             ]);
            //             break;
            //         }
            //     }
            // }

            // If still no agent found, get the first available agent
            // if (!$agent) {
            //     $agent = User::where('type', 20)->first();
                
            //     if ($agent) {
            //         Log::warning('ShanTransaction: No agent found for player, using default agent', [
            //             'player_id' => $firstPlayer->id,
            //             'player_username' => $firstPlayer->user_name,
            //             'default_agent_id' => $agent->id,
            //             'default_agent_username' => $agent->user_name,
            //         ]);
            //     }
            // }

            // Get system wallet (default banker)
            $systemWallet = User::adminUser();
            if (!$systemWallet) {
                Log::error('ShanTransaction: System wallet not found');
                return $this->error('System wallet not found', 'System wallet (SYS001) not configured', 500);
            }

            $secretKey = $agent?->shan_secret_key;
            $callbackUrlBase = $agent?->shan_callback_url;

            Log::info('ShanTransaction: Agent and system wallet information', [
               // 'agent_id' => $agent?->id,
                'agent_username' => $agent?->user_name,
                'agent_type' => $agent?->type,
                'agent_shan_code' => $agent?->shan_agent_code,
                'system_wallet_id' => $systemWallet->id,
                'system_wallet_username' => $systemWallet->user_name,
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

            // Capture system wallet balance BEFORE player transactions
            $systemWalletBeforeBalance = $systemWallet->balanceFloat;

            // Initialize results array
            $results = [];

            try {
                DB::beginTransaction();

                foreach ($validated['players'] as $playerData) {
                $player = User::where('user_name', $playerData['player_id'])->first();
                if (!$player) {
                    throw new \RuntimeException("Player not found: {$playerData['player_id']}");
                }

                Log::info('ShanTransaction: Processing player', [
                    'player_id' => $player->id,
                    'username' => $player->user_name,
//'agent_id' => $agent->id, // Use found agent ID instead of player's agent_id
                    'player_data' => $playerData,
                ]);

                // Capture balance before transaction
                $beforeBalance = $player->balanceFloat;
                
                // Use amount_changed from request instead of calculating
                $amountChanged = $playerData['amount_changed'];
                $betAmount = $playerData['bet_amount'];
                $winLoseStatus = $playerData['win_lose_status'];
                
                // Calculate net amount for this player based on win/lose status
                // If player wins (status 1), amount_changed is positive for player (negative for banker)
                // If player loses (status 0), amount_changed is negative for player (positive for banker)
                $playerNetAmount = $winLoseStatus == 1 ? $amountChanged : -$amountChanged;
                $totalPlayerNet += $playerNetAmount;

                // Update wallet based on win/lose status
                if ($winLoseStatus == 1) {
                    // Player wins - System wallet pays the player
                    $this->walletService->forceTransfer(
                        $systemWallet, // System wallet pays
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
                    // Player loses - Player pays the system wallet
                    $this->walletService->forceTransfer(
                        $player,
                        $systemWallet, // Player pays system wallet
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

                // Store transaction history with all required fields
                ReportTransaction::create([
                    'user_id' => $player->id,
                   // 'agent_id' => $agent->id, // Use found agent ID instead of player's agent_id
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

                // Add to callback players (exclude system wallet)
                if ($player->user_name !== $systemWallet->user_name) {
                    $callbackPlayers[] = [
                        'player_id' => $player->user_name,
                        'balance' => $afterBalance,
                    ];
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

            // Step 7: Handle banker (could be system wallet or specific player)
            $bankerUserName = $validated['banker']['player_id'];
            $banker = User::where('user_name', $bankerUserName)->first();

            // If banker is not found or is system wallet, use system wallet as default
            if (!$banker || $bankerUserName === $systemWallet->user_name) {
                $banker = $systemWallet;
                Log::info('ShanTransaction: Using system wallet as banker', [
                    'banker_id' => $banker->id,
                    'banker_username' => $banker->user_name,
                ]);
            } else {
                Log::info('ShanTransaction: Using specific player as banker', [
                    'banker_id' => $banker->id,
                    'banker_username' => $banker->user_name,
                ]);
            }

            // Refresh system wallet balance after player transactions
            // if ($banker->id === $systemWallet->id) {
            //     $systemWallet->refresh();
            // }

            $systemWallet->refresh();


            // Capture banker balance after player transactions
            $bankerBeforeBalance = $banker->balanceFloat;
            
            // Calculate banker amount change correctly
            // totalPlayerNet is the net change for all players combined
            // Banker's change is the opposite of players' net change
            $bankerAmountChange = -$totalPlayerNet; // Banker gains what players lose (opposite sign)

            Log::info('ShanTransaction: Processing banker transaction', [
                'banker_id' => $banker->id,
                'banker_username' => $banker->user_name,
                'total_player_net' => $totalPlayerNet,
                'banker_amount_change' => $bankerAmountChange,
                'banker_gains' => $bankerAmountChange > 0 ? 'Yes' : 'No',
                'system_wallet_before_player_transactions' => $systemWalletBeforeBalance,
                'system_wallet_after_player_transactions' => $bankerBeforeBalance,
                'system_wallet_actual_change' => $bankerBeforeBalance - $systemWalletBeforeBalance,
            ]);

                // Update banker wallet - handle both system wallet and specific players
                if ($banker->id === $systemWallet->id) {
                    // System wallet is the banker - no additional transfer needed
                    // The system wallet already received/payed in the player transactions above
                    Log::info('ShanTransaction: System wallet is banker - no additional transfer needed', [
                        'banker_id' => $banker->id,
                        'total_player_net' => $totalPlayerNet,
                        'banker_amount_change' => $bankerAmountChange,
                    ]);
                } else {
                    // Specific player is the banker - transfer from/to system wallet
                    if ($bankerAmountChange > 0) {
                        // Banker receives money from system wallet
                        $this->walletService->forceTransfer(
                            $systemWallet,
                            $banker,
                            $bankerAmountChange,
                            TransactionName::BankerDeposit,
                            [
                                'reason' => 'banker_receive',
                                'game_type_id' => $gameTypeId,
                                'wager_code' => $wagerCode,
                            ]
                        );
                    } elseif ($bankerAmountChange < 0) {
                        // Banker pays money to system wallet
                        $this->walletService->forceTransfer(
                            $banker,
                            $systemWallet,
                            abs($bankerAmountChange),
                            TransactionName::BankerWithdraw,
                            [
                                'reason' => 'banker_payout',
                                'game_type_id' => $gameTypeId,
                                'wager_code' => $wagerCode,
                            ]
                        );
                    }
                }

                // Refresh banker balance
                $banker->refresh();
                $bankerAfterBalance = $banker->balanceFloat;

                // Store banker transaction
                ReportTransaction::create([
                    'user_id' => $banker->id,
                   // 'agent_id' => $agent->id, // Use found agent ID instead of player's agent_id
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
                    'system_wallet_balance' => $systemWallet->balanceFloat,
                ]);

                // Step 8: Send callback to client site
                if ($callbackUrlBase && $secretKey) {
                    $this->sendCallbackToClient(
                        $callbackUrlBase,
                        $wagerCode,
                        $gameTypeId,
                        $callbackPlayers,
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
                    'system_wallet' => [
                        'player_id' => $systemWallet->user_name,
                        'balance' => $systemWallet->balanceFloat,
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
        $callbackUrl = $callbackUrlBase . '/shan/client/balance-update';

        $callbackPayload = [
            'wager_code' => $wagerCode,
            'game_type_id' => $gameTypeId,
            'players' => $callbackPlayers,
            'banker_balance' => $bankerBalance,
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
