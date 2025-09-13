<?php

namespace App\Services;

use App\Enums\TransactionName;
use App\Models\Admin\ReportTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShanTransactionService
{
    private const PROVIDER_URL = 'https://ponewine20x.xyz/api/transactions';

    private const TRANSACTION_KEY = 'yYpfrVcWmkwxWx7um0TErYHj4YcHOOWr';

    public function __construct(
        private WalletService $walletService
    ) {}

    public function processTransaction(array $validated, array $players): array
    {
        Log::info('Starting Shan transaction process', [
            'validated_data' => $validated,
            'players' => $players,
        ]);

        $admin = User::adminUser();
        if (! $admin) {
            Log::error('Admin user not found');
            throw new \RuntimeException('Admin (system wallet) not found');
        }

        Log::info('Admin user found', ['admin_id' => $admin->id]);

        DB::beginTransaction();
        try {
            $processedPlayers = [];
            foreach ($players as $index => $playerData) {
                Log::info('Processing player transaction', [
                    'player_index' => $index,
                    'player_data' => $playerData,
                ]);

                $user = User::where('user_name', $playerData['player_id'])->first();
                if (! $user) {
                    Log::error('Player not found', ['player_id' => $playerData['player_id']]);
                    throw new \RuntimeException("Player not found: {$playerData['player_id']}");
                }

                Log::info('Player found', [
                    'player_id' => $user->id,
                    'username' => $user->user_name,
                    'current_balance' => $user->balanceFloat,
                ]);

                $this->handleWalletTransaction($playerData, $user, $admin, $validated['game_type_id']);
                $this->storeTransactionHistory($playerData, $user, $validated['game_type_id']);

                // Refresh user balance
                $user->refresh();

                Log::info('Player transaction completed', [
                    'player_id' => $user->id,
                    'new_balance' => $user->balanceFloat,
                    'transaction_amount' => $playerData['amount_changed'],
                ]);

                // Add processed player with updated balance
                $processedPlayers[] = array_merge($playerData, [
                    'current_balance' => $user->balanceFloat,
                ]);
            }

            DB::commit();
            Log::info('All transactions committed successfully', [
                'processed_players' => $processedPlayers,
            ]);

            return $this->notifyProvider($validated, $processedPlayers);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process Shan transaction', [
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'players' => $players,
                'data' => $validated,
            ]);
            throw $e;
        }
    }

    private function handleWalletTransaction(array $playerData, User $user, User $admin, int $gameTypeId): void
    {
        Log::info('Starting wallet transaction', [
            'player_id' => $user->id,
            'admin_id' => $admin->id,
            'game_type_id' => $gameTypeId,
            'amount' => $playerData['amount_changed'],
            'win_lose_status' => $playerData['win_lose_status'],
        ]);

        try {
            if ($playerData['win_lose_status'] == 1) {
                $this->walletService->forceTransfer(
                    $admin,
                    $user,
                    $playerData['amount_changed'],
                    TransactionName::Win,
                    ['reason' => 'player_win', 'game_type_id' => $gameTypeId]
                );
                Log::info('Win transaction completed', [
                    'player_id' => $user->id,
                    'amount' => $playerData['amount_changed'],
                ]);
            } else {
                $this->walletService->forceTransfer(
                    $user,
                    $admin,
                    $playerData['amount_changed'],
                    TransactionName::Loss,
                    ['reason' => 'player_lose', 'game_type_id' => $gameTypeId]
                );
                Log::info('Loss transaction completed', [
                    'player_id' => $user->id,
                    'amount' => $playerData['amount_changed'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Wallet transaction failed', [
                'error' => $e->getMessage(),
                'player_id' => $user->id,
                'amount' => $playerData['amount_changed'],
            ]);
            throw $e;
        }
    }

    private function storeTransactionHistory(array $playerData, User $user, int $gameTypeId): void
    {
        Log::info('Storing transaction history', [
            'player_id' => $user->id,
            'game_type_id' => $gameTypeId,
            'transaction_data' => $playerData,
        ]);

        try {
            $transaction = ReportTransaction::create([
                'game_type_id' => $gameTypeId,
                'user_id' => $user->id,
                'transaction_amount' => $playerData['amount_changed'],
                'status' => $playerData['win_lose_status'],
                'bet_amount' => $playerData['bet_amount'],
                'valid_amount' => $playerData['bet_amount'],
            ]);

            Log::info('Transaction history stored', [
                'transaction_id' => $transaction->id,
                'player_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store transaction history', [
                'error' => $e->getMessage(),
                'player_id' => $user->id,
                'data' => $playerData,
            ]);
            throw $e;
        }
    }

    private function notifyProvider(array $validated, array $players): array
    {
        $payload = [
            'game_type_id' => $validated['game_type_id'],
            'players' => $players,
        ];

        Log::info('Notifying provider', [
            'payload' => $payload,
        ]);

        try {
            $response = Http::withHeaders([
                'X-Transaction-Key' => self::TRANSACTION_KEY,
                'Accept' => 'application/json',
            ])->post(self::PROVIDER_URL, $payload);

            if (! $response->successful()) {
                Log::error('Provider transaction failed', [
                    'payload' => $payload,
                    'response' => $response->body(),
                    'status_code' => $response->status(),
                ]);
                throw new \RuntimeException('Failed to report to provider');
            }

            Log::info('Provider notification successful', [
                'response' => $response->json(),
            ]);

            return [
                'status' => 'success',
                'provider_result' => $response->json(),
                'players' => $players,
            ];
        } catch (\Exception $e) {
            Log::error('Provider notification failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            throw $e;
        }
    }
}
