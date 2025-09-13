<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Helpers\InternalApiHelper;
use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShanLaunchGameController extends Controller
{
    public function launch(Request $request)
    {
        // 1. Validate input
        $validator = Validator::make($request->all(), [
            'member_account' => 'required|string|max:50',
            'operator_code' => 'required|string',
        ]);
        if ($validator->fails()) {
            Log::warning('ShanLaunchGameController: Validation failed', ['errors' => $validator->errors()]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $player = Auth::user();
        Log::info('ShanLaunchGameController: Player', ['player' => $player]);
        if (! $player) {
            Log::warning('ShanLaunchGameController: No authenticated user');

            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized',
            ], 401);
        }

        $memberAccount = $request->input('member_account');
        $operatorCode = $request->input('operator_code');

        // 2. Lookup operator (for callback_url and secret_key)
        $operator = Operator::where('code', $operatorCode)
            ->where('active', true)
            ->first();
        if (! $operator) {
            Log::warning('ShanLaunchGameController: Invalid operator code', ['operator_code' => $operatorCode]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid operator code',
            ], 403);
        }

        // 3. Get player balance (use WalletService if needed)
        $balance = $player->balanceFloat;

        // 4. Build launch game URL
        $launchGameUrl = sprintf(
            'https://goldendragon7.pro/?user_name=%s&balance=%s',
            urlencode($memberAccount),
            $balance
        );

        Log::info('ShanLaunchGameController: Launch URL generated', [
            'user_id' => $player->id,
            'member_account' => $memberAccount,
            'operator_code' => $operatorCode,
            'launch_game_url' => $launchGameUrl,
        ]);

        return response()->json([
            'status' => 'success',
            'launch_game_url' => $launchGameUrl,
        ]);
    }
}
