<?php

namespace App\Http\Controllers\Api\V1\gplus\Webhook;

use App\Enums\SeamlessWalletCode;
use App\Http\Controllers\Controller;
use App\Models\PlaceBet;
use App\Models\User;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PushBetDataController extends Controller
{
    public function pushBetData(Request $request)
    {
        Log::info('Push Bet Data API Request', ['request' => $request->all()]);

        try {
            $request->validate([
                'operator_code' => 'required|string',
                'wagers' => 'required|array',
                'sign' => 'required|string',
                'request_time' => 'required|integer', // Assuming Unix timestamp in seconds or milliseconds
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Push Bet Data API Validation Failed', ['errors' => $e->errors()]);

            return ApiResponseService::error(
                SeamlessWalletCode::InternalServerError, // Or a more specific validation error code
                'Validation failed',
                $e->errors()
            );
        }

        $secretKey = Config::get('seamless_key.secret_key');
        $expectedSign = md5(
            $request->operator_code.
            $request->request_time.
            'pushbetdata'.
            $secretKey
        );

        if (strtolower($request->sign) !== strtolower($expectedSign)) {
            Log::warning('Push Bet Data Invalid Signature', ['provided' => $request->sign, 'expected' => $expectedSign]);

            return response()->json([
                'code' => SeamlessWalletCode::InvalidSignature->value,
                'message' => 'Invalid signature',
            ]);
        }

        foreach ($request->wagers as $tx) {
            $memberAccount = $tx['member_account'] ?? null;
            $user = User::where('user_name', $memberAccount)->first();

            if (! $user) {
                Log::warning('Member not found for pushBetData', ['member_account' => $memberAccount, 'transaction' => $tx]);

                // Instead of returning, which would stop the whole batch,
                // you might want to log this and potentially skip or flag this specific wager.
                // For now, we'll return, but consider a batch processing approach if required.
                return response()->json([
                    'code' => SeamlessWalletCode::MemberNotExist->value,
                    'message' => 'Member not found',
                ]);
            }

            $transactionId = $tx['wager_code'] ?? null;
            if (! $transactionId) {
                Log::warning('Transaction missing wager_code in pushBetData', ['tx' => $tx]);

                continue; // Skip this specific wager if it lacks a transaction ID
            }

            // Convert timestamps from milliseconds to seconds if they are in milliseconds
            $requestTimeInSeconds = $request->request_time ? floor($request->request_time / 1000) : null;
            $settledAtInSeconds = (isset($tx['settled_at']) && $tx['settled_at']) ? floor($tx['settled_at'] / 1000) : null;
            $createdAtProviderInSeconds = (isset($tx['created_at']) && $tx['created_at']) ? floor($tx['created_at'] / 1000) : null;

            $placeBet = PlaceBet::where('transaction_id', $transactionId)->first();

            if ($placeBet) {
                // Update existing record
                $placeBet->update([
                    'member_account' => $tx['member_account'] ?? $placeBet->member_account,
                    'product_code' => $tx['product_code'] ?? $placeBet->product_code,
                    'amount' => $tx['bet_amount'] ?? $placeBet->amount, // Assuming 'bet_amount' is the amount for this context
                    'action' => $tx['wager_type'] ?? $placeBet->action,
                    'status' => $tx['wager_status'] ?? $placeBet->status,
                    'meta' => json_encode($tx), // Ensure meta is stored as JSON string
                    'wager_status' => $tx['wager_status'] ?? $placeBet->wager_status,
                    'round_id' => $tx['round_id'] ?? $placeBet->round_id,
                    'game_type' => $tx['game_type'] ?? $placeBet->game_type,
                    'channel_code' => $tx['channel_code'] ?? $placeBet->channel_code,
                    // Convert timestamp to DateTime object if it's not already
                    'settle_at' => $settledAtInSeconds ? now()->setTimestamp($settledAtInSeconds) : $placeBet->settle_at,
                    'created_at_provider' => $createdAtProviderInSeconds ? now()->setTimestamp($createdAtProviderInSeconds) : $placeBet->created_at_provider,
                    'currency' => $tx['currency'] ?? $placeBet->currency,
                    'game_code' => $tx['game_code'] ?? $placeBet->game_code,
                    // No need to update operator_code here as it's typically set on creation
                ]);
                Log::info('Updated place_bets record via PushBetData', ['transaction_id' => $transactionId]);
            } else {
                // Insert new record
                PlaceBet::create([
                    'transaction_id' => $transactionId,
                    'member_account' => $tx['member_account'] ?? '',
                    'product_code' => $tx['product_code'] ?? 0,
                    'amount' => $tx['bet_amount'] ?? 0,
                    'action' => $tx['wager_type'] ?? '',
                    'status' => $tx['wager_status'] ?? '', // Initial status from pushbetdata
                    'meta' => json_encode($tx),
                    'wager_status' => $tx['wager_status'] ?? '',
                    'round_id' => $tx['round_id'] ?? '',
                    'game_type' => $tx['game_type'] ?? '',
                    'channel_code' => $tx['channel_code'] ?? '',
                    // FIX: Add operator_code here, as it's a required field in your DB
                    'operator_code' => $request->operator_code,
                    // FIX: Add request_time from the main request
                    'request_time' => $requestTimeInSeconds ? now()->setTimestamp($requestTimeInSeconds) : null,
                    // Convert timestamp to DateTime object if it's not already
                    'settle_at' => $settledAtInSeconds ? now()->setTimestamp($settledAtInSeconds) : null,
                    'created_at_provider' => $createdAtProviderInSeconds ? now()->setTimestamp($createdAtProviderInSeconds) : null,
                    'currency' => $tx['currency'] ?? '',
                    'game_code' => $tx['game_code'] ?? '',
                    'sign' => $request->sign, // Also store the sign
                ]);
                Log::info('Inserted new place_bets record via PushBetData', ['transaction_id' => $transactionId]);
            }
        }

        return response()->json([
            'code' => SeamlessWalletCode::Success->value,
            'message' => '',
        ]);
    }
}
