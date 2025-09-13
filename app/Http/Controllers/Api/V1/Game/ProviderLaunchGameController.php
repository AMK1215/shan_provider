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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProviderLaunchGameController extends Controller
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

            $requestTime = now('Asia/Shanghai')->timestamp;
            Log::info('RequestTime', ['Provider Request Time' => $requestTime]);

        try {
            $validatedData = $request->validate([
                'agent_code' => 'required|string',
                'product_code' => 'required|integer',
                'game_type' => 'required|string',
                'member_account' => 'required|string',
                'balance' => 'required|numeric|min:0',
                'request_time' => 'required|integer',
                'sign' => 'required|string',
                'nickname' => 'nullable|string',
                'callback_url' => 'nullable|string',
            ]);

            // Verify signature first
            if (!$this->verifySignature($request)) {
                Log::warning('Invalid signature for provider launch game request', [
                    'agent_code' => $request->agent_code,
                    'member_account' => $request->member_account,
                    'received_sign' => $request->sign,
                    'expected_sign' => $this->generateExpectedSign($request),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'code' => 401,
                    'message' => 'Invalid signature',
                ], 401);
            }

            Log::info('Signature verification passed for provider launch game');

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
        Log::info('Agent code', ['agent_code' => $agentCode]);
        Log::info('Callback url', ['callback_url' => $callbackUrl]);
        Log::info('Member account', ['member_account' => $memberAccount]);
        Log::info('Requested balance', ['requested_balance' => $requestedBalance]);
        Log::info('Client user', ['client_user' => $clientUser]);
        
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

        // Initialize WalletService start
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
                'shan_secret_key' => $agent->shan_secret_key,
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
        $launchGameUrl = sprintf(
            'https://use.shweshankoemee.site/?user_name=%s&balance=%s',
            urlencode($memberAccount),
            $balance
        );

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
     * Verify the signature of the incoming request.
     */
    private function verifySignature(Request $request): bool
    {
        $expectedSign = $this->generateExpectedSign($request);

        // Use hash_equals to prevent timing attacks when comparing hashes
        return hash_equals($expectedSign, $request->sign);
    }

    /**
     * Generate the expected signature for verification.
     * Following the pattern: md5(request_time + secret_key + 'launchgame' + agent_code)
     */
    private function generateExpectedSign(Request $request): string
    {
        // Get the secret key for the agent
        $agentCode = $request->agent_code;
        $agent = User::where('shan_agent_code', $agentCode)->first();
        
        if (!$agent) {
            Log::error('Agent not found for signature verification', ['agent_code' => $agentCode]);
            return '';
        }

        // Get the secret key from the agent's configuration or use a default
        // You might need to adjust this based on how you store secret keys for agents
        //$secretKey = config('shan_key.secret_key'); // or get from agent's config

        $secretKey = $agent->shan_secret_key;

        // Check if secret key is null or empty
        if (empty($secretKey)) {
            Log::error('Agent secret key is null or empty', [
                'agent_code' => $agentCode,
                'agent_id' => $agent->id,
                'agent_username' => $agent->user_name,
            ]);
            return '';
        }

        Log::info('Secret key', ['provider site agent secret_key' => $secretKey]);
        
        // Generate signature following the same pattern as client: md5(request_time + secret_key + 'launchgame' + agent_code)
        $signString = $request->request_time . $secretKey . 'launchgame' . $agentCode;
        $expectedSign = md5($signString);

        // Log the components used for signature generation (masking the full secret key)
        Log::debug('Generating signature for verification', [
            'components' => [
                'request_time' => $request->request_time,
                'secret_key' => '***'.substr($secretKey, -4), // Log only last 4 chars for security
                'action' => 'launchgame',
                'agent_code' => $agentCode,
            ],
            'full_string' => $request->request_time.'***'.substr($secretKey, -4).'launchgame'.$agentCode,
            'md5_result' => $expectedSign,
        ]);

        return $expectedSign;
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
                    'shan_secret_key' => $agent->shan_secret_key,
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
