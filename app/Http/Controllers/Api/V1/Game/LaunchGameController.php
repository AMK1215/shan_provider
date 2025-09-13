<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Enums\SeamlessWalletCode;
use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Make sure to use Http facade for making requests
use Illuminate\Support\Str;

class LaunchGameController extends Controller
{
    /**
     * Provider Launch Game - receives request and responds with launch game URL
     * This is a provider endpoint that builds and returns game URLs to client sites
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function launchGameForClient(Request $request)
    {
        Log::info('Provider Launch Game Request', ['request' => $request->all()]);

        try {
            $validatedData = $request->validate([
                'agent_code' => 'required|string',
                'product_code' => 'required|integer',
                'game_type' => 'required|string',
                'member_account' => 'required|string',
                'balance' => 'required|numeric|min:0',
                'nickname' => 'nullable|string',
                'callback_url' => 'nullable|string',
            ]);

            // Use MMK currency for all products
            $apiCurrency = 'MMK';
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Provider Launch Game Validation Failed', ['errors' => $e->errors()]);

            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Get or create user from member_account
        $memberAccount = $request->member_account;
        $requestedBalance = $request->balance;
        $clientUser = User::where('user_name', $memberAccount)->first();

        // Get agent information from agent_code
        $agentCode = $validatedData['agent_code'];
        $callbackUrl = $validatedData['callback_url'];
        $agent = User::where('shan_agent_code', $agentCode)->first();
        $agent_name = $agent->user_name;
        Log::info('Agent name', ['agent_name' => $agent_name]);

        if (!$agent) {
            Log::error('Provider Launch Game: Agent not found', [
                'agent_code' => $agentCode,
                'member_account' => $memberAccount,
            ]);
            return response()->json([
                'code' => 404,
                'message' => 'Agent not found',
            ], 404);
        }

        Log::info('Provider Launch Game: Agent found', [
            'agent_id' => $agent->id,
            'agent_username' => $agent->user_name,
            'agent_code' => $agentCode,
            'member_account' => $memberAccount,
        ]);

        // Initialize WalletService
        $walletService = new WalletService;

        // If no client user in our db users table, create automatically
        if (! $clientUser) {
            $clientUser = User::create([
                'user_name' => $memberAccount,
                'name' => $memberAccount,
                'password' => Hash::make($memberAccount),
                'type' => UserType::Player->value,
                'status' => 1,
                'is_changed_password' => 1,
                'shan_agent_code' => $agentCode,
                'agent_id' => $agent->id, // Set the agent relationship
                'client_agent_name' => $agent_name,
                'client_agent_id' => $agent->id,
                'shan_callback_url' => $callbackUrl,
            ]);
            Log::info('Created new user for provider launch game', [
                'member_account' => $memberAccount,
                'agent_id' => $agent->id,
                'agent_username' => $agent->user_name,
                'agent_name' => $agent_name,
                'agent_code' => $agentCode,
                'callback_url' => $callbackUrl,
            ]);

            // Deposit initial balance for new user
            $walletService->deposit($clientUser, $requestedBalance, TransactionName::Deposit, [
                'source' => 'provider_launch_game',
                'description' => 'Initial balance for new user',
                'agent_id' => $agent->id,
            ]);

            Log::info('Deposited initial balance for new user', [
                'member_account' => $memberAccount,
                'balance' => $requestedBalance,
                'agent_id' => $agent->id,
            ]);
        } else {
            // For existing user, update agent relationship if needed
            if ($clientUser->agent_id !== $agent->id || $clientUser->client_agent_id !== $agent->id) {
                $clientUser->update([
                    'agent_id' => $agent->id,
                    'shan_agent_code' => $agentCode,
                    'client_agent_name' => $agent_name,
                    'client_agent_id' => $agent->id,
                ]);
                Log::info('Updated agent relationship for existing user', [
                    'member_account' => $memberAccount,
                    'old_agent_id' => $clientUser->agent_id,
                    'new_agent_id' => $agent->id,
                    'client_agent_name' => $agent_name,
                ]);
            }

            // For existing user, update balance if different
            $currentBalance = $clientUser->balanceFloat;
            if ($currentBalance != $requestedBalance) {
                if ($requestedBalance > $currentBalance) {
                    // Deposit additional amount
                    $depositAmount = $requestedBalance - $currentBalance;
                    $walletService->deposit($clientUser, $depositAmount, TransactionName::Deposit, [
                        'source' => 'provider_launch_game',
                        'description' => 'Balance update for existing user',
                        'agent_id' => $agent->id,
                    ]);

                    Log::info('Updated balance for existing user (deposit)', [
                        'member_account' => $memberAccount,
                        'current_balance' => $currentBalance,
                        'requested_balance' => $requestedBalance,
                        'deposit_amount' => $depositAmount,
                        'agent_id' => $agent->id,
                    ]);
                } else {
                    // Withdraw excess amount
                    $withdrawAmount = $currentBalance - $requestedBalance;
                    $walletService->withdraw($clientUser, $withdrawAmount, TransactionName::Withdraw, [
                        'source' => 'provider_launch_game',
                        'description' => 'Balance adjustment for existing user',
                        'agent_id' => $agent->id,
                    ]);

                    Log::info('Updated balance for existing user (withdraw)', [
                        'member_account' => $memberAccount,
                        'current_balance' => $currentBalance,
                        'requested_balance' => $requestedBalance,
                        'withdraw_amount' => $withdrawAmount,
                        'agent_id' => $agent->id,
                    ]);
                }
            }
        }

        // Get updated user balance
        $balance = $clientUser->fresh()->balanceFloat;

        // Build launch game URL with Shan provider configuration
        // $launchGameUrl = sprintf(
        //     'https://ponewine20x.xyz/?user_name=%s&balance=%s&product_code=%s&game_type=%s&agent_code=%s',
        //     urlencode($memberAccount),
        //     $balance,
        //     $validatedData['product_code'],
        //     $validatedData['game_type'],
        //     $validatedData['agent_code']
        // );

        $launchGameUrl = sprintf(
            'https://golden-mm-shan.vercel.app/?user_name=%s&balance=%s',
            urlencode($memberAccount),
            $balance
        );

        //  $launchGameUrl = sprintf(
        //     'https://delight-shankomee.vercel.app/?user_name=%s&balance=%s',
        //     urlencode($memberAccount),
        //     $balance
        // );

        // $launchGameUrl = sprintf(
        //     'https://goldendragon7.pro/?user_name=%s&balance=%s',
        //     urlencode($memberAccount),
        //     $balance
       // https://delight-shankomee.vercel.app/
        // );

        Log::info('Provider Launch Game URL generated', [
            'member_account' => $memberAccount,
            'balance' => $balance,
            'product_code' => $validatedData['product_code'],
            'game_type' => $validatedData['game_type'],
            'launch_game_url' => $launchGameUrl,
        ]);

        // Return the launch game URL to client site
        return response()->json([
            'code' => 200,
            'message' => 'Game launched successfully',
            'url' => $launchGameUrl,
        ]);
    }

    /**
     * Authenticated Launch Game - for authenticated users
     * This endpoint requires authentication and is used by authenticated clients
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function launchGame(Request $request)
    {
        Log::info('Authenticated Launch Game Request', ['request' => $request->all()]);

        try {
            $validatedData = $request->validate([
                'product_code' => 'required|integer',
                'game_type' => 'required|string',
                'member_account' => 'required|string',
                'balance' => 'required|numeric|min:0',
                'nickname' => 'nullable|string',
            ]);

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Use MMK currency for all products
            $apiCurrency = 'MMK';

            // Get or create user from member_account
            $memberAccount = $request->member_account;
            $requestedBalance = $request->balance;
            $clientUser = User::where('user_name', $memberAccount)->first();

            // Get agent information from authenticated user
            $agent = $user;
            $agentCode = $agent->shan_agent_code ?? $agent->user_name;
            $agent_name = $agent->user_name;

            Log::info('Authenticated Launch Game: Agent found', [
                'agent_id' => $agent->id,
                'agent_username' => $agent->user_name,
                'agent_code' => $agentCode,
                'member_account' => $memberAccount,
            ]);

            // Initialize WalletService
            $walletService = new WalletService;

            // If no client user in our db users table, create automatically
            if (!$clientUser) {
                $clientUser = User::create([
                    'user_name' => $memberAccount,
                    'name' => $memberAccount,
                    'password' => Hash::make($memberAccount),
                    'type' => UserType::Player->value,
                    'status' => 1,
                    'is_changed_password' => 1,
                    'shan_agent_code' => $agentCode,
                    'agent_id' => $agent->id,
                    'client_agent_name' => $agent_name,
                    'client_agent_id' => $agent->id,
                ]);

                Log::info('Created new user for authenticated launch game', [
                    'member_account' => $memberAccount,
                    'agent_id' => $agent->id,
                    'agent_username' => $agent->user_name,
                    'agent_name' => $agent_name,
                    'agent_code' => $agentCode,
                ]);

                // Deposit initial balance for new user
                $walletService->deposit($clientUser, $requestedBalance, TransactionName::Deposit, [
                    'source' => 'authenticated_launch_game',
                    'description' => 'Initial balance for new user',
                    'agent_id' => $agent->id,
                ]);

                Log::info('Deposited initial balance for new user', [
                    'member_account' => $memberAccount,
                    'balance' => $requestedBalance,
                    'agent_id' => $agent->id,
                ]);
            } else {
                // For existing user, update agent relationship if needed
                if ($clientUser->agent_id !== $agent->id || $clientUser->client_agent_id !== $agent->id) {
                    $clientUser->update([
                        'agent_id' => $agent->id,
                        'shan_agent_code' => $agentCode,
                        'client_agent_name' => $agent_name,
                        'client_agent_id' => $agent->id,
                    ]);
                    Log::info('Updated agent relationship for existing user', [
                        'member_account' => $memberAccount,
                        'old_agent_id' => $clientUser->agent_id,
                        'new_agent_id' => $agent->id,
                        'client_agent_name' => $agent_name,
                    ]);
                }

                // For existing user, update balance if different
                $currentBalance = $clientUser->balanceFloat;
                if ($currentBalance != $requestedBalance) {
                    if ($requestedBalance > $currentBalance) {
                        // Deposit additional amount
                        $depositAmount = $requestedBalance - $currentBalance;
                        $walletService->deposit($clientUser, $depositAmount, TransactionName::Deposit, [
                            'source' => 'authenticated_launch_game',
                            'description' => 'Balance update for existing user',
                            'agent_id' => $agent->id,
                        ]);

                        Log::info('Updated balance for existing user (deposit)', [
                            'member_account' => $memberAccount,
                            'current_balance' => $currentBalance,
                            'requested_balance' => $requestedBalance,
                            'deposit_amount' => $depositAmount,
                            'agent_id' => $agent->id,
                        ]);
                    } else {
                        // Withdraw excess amount
                        $withdrawAmount = $currentBalance - $requestedBalance;
                        $walletService->withdraw($clientUser, $withdrawAmount, TransactionName::Withdraw, [
                            'source' => 'authenticated_launch_game',
                            'description' => 'Balance adjustment for existing user',
                            'agent_id' => $agent->id,
                        ]);

                        Log::info('Updated balance for existing user (withdraw)', [
                            'member_account' => $memberAccount,
                            'current_balance' => $currentBalance,
                            'requested_balance' => $requestedBalance,
                            'withdraw_amount' => $withdrawAmount,
                            'agent_id' => $agent->id,
                        ]);
                    }
                }
            }

            // Get updated user balance
            $balance = $clientUser->fresh()->balanceFloat;

            // Build launch game URL with Shan provider configuration
            $launchGameUrl = sprintf(
                'https://shan-ko-mee-mm.vercel.app/?user_name=%s&balance=%s',
                urlencode($memberAccount),
                $balance
            );

            Log::info('Authenticated Launch Game URL generated', [
                'member_account' => $memberAccount,
                'balance' => $balance,
                'product_code' => $validatedData['product_code'],
                'game_type' => $validatedData['game_type'],
                'launch_game_url' => $launchGameUrl,
            ]);

            // Return the launch game URL to authenticated client
            return response()->json([
                'code' => 200,
                'message' => 'Game launched successfully',
                'url' => $launchGameUrl,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Authenticated Launch Game Validation Failed', ['errors' => $e->errors()]);

            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Authenticated Launch Game Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'code' => 500,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
