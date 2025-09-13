<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShanLaunchGameController extends Controller
{
    public function launch(Request $request)
    {
        // Log incoming request
        Log::info('ShanLaunchGame: Launch request received', [
            'member_account' => $request->member_account,
            'product_code' => $request->product_code,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 1. Validate input (including sign and operator_code)
        $validator = Validator::make($request->all(), [
            'member_account' => 'required|string|max:50',
            'product_code' => 'required|string',

        ]);

        if ($validator->fails()) {
            Log::warning('ShanLaunchGame: Validation failed', [
                'member_account' => $request->member_account,
                'product_code' => $request->product_code,
                'errors' => $validator->errors()->toArray(),
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $member_account = $request->member_account;
        $product_code = $request->product_code;

        Log::info('ShanLaunchGame: Validation passed', [
            'member_account' => $member_account,
            'product_code' => $product_code,
        ]);

        $product = Product::where('product_code', $product_code)->first();
        if (! $product) {
            Log::warning('ShanLaunchGame: Product not found', [
                'member_account' => $member_account,
                'product_code' => $product_code,
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Product not found',
            ], 404);
        }

        Log::info('ShanLaunchGame: Product found', [
            'member_account' => $member_account,
            'product_code' => $product_code,
            'product_id' => $product->id,
        ]);

        // 2. Signature check
        // $secret_key = config('shan.services.shan_key'); // or fetch from DB as needed
        // 2. Load necessary configuration
        $operator_code = Config::get('shan_key.agent_code');
        $secret_key = Config::get('shan_key.secret_key');
        $providerUrl = Config::get('shan_key.api_url'); // Get API URL from config
        $api_currency = Config::get('shan_key.api_currency'); // Get API Currency from config
        $expected_sign = md5($operator_code.$member_account.$secret_key);

        Log::info('ShanLaunchGame: Configuration loaded', [
            'member_account' => $member_account,
            'operator_code' => $operator_code,
            'provider_url' => $providerUrl,
            'api_currency' => $api_currency,
            'expected_sign' => $expected_sign,
        ]);

        // 3. Member must exist
        $user = User::where('user_name', $member_account)->first();
        if (! $user) {
            Log::warning('ShanLaunchGame: Member not found', [
                'member_account' => $member_account,
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Member not found',
            ], 404);
        }

        Log::info('ShanLaunchGame: Member found', [
            'member_account' => $member_account,
            'user_id' => $user->id,
        ]);

        // 4. Call Provider API to get Launch Game URL
        $providerUrl = 'https://ponewine20x.xyz/api/shan/launch-game'; // e.g. 'https://provider-site.com/api/shan/launch-game'

        $requestData = [
            'member_account' => $member_account,
            'operator_code' => $operator_code,
            'sign' => $expected_sign,
        ];

        Log::info('ShanLaunchGame: Calling provider API', [
            'provider_url' => $providerUrl,
            'request_data' => $requestData,
        ]);

        $response = Http::post($providerUrl, $requestData);

        // 5. Pass back provider's response (or parse/modify as needed)
        if ($response->successful()) {
            $responseData = $response->json();

            Log::info('ShanLaunchGame: Provider API successful', [
                'member_account' => $member_account,
                'provider_response' => $responseData,
                'status_code' => $response->status(),
            ]);

            return response()->json($responseData, $response->status());
        } else {
            Log::error('ShanLaunchGame: Provider API failed', [
                'member_account' => $member_account,
                'provider_url' => $providerUrl,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'request_data' => $requestData,
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Provider API error',
                'error_detail' => $response->body(),
            ], $response->status());
        }
    }
}
