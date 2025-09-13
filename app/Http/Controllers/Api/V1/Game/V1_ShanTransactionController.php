<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReportTransaction;
use App\Models\User;
use App\Services\WalletService;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShanTransactionController extends Controller
{
    use HttpResponses;

    public function __construct(
        private WalletService $walletService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'game_type_id' => 'required|integer',
            'players' => 'required|array|min:1',
            'players.*.player_id' => 'required|string',
            'players.*.bet_amount' => 'required|numeric',
            'players.*.amount_changed' => 'required|numeric',
            'players.*.win_lose_status' => 'required|integer|in:0,1',
        ]);

        try {
            DB::beginTransaction();

            // Get the system wallet (banker)
            $banker = User::adminUser();
            if (! $banker) {
                return $this->error('', 'Banker (system wallet) not found', 404);
            }

            $results = [];
            $processedPlayers = [];

            // Process each player's transaction
            foreach ($validatedData['players'] as $playerData) {
                $player = User::where('user_name', $playerData['player_id'])->first();
                if (! $player) {
                    throw new \RuntimeException("Player not found: {$playerData['player_id']}");
                }

                Log::info('Processing player transaction', [
                    'player_id' => $player->id,
                    'player_data' => $playerData,
                ]);

                // Handle wallet transaction
                if ($playerData['win_lose_status'] == 1) {
                    // Player wins: banker pays the player
                    $this->walletService->forceTransfer(
                        $banker,
                        $player,
                        $playerData['amount_changed'],
                        TransactionName::Win,
                        ['reason' => 'player_win', 'game_type_id' => $validatedData['game_type_id']]
                    );
                } else {
                    // Player loses: player pays the banker
                    $this->walletService->forceTransfer(
                        $player,
                        $banker,
                        $playerData['amount_changed'],
                        TransactionName::Loss,
                        ['reason' => 'player_lose', 'game_type_id' => $validatedData['game_type_id']]
                    );
                }

                // Store transaction history
                ReportTransaction::create([
                    'user_id' => $player->id,
                    'game_type_id' => $validatedData['game_type_id'],
                    'transaction_amount' => $playerData['amount_changed'],
                    'status' => $playerData['win_lose_status'],
                    'bet_amount' => $playerData['bet_amount'],
                    'valid_amount' => $playerData['bet_amount'],
                ]);

                // Refresh player balance
                $player->refresh();

                // Add to results
                $processedPlayers[] = array_merge($playerData, [
                    'current_balance' => $player->balanceFloat,
                ]);

                $results[] = [
                    'player_id' => $player->user_name,
                    'balance' => $player->balanceFloat,
                ];
            }

            // Store banker transaction
            ReportTransaction::create([
                'user_id' => $banker->id,
                'game_type_id' => $validatedData['game_type_id'],
                'transaction_amount' => array_sum(array_column($validatedData['players'], 'amount_changed')),
                'banker' => 1,
                'final_turn' => 1,
            ]);

            // Refresh banker balance
            $banker->refresh();
            $results[] = [
                'player_id' => $banker->user_name,
                'balance' => $banker->balanceFloat,
            ];

            DB::commit();

            Log::info('All transactions completed successfully', [
                'results' => $results,
            ]);

            return $this->success([
                'status' => 'success',
                'players' => $processedPlayers,
                'banker' => [
                    'player_id' => $banker->user_name,
                    'balance' => $banker->balanceFloat,
                ],
            ], 'Transaction Successful');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Transaction failed', $e->getMessage(), 500);
        }
    }
}
