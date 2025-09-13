<?php

namespace App\Http\Controllers\Api\V2\Shan;

use App\Enums\ShankomeeCode;
use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShankomeeGetBalanceController extends Controller
{
    public function shangetbalance(Request $request)
    {
        Log::info('Shankomee GetBalance: Request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $request->all(),
        ]);

        // 1. Validate input structure
        $validator = Validator::make($request->all(), [
            'batch_requests' => 'required|array|min:1',
            'batch_requests.*.member_account' => 'required|string|max:50',
            'batch_requests.*.product_code' => 'required|integer',
            'batch_requests.*.balance' => 'required|numeric', // balance comes from external!
            'operator_code' => 'required|string',
            'currency' => 'required|string',
            'request_time' => 'required|integer',
            'sign' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Shankomee GetBalance: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        Log::info('Shankomee GetBalance: Validation passed');

        // 2. Check operator_code in database
        $operator = Operator::where('code', $request->operator_code)
            ->where('active', true)
            ->first();

        Log::info('Shankomee GetBalance: Operator found', [
            'operator_code' => $request->operator_code,
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'operator_callback_url' => $operator->callback_url,
        ]);

        if (! $operator) {
            Log::warning('Shankomee GetBalance: Invalid operator_code', [
                'operator_code' => $request->operator_code,
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid or inactive operator_code',
            ], 403);
        }

        Log::info('Shankomee GetBalance: Operator found', [
            'operator_code' => $request->operator_code,
            'operator_id' => $operator->id,
        ]);

        // 3. Signature check using operator's secret_key from DB
        $secret_key = $operator->secret_key;
        $expectedSign = md5($request->operator_code.$request->request_time.'getbalance'.$secret_key);

        Log::info('Shankomee GetBalance: Signature verification', [
            'provided_sign' => $request->sign,
            'expected_sign' => $expectedSign,
            'operator_code' => $request->operator_code,
            'request_time' => $request->request_time,
        ]);

        if ($request->sign !== $expectedSign) {
            Log::warning('Shankomee GetBalance: Invalid signature', [
                'provided_sign' => $request->sign,
                'expected_sign' => $expectedSign,
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Signature invalid',
            ], 403);
        }

        Log::info('Shankomee GetBalance: Signature verification passed');

        // 4. Validate product_codes from DB
        $allowed_product_codes = Product::where('status', 'ACTIVATED')
            ->where('game_list_status', true)
            ->pluck('product_code')
            ->toArray();

        Log::info('Shankomee GetBalance: Allowed product codes', [
            'allowed_codes' => $allowed_product_codes,
        ]);

        $callbackUrl = $operator->callback_url ?? 'https://ponewine20x.xyz/api/shan/balance';

        $results = [];
        foreach ($request->batch_requests as $item) {
            // Check if member_account exists in users table
            $user = User::where('user_name', $item['member_account'])->first();

            if (! $user) {
                // User does not exist in our database
                Log::warning('Shankomee callback: User not found', [
                    'member_account' => $item['member_account'],
                    'callback_payload' => $item,
                ]);
                $balance = null;
                $status = 'user_not_found';
            } else {
                // User exists, get balance from database
                $databaseBalance = $user->balanceFloat ?? 0;
                $callbackBalance = $item['balance'] ?? 0;

                Log::info('Shankomee callback: Balance comparison', [
                    'member_account' => $item['member_account'],
                    'database_balance' => $databaseBalance,
                    'callback_balance' => $callbackBalance,
                    'difference' => $databaseBalance - $callbackBalance,
                ]);

                if ($databaseBalance < $callbackBalance) {
                    // Database balance is less than callback balance - potential issue
                    Log::warning('Shankomee callback: Database balance less than callback', [
                        'member_account' => $item['member_account'],
                        'database_balance' => $databaseBalance,
                        'callback_balance' => $callbackBalance,
                    ]);
                    $status = 'insufficient_balance';
                    $balance = $databaseBalance;
                } else {
                    // Database balance is greater than or equal to callback balance - acceptable
                    Log::info('Shankomee callback: Balance check passed', [
                        'member_account' => $item['member_account'],
                        'database_balance' => $databaseBalance,
                        'callback_balance' => $callbackBalance,
                    ]);
                    $status = 'success';
                    $balance = $databaseBalance;
                }
            }

            $results[] = [
                'member_account' => $item['member_account'],
                'product_code' => $item['product_code'],
                'balance' => $balance,
                'currency' => $request->currency,
                'status' => $status,
            ];
        }

        Log::info('Shankomee GetBalance: Response sent', [
            'results' => $results,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $results,
        ]);

    }

    public function LaunchGame(Request $request)
    {
        Log::info('Shankomee LaunchGame: Request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $request->all(),
        ]);

        // Validate input
        $validator = Validator::make($request->all(), [
            'member_account' => 'required|string|max:50',
            'operator_code' => 'required|string', // if you want to validate operator as well
        ]);

        if ($validator->fails()) {
            Log::warning('Shankomee LaunchGame: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        Log::info('Shankomee LaunchGame: Validation passed');

        $operator_code = $request->operator_code;
        $member_account = $request->member_account;
        $currency = 'MMK';
        $request_time = time();

        // Get operator from database to use its secret_key
        $operator = Operator::where('code', $operator_code)
            ->where('active', true)
            ->first();

        if (! $operator) {
            Log::warning('Shankomee LaunchGame: Invalid operator_code', [
                'operator_code' => $operator_code,
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid or inactive operator_code',
            ], 403);
        }

        $secret_key = $operator->secret_key;
        $sign = md5($operator_code.$request_time.'getbalance'.$secret_key);

        Log::info('Shankomee LaunchGame: Signature generation', [
            'operator_code' => $operator_code,
            'request_time' => $request_time,
            'secret_key' => $secret_key,
            'generated_sign' => $sign,
        ]);

        // Get user balance from database first
        $user = User::where('user_name', $member_account)->first();
        $userBalance = $user ? ($user->balanceFloat ?? 0) : 0;

        // Prepare GetBalance API request payload
        $getBalancePayload = [
            'batch_requests' => [
                [
                    'member_account' => $member_account,
                    'product_code' => 100100, // Use the same product_code as in the example
                    'balance' => $userBalance, // Add the balance field
                ],
            ],
            'operator_code' => $operator_code,
            'currency' => $currency,
            'request_time' => $request_time,
            'sign' => $sign,
        ];

        Log::info('Shankomee LaunchGame: Internal API call payload', [
            'member_account' => $member_account,
            'payload' => $getBalancePayload,
            'user_balance' => $userBalance,
        ]);

        // Call your own GetBalance API (internal call)
        $getBalanceApiUrl = url('/api/provider/shan/ShanGetBalances'); // Use relative URL for internal call

        $response = Http::post($getBalanceApiUrl, $getBalancePayload);

        Log::info('Shankomee LaunchGame: Internal API response', [
            'url' => $getBalanceApiUrl,
            'status_code' => $response->status(),
            'response_body' => $response->body(),
        ]);

        if (! $response->successful()) {
            Log::error('Failed to get balance from internal API', [
                'url' => $getBalanceApiUrl,
                'payload' => $getBalancePayload,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Unable to get balance',
            ], 500);
        }

        $resultData = $response->json();
        $balance = 0;

        if (isset($resultData['data'][0]['balance'])) {
            $balance = $resultData['data'][0]['balance'];
        }

        // Check if member exists in users table, create if not
        $member = User::where('user_name', $member_account)->first();
        if (! $member) {
            $member = User::create([
                'user_name' => $member_account,
                'name' => $member_account,
                'type' => '40',
                'status' => 1,
                'is_changed_password' => 1,
            ]);
        }

        // Build launch game URL
        $launchGameUrl = 'https://delight-myanmar-shan-ko-mee.vercel.app/?user_name='.urlencode($member_account).'&balance='.$balance;

        Log::info('Shankomee LaunchGame: Response sent', [
            'member_account' => $member_account,
            'balance' => $balance,
            'launch_game_url' => $launchGameUrl,
        ]);

        return response()->json([
            'status' => 'success',
            'launch_game_url' => $launchGameUrl,
        ]);
    }
}
