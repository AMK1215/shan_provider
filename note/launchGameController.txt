<?php

namespace App\Http\Controllers\Api\V1\Game; // Updated namespace

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // Added for Str::random()
use App\Enums\SeamlessWalletCode;
use App\Models\GameList; // Assuming you have a GameList model
use Carbon\Carbon; // Added for Carbon::now()

class LaunchGameController extends Controller
{
    // Define constants as used in the provided code for the external API payload
    private const LANGUAGE_CODE = 0; // Keeping as 0 as per your provided code, but typically 'en', 'id', etc.
    private const PLATFORM_WEB = 'WEB';
    private const PLATFORM_DESKTOP = 'DESKTOP';
    private const PLATFORM_MOBILE = 'MOBILE';

    /**
     * Generate a secure token for game authentication
     *
     * @param User $user
     * @return string
     */
    private function generateGameToken(User $user): string
    {
        $timestamp = Carbon::now()->timestamp;
        $randomString = Str::random(16);
        $userIdentifier = $user->id;

        // Create a unique token combining user ID, timestamp, and random string
        $tokenBase = $userIdentifier . '|' . $timestamp . '|' . $randomString;

        // Encrypt the token using the application key
        // Ensure APP_KEY is set in your .env file for encryption to work
        $encryptedToken = encrypt($tokenBase);

        // Store the token in cache with expiration (e.g., 1 hour)
        $cacheKey = 'game_token_' . $user->id;
        cache()->put($cacheKey, $encryptedToken, Carbon::now()->addHour());

        return $encryptedToken;
    }

    /**
     * Verify if a game token is valid
     *
     * @param string $token
     * @param User $user
     * @return bool
     */
    private function verifyGameToken(string $token, User $user): bool
    {
        $cacheKey = 'game_token_' . $user->id;
        $storedToken = cache()->get($cacheKey);

        if (!$storedToken || $storedToken !== $token) {
            return false;
        }

        // Optionally, decrypt and check token components for added security
        try {
            $decryptedTokenBase = decrypt($token);
            list($userId, $timestamp, $randomString) = explode('|', $decryptedTokenBase);
            if ((int)$userId !== $user->id) {
                return false;
            }
            // You might add a timestamp check here to ensure the token isn't too old
            // e.g., if (Carbon::createFromTimestamp($timestamp)->addMinutes(5)->isPast()) { return false; }
        } catch (\Exception $e) {
            Log::error('Failed to decrypt or parse game token', ['token' => $token, 'error' => $e->getMessage()]);
            return false;
        }

        return true;
    }

    /**
     * Handles the game launch request.
     * This method validates the incoming request, authenticates the user,
     * generates a signature, constructs a payload, and makes an HTTP call
     * to an external game provider's launch API.
     *
     * @param Request $request The incoming HTTP request containing game launch details.
     * @return \Illuminate\Http\JsonResponse
     */
    public function launchGame(Request $request)
    {
        Log::info('Launch Game API Request', ['request' => $request->all()]);

        // Authenticate the user. This assumes your route is protected by 'auth:api' middleware
        // or a similar authentication mechanism where Auth::user() is available.
        $user = Auth::user();
        if (!$user) {
            Log::warning('Unauthenticated user attempting game launch.');
            return ApiResponseService::error(
                SeamlessWalletCode::MemberNotExist, // Use MemberNotExist or a more specific auth error code
                'Authentication required. Please log in.'
            );
        }

        // Validate the incoming request data from your frontend/client
        try {
            $validatedData = $request->validate([
                'game_code' => 'required|string',
                'product_code' => 'required|integer',
                'game_type' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Launch Game API Validation Failed', ['errors' => $e->errors()]);
            return ApiResponseService::error(
                SeamlessWalletCode::InternalServerError, // Use a generic error code for validation
                'Validation failed',
                $e->errors()
            );
        }

        // Use config values for agent_code, secret_key, api_url, and api_currency
        // Assuming 'seamless_key' config file holds these values for the external provider API
        $agentCode = config('seamless_key.agent_code');
        $secretKey = config('seamless_key.secret_key');
        $apiUrl = config('seamless_key.api_url') . '/api/operators/launch-game'; // External game provider's launch endpoint
        $apiCurrency = config('seamless_key.api_currency'); // Assuming this exists in seamless_key config
        $operatorLobbyUrl = 'https://amk-movies-five.vercel.app'; // Hardcoded lobby URL

        // Set request_time to now() in GMT+8 (Asia/Shanghai) as integer timestamp (seconds)
        $nowGmt8 = now('Asia/Shanghai'); // GMT+8
        $requestTime = $nowGmt8->timestamp; // integer seconds

        // Generate signature for the request to the external provider
        // Formula: md5(request_time + secret_key + "launchgame" + agent_code)
        $generatedSignature = md5(
            $requestTime . $secretKey . 'launchgame' . $agentCode
        );

        // Generate a secure token for this game session
        $gameToken = $this->generateGameToken($user);
        $game_code = null;
        $password = "abcd1234";

        
        $payload = [
            'operator_code' => $agentCode,
            'member_account' => $user->user_name,
           // 'password' => $gameToken, // Using secure token instead of actual password
           'password' => $password,
           'nickname' => $request->input('nickname') ?? $user->name, // Access nickname directly from request or fallback
            'currency' => $apiCurrency,
            'game_code' => $validatedData['game_code'],
            'product_code' => $validatedData['product_code'],
            'game_type' => $validatedData['game_type'],
            'language_code' => self::LANGUAGE_CODE,
            'ip' => $request->ip(),
            'platform' => self::PLATFORM_WEB, // Using WEB platform constant
            'sign' => $generatedSignature, // Use the generated signature for the external API call
            'request_time' => $requestTime,
            'operator_lobby_url' => $operatorLobbyUrl,
        ];

        // Log the payload being sent to the external API for debugging
        Log::info('Sending Launch Game Request to Provider', ['url' => $apiUrl, 'payload' => $payload]);

        try {
            // Send the POST request to the external game launch API
            $response = \Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($apiUrl, $payload);

            // Check if the external API request was successful (HTTP 2xx status code)
            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Provider Launch Game API Response', ['response' => $responseData]);

                // Return a structured JSON response to your client
                // Ensure the keys ('code', 'message', 'url', 'content') match what your frontend expects
                return response()->json([
                    'code'    => $responseData['code'] ?? SeamlessWalletCode::InternalServerError->value,
                    'message' => $responseData['message'] ?? 'Game launched successfully',
                    'url'     => $responseData['url'] ?? '', // Expecting the game URL from the provider
                    'content' => $responseData['content'] ?? '', // Any additional content from provider
                ]);
            }

            // If the external API request failed, log the error and return a detailed JSON response
            Log::error('Provider Launch Game API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'request_payload' => $payload
            ]);
            return response()->json(
                ['code' => $response->status(), 'message' => 'Provider API request failed', 'url' => '', 'content' => $response->body()],
                $response->status()
            );
        } catch (\Throwable $e) {
            // Catch any unexpected exceptions that might occur during the HTTP request (e.g., network issues)
            Log::error('Unexpected error during provider API call', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_payload' => $payload
            ]);
            return response()->json(
                ['code' => 500, 'message' => 'Unexpected error', 'url' => '', 'content' => $e->getMessage()],
                500
            );
        }
    }
}
