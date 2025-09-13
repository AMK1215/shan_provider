<?php

namespace App\Http\Controllers\Api\V1\Shan;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\ProcessedWagerCallback;
use Bavix\Wallet\External\Dto\Extra; // Needed for meta data with forceDeposit/forceWithdraw
use Bavix\Wallet\External\Dto\Option; // Needed for meta data with forceDeposit/forceWithdraw
// Assuming you have TransactionName Enums on the client side too, or define strings directly
// use App\Enums\TransactionName; // If you have this on client side
use DateTimeImmutable; // <--- ADD THIS LINE
use DateTimeZone;     // <--- ADD THIS LINE FOR CONSISTENCY WITH UTC

class BalanceUpdateCallbackController extends Controller
{
    // You might want a WalletService on the client side too, or put these methods directly here
    // For simplicity, I'll put the logic directly here.

    public function handleBalanceUpdate(Request $request)
    {
        Log::info('ClientSite: BalanceUpdateCallback received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            $validated = $request->validate([
                'wager_code' => 'required|string|max:255',
                'game_type_id' => 'nullable|integer',
                'players' => 'required|array',
                'players.*.player_id' => 'required|string|max:255',
                'players.*.balance' => 'required|numeric|min:0', // Player's NEW balance from provider
                'banker_balance' => 'nullable|numeric',
                'timestamp' => 'required|string',
                'total_player_net' => 'nullable|numeric',
                'banker_amount_change' => 'nullable|numeric',
                //'signature' => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ClientSite: BalanceUpdateCallback validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_REQUEST_DATA',
                'message' => 'Invalid request data: ' . $e->getMessage(),
            ], 400);
        }

        $providerSecretKey = Config::get('shan_key.secret_key');
        Log::info('ClientSite: Provider secret key', ['provider_secret_key' => $providerSecretKey]);
        if (!$providerSecretKey) {
            Log::critical('ClientSite: Provider secret key not configured!');
            return response()->json([
                'status' => 'error', 'code' => 'INTERNAL_ERROR', 'message' => 'Provider secret key not configured on client site.',
            ], 500);
        }

        
        try {
            DB::beginTransaction();

            // Idempotency Check (CRITICAL) - Uncomment and implement this
            if (ProcessedWagerCallback::where('wager_code', $validated['wager_code'])->exists()) {
                DB::commit();
                Log::info('ClientSite: Duplicate wager_code received, skipping processing.', ['wager_code' => $validated['wager_code']]);
                return response()->json(['status' => 'success', 'code' => 'ALREADY_PROCESSED', 'message' => 'Wager already processed.'], 200);
            }

            foreach ($validated['players'] as $playerData) {
                $user = User::where('user_name', $playerData['player_id'])->first();

                if (!$user) {
                    Log::error('ClientSite: Player not found for balance update. Rolling back transaction.', [
                        'player_id' => $playerData['player_id'], 'wager_code' => $validated['wager_code'],
                    ]);
                    throw new \RuntimeException("Player {$playerData['player_id']} not found on client site.");
                }

                $currentBalance = $user->wallet->balanceFloat; // Get current balance
                $newBalance = $playerData['balance']; // New balance from provider
                $balanceDifference = $newBalance - $currentBalance; // Calculate difference

                $meta = [
                    'wager_code' => $validated['wager_code'],
                    'game_type_id' => 15,
                    'provider_new_balance' => $newBalance,
                    'client_old_balance' => $currentBalance,
                    'description' => 'Game settlement from provider',
                ];

                if ($balanceDifference > 0) {
                    // Player won or received funds
                    $user->depositFloat($balanceDifference, $meta);
                    Log::info('ClientSite: Deposited to player wallet', [
                        'player_id' => $user->user_name, 'amount' => $balanceDifference,
                        'new_balance' => $user->wallet->balanceFloat, 'wager_code' => $validated['wager_code'],
                    ]);
                } elseif ($balanceDifference < 0) {
                    // Player lost or paid funds
                    // Use forceWithdrawFloat if balance might go below zero (e.g., for game losses)
                    // Otherwise, use withdrawFloat which checks for sufficient funds.
                    $user->forceWithdrawFloat(abs($balanceDifference), $meta);
                    Log::info('ClientSite: Withdrew from player wallet', [
                        'player_id' => $user->user_name, 'amount' => abs($balanceDifference),
                        'new_balance' => $user->wallet->balanceFloat, 'wager_code' => $validated['wager_code'],
                    ]);
                } else {
                    // Balance is the same, no action needed
                    Log::info('ClientSite: Player balance unchanged', [
                        'player_id' => $user->user_name, 'balance' => $newBalance, 'wager_code' => $validated['wager_code'],
                    ]);
                }

                // Refresh the user model to reflect the latest balance if needed for subsequent operations in the loop
                $user->refresh();
            }

            

            ProcessedWagerCallback::create([
                'wager_code' => $validated['wager_code'],
                'game_type_id' => 15,
                'players' => json_encode($validated['players']), // ✅ encode array to JSON string
                'banker_balance' => $validated['banker_balance'],
                'timestamp' => $validated['timestamp'],
                'total_player_net' => $validated['total_player_net'],
                'banker_amount_change' => $validated['banker_amount_change'],
            ]);

            Log::info('ClientSite: ProcessedWagerCallback created', ['wager_code' => $validated['wager_code']]);
            

            DB::commit();

            Log::info('ClientSite: All balances updated successfully', ['wager_code' => $validated['wager_code']]);

            return response()->json([
                'status' => 'success', 'code' => 'SUCCESS', 'message' => 'Balances updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ClientSite: Error processing balance update', [
                'error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'payload' => $request->all(),
                'wager_code' => $request->input('wager_code'),
            ]);
            return response()->json([
                'status' => 'error', 'code' => 'INTERNAL_SERVER_ERROR', 'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}