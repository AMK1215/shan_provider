<?php

namespace App\Http\Controllers\Api\V1\DigitGame;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // For generating wager_code

// Models
use App\Models\DigitGame\DigitBet;
use App\Models\User; // Assuming User model is in App\Models
// Make sure to adjust these if your models are in different namespaces
// use App\Models\Product; // If you have a Product model
// use App\Models\GameType; // If you have a GameType model

// Services
use App\Services\WalletService;
use App\Enums\TransactionName; // Assuming you have this Enum
use App\Enums\TransactionType; // Assuming you have this Enum if used directly in WalletService

class DigitBetController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle placing a new digit bet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user(); // Get the authenticated user

        //dd($user->user_name);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Validate incoming request data
        try {
            $validated = $request->validate([
                'bet_type' => ['required', 'string', 'in:small,big,green,yellow,red,digit'],
                'digit' => ['nullable', 'integer', 'min:0', 'max:9', 'required_if:bet_type,digit'], // Required only if bet_type is 'digit'
                'rolled_number' => ['required', 'integer', 'min:0', 'max:9'],
                'outcome' => ['required', 'string', 'in:win,lose'],
                'bet_amount' => ['required', 'numeric', 'min:1'],
                'multiplier' => ['required', 'integer', 'min:1'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('DigitBet validation failed for user ' . $user->id . ':', ['errors' => $e->errors(), 'request_data' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $betAmount = $validated['bet_amount'];
        $multiplier = $validated['multiplier'];
        $totalBet = $betAmount * $multiplier;
        $winAmount = 0;
        $profit = -$totalBet; // Start with negative profit (loss of bet amount)

        // Ensure user has enough balance
        if ($user->balanceFloat < $totalBet) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Please deposit funds to play.'
            ], 400); // Bad Request
        }

        // Use a database transaction to ensure atomicity
        // This is crucial for financial transactions
        DB::beginTransaction();
        try {
            // Deduct bet amount from user's balance
            $this->walletService->withdraw(
                $user,
                $totalBet,
                TransactionName::GAME_BET, // Use your appropriate enum value
                ['game_name' => 'Digit Bet', 'bet_type' => $validated['bet_type'], 'rolled_number' => $validated['rolled_number']]
            );

            // Calculate win_amount and profit based on game logic
            // Assuming fixed odds for different bet types
            if ($validated['outcome'] === 'win') {
                switch ($validated['bet_type']) {
                    case 'digit':
                        // Example: 9x payout for picking the exact digit
                        $payoutMultiplier = 9;
                        break;
                    case 'small':
                    case 'big':
                    case 'green':
                    case 'yellow':
                    case 'red':
                        // Example: 2x payout for 50/50 or color bets
                        $payoutMultiplier = 2;
                        break;
                    default:
                        $payoutMultiplier = 0; // Should not happen due to validation
                        break;
                }
                $winAmount = $totalBet * $payoutMultiplier;
                $profit = $winAmount - $totalBet;

                // Deposit win amount to user's balance
                $this->walletService->deposit(
                    $user,
                    $winAmount,
                    TransactionName::GAME_WIN, // Use your appropriate enum value
                    ['game_name' => 'Digit Bet', 'bet_type' => $validated['bet_type'], 'rolled_number' => $validated['rolled_number']]
                );
            }

            // Create and save the DigitBet record
            $bet = DigitBet::create([
                'user_id' => $user->id,
                'member_account' => $user->user_name ?? $user->name ?? $user->phone ?? 'unknown',

                //'member_account' => $user->user_name, // Adjust if user model has member_account
                'bet_type' => $validated['bet_type'],
                'digit' => $validated['digit'] ?? null,
                'bet_amount' => $betAmount,
                'multiplier' => $multiplier,
                'rolled_number' => $validated['rolled_number'],
                'win_amount' => $winAmount,
                'profit' => $profit,
                'status' => 'Settle', // Mark as settled as outcome is known
                'bet_time' => now(),
                'wager_code' => 'W' . Str::random(12) . time(), // Unique wager code
                'outcome' => $validated['outcome'],
                'game_type_id' => 1, // Placeholder: Get this from your database/config
                'game_name' => 'Digit Bet',
                'game_type' => 'number',
                'game_provider_id' => 'game_provider_id', // Placeholder: Get this from config
                'product_id' => 1, // Placeholder: Get this from your database/config
            ]);

            DB::commit();

            // Refresh user balance after transaction
            $user->refresh();

            // Fetch updated history for the response
            $history = DigitBet::where('user_id', $user->id)
                               ->latest('bet_time')
                               ->limit(10) // Limit to last 10 entries
                               ->get()
                               ->map(function($bet) {
                                   // Format for frontend if needed
                                   return [
                                       'timestamp' => $bet->bet_time->toDateTimeString(),
                                       'betType' => $bet->bet_type,
                                       'digit' => $bet->digit,
                                       'rolledNumber' => $bet->rolled_number,
                                       'outcome' => $bet->outcome,
                                       'bet_amount' => $bet->bet_amount,
                                       'multiplier' => $bet->multiplier,
                                       'win_amount' => $bet->win_amount,
                                       'profit' => $bet->profit,
                                   ];
                               });

            return response()->json([
                'success' => true,
                'message' => 'Bet placed and settled successfully!',
                'data' => [
                    'balance' => $user->balanceFloat,
                    'history' => $history,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DigitBet transaction failed for user ' . $user->id . ':', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during the bet process. Please try again.',
                'error' => $e->getMessage() // For development, remove in production
            ], 500);
        }
    }

    /**
     * Get the bet history for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $history = DigitBet::where('user_id', $user->id)
                           ->latest('bet_time') // Order by latest bets
                           ->limit(10)          // Get last 10 bets
                           ->get()
                           ->map(function($bet) {
                               // Format the data as expected by your React frontend
                               return [
                                   'timestamp' => $bet->bet_time->toDateTimeString(),
                                   'betType' => $bet->bet_type,
                                   'digit' => $bet->digit,
                                   'rolledNumber' => $bet->rolled_number,
                                   'outcome' => $bet->outcome,
                                   'bet_amount' => $bet->bet_amount,
                                   'multiplier' => $bet->multiplier,
                                   'win_amount' => $bet->win_amount,
                                   'profit' => $bet->profit,
                                   // Add other fields if needed by the frontend history table
                               ];
                           });

        return response()->json([
            'success' => true,
            'message' => 'Bet history retrieved successfully.',
            'data' => $history
        ], 200);
    }
}
