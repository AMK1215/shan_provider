<?php

namespace App\Http\Controllers\Api\PoneWine;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\PoneWineBet;
use Bavix\Wallet\External\Dto\Extra; 
use Bavix\Wallet\External\Dto\Option; 
use DateTimeImmutable; 
use DateTimeZone;     

class PoneWineClientBalanceUpdateController extends Controller
{
    public function PoneWineClientReport(Request $request)
    {
        Log::info('PoneWine ClientSite: PoneWineClientReport received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            $validated = $request->validate([
                'roomId' => 'required|integer',
                'matchId' => 'required|string|max:255',
                'winNumber' => 'required|integer',
                'wagerCode' => 'required|string|max:255',
                'signature' => 'required|string',
                'players' => 'required|array',
                'players.*.player_id' => 'required|string|max:255',
                'players.*.balance' => 'required|numeric|min:0', // Player's NEW balance from provider
                'players.*.winLoseAmount' => 'required|numeric',
                'players.*.betInfos' => 'required|array',
                'players.*.betInfos.*.betNumber' => 'required|integer',
                'players.*.betInfos.*.betAmount' => 'required|numeric|min:0',
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

        // Validate signature for security
        $signature = $validated['signature'];
        unset($validated['signature']); // Remove signature from payload for validation
        ksort($validated);
        $expectedSignature = hash_hmac('md5', json_encode($validated), $providerSecretKey);
        
        if (!hash_equals($expectedSignature, $signature)) {
            Log::error('ClientSite: Invalid signature received', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'match_id' => $validated['matchId'],
            ]);
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_SIGNATURE',
                'message' => 'Invalid signature provided.',
            ], 401);
        }

        
        try {
            DB::beginTransaction();

            // Idempotency Check (CRITICAL) - Check if match already exists
            if (PoneWineBet::where('match_id', $validated['matchId'])->exists()) {
                DB::commit();
                Log::info('ClientSite: Duplicate match_id received, skipping processing.', ['match_id' => $validated['matchId']]);
                return response()->json(['status' => 'success', 'code' => 'ALREADY_PROCESSED', 'message' => 'Match already processed.'], 200);
            }

            foreach ($validated['players'] as $playerData) {
                $user = User::where('user_name', $playerData['player_id'])->first();

                if (!$user) {
                    Log::error('ClientSite: Player not found for balance update. Rolling back transaction.', [
                        'player_id' => $playerData['player_id'], 'match_id' => $validated['matchId'],
                    ]);
                    throw new \RuntimeException("Player {$playerData['player_id']} not found on client site.");
                }

                $currentBalance = $user->wallet->balanceFloat; // Get current balance
                $newBalance = $playerData['balance']; // New balance from provider
                $balanceDifference = $newBalance - $currentBalance; // Calculate difference

                Log::info('ClientSite: Processing player balance update', [
                    'player_id' => $user->user_name,
                    'current_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'balance_difference' => $balanceDifference,
                    'win_lose_amount' => $playerData['winLoseAmount'],
                    'match_id' => $validated['matchId'],
                ]);

                // Validate that balance difference matches winLoseAmount (with small tolerance for floating point)
                if (abs($balanceDifference - $playerData['winLoseAmount']) > 0.01) {
                    Log::error('ClientSite: Balance difference does not match winLoseAmount', [
                        'player_id' => $user->user_name,
                        'balance_difference' => $balanceDifference,
                        'win_lose_amount' => $playerData['winLoseAmount'],
                        'match_id' => $validated['matchId'],
                    ]);
                    throw new \RuntimeException("Balance difference does not match winLoseAmount for player {$user->user_name}");
                }

                $meta = [
                    'match_id' => $validated['matchId'],
                    'room_id' => $validated['roomId'],
                    'win_number' => $validated['winNumber'],
                    'wager_code' => $validated['wagerCode'],
                    'provider_new_balance' => $newBalance,
                    'client_old_balance' => $currentBalance,
                    'description' => 'Pone Wine game settlement from provider',
                ];

                if ($balanceDifference > 0) {
                    // Player won or received funds
                    $user->depositFloat($balanceDifference, $meta);
                    Log::info('ClientSite: Deposited to player wallet', [
                        'player_id' => $user->user_name, 'amount' => $balanceDifference,
                        'new_balance' => $user->wallet->balanceFloat, 'match_id' => $validated['matchId'],
                    ]);
                } elseif ($balanceDifference < 0) {
                    // Player lost or paid funds
                    // Use forceWithdrawFloat if balance might go below zero (e.g., for game losses)
                    // Otherwise, use withdrawFloat which checks for sufficient funds.
                    $user->forceWithdrawFloat(abs($balanceDifference), $meta);
                    Log::info('ClientSite: Withdrew from player wallet', [
                        'player_id' => $user->user_name, 'amount' => abs($balanceDifference),
                        'new_balance' => $user->wallet->balanceFloat, 'match_id' => $validated['matchId'],
                    ]);
                } else {
                    // Balance is the same, no action needed
                    Log::info('ClientSite: Player balance unchanged', [
                        'player_id' => $user->user_name, 'balance' => $newBalance, 'match_id' => $validated['matchId'],
                    ]);
                }

                // Refresh the user model to reflect the latest balance if needed for subsequent operations in the loop
                $user->refresh();
            }

            // Store game match data
            $gameMatchData = [
                'roomId' => $validated['roomId'],
                'matchId' => $validated['matchId'],
                'winNumber' => $validated['winNumber'],
                'players' => $validated['players']
            ];

            $gameMatch = PoneWineBet::storeGameMatchData($gameMatchData);
            Log::info('ClientSite: Game match data stored', [
                'match_id' => $gameMatch->match_id,
                'room_id' => $gameMatch->room_id,
                'win_number' => $gameMatch->win_number
            ]);
            

            DB::commit();

            Log::info('ClientSite: All balances updated successfully', ['match_id' => $validated['matchId']]);

            return response()->json([
                'status' => 'success', 'code' => 'SUCCESS', 'message' => 'Balances updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ClientSite: Error processing balance update', [
                'error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'payload' => $request->all(),
                'match_id' => $request->input('matchId'),
            ]);
            return response()->json([
                'status' => 'error', 'code' => 'INTERNAL_SERVER_ERROR', 'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
