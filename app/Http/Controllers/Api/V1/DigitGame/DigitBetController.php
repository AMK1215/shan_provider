<?php

namespace App\Http\Controllers\Api\V1\DigitGame;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\DigitGame\DigitBet;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request; // For generating wager_code
// Models
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Assuming User model is in App\Models
// Services
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Assuming you have this Enum

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        try {
            $validated = $request->validate([
                'bet_type' => ['required', 'string', 'in:small,big,green,yellow,red,digit'],
                'digit' => ['nullable', 'integer', 'min:0', 'max:9', 'required_if:bet_type,digit'],
                'rolled_number' => ['required', 'integer', 'min:0', 'max:9'],
                'outcome' => ['required', 'string', 'in:win,lose'],
                'bet_amount' => ['required', 'numeric', 'min:1'],
                'multiplier' => ['required', 'integer', 'min:1'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('DigitBet validation failed for user '.$user->id.':', ['errors' => $e->errors(), 'request_data' => $request->all()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $betAmount = $validated['bet_amount'];
        $multiplier = $validated['multiplier'];
        $rolledNumber = $validated['rolled_number'];
        $totalBet = $betAmount * $multiplier; // Total amount risked by the player
        $winAmount = 0;
        $profit = -$totalBet; // Default profit is a loss of totalBet

        // Ensure user has enough balance
        if ($user->balanceFloat < $totalBet) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Please deposit funds to play.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Deduct bet amount from user's balance
            $this->walletService->withdraw(
                $user,
                $totalBet,
                TransactionName::GAME_BET,
                ['game_name' => 'Digit Bet', 'bet_type' => $validated['bet_type'], 'rolled_number' => $rolledNumber]
            );

            // Calculate win_amount and profit based on game logic
            if ($validated['outcome'] === 'win') {
                $payoutFactor = 0; // Initialize payout factor

                switch ($validated['bet_type']) {
                    case 'digit':
                        $selectedDigit = $validated['digit'];
                        // Special case: If player bet on '5' AND the rolled number is '5'
                        if ($selectedDigit === 5 && $rolledNumber === 5) {
                            $payoutFactor = 10; // Payout bet amount * 10
                        } else {
                            $payoutFactor = 5; // Payout bet amount * 5 for other exact digits
                        }
                        break;
                    case 'small':
                    case 'big':
                        // Payout is bet amount + bet amount (i.e., 2x the bet amount)
                        $payoutFactor = 2;
                        break;
                    case 'green': // Assuming these also use a 2x payout if they are simpler 50/50 style bets
                    case 'yellow':
                    case 'red':
                        // You might need to confirm the exact payout rules for colors.
                        // For now, assuming 2x as per 'small'/'big' if not specified otherwise.
                        $payoutFactor = 2;
                        break;
                    default:
                        $payoutFactor = 0; // Should not be reached due to validation
                        break;
                }

                $winAmount = $totalBet * $payoutFactor;
                $profit = $winAmount - $totalBet; // Profit is winnings minus original total bet

                // Deposit win amount to user's balance
                $this->walletService->deposit(
                    $user,
                    $winAmount,
                    TransactionName::GAME_WIN,
                    ['game_name' => 'Digit Bet', 'bet_type' => $validated['bet_type'], 'rolled_number' => $rolledNumber, 'winnings' => $winAmount]
                );
            }

            // Create and save the DigitBet record
            $bet = DigitBet::create([
                'user_id' => $user->id,
                // Fallback for member_account: try user_name, then name, then phone, else 'unknown'
                'member_account' => $user->user_name ?? $user->name ?? $user->phone ?? 'unknown',
                'bet_type' => $validated['bet_type'],
                'digit' => $validated['digit'] ?? null,
                'bet_amount' => $betAmount,
                'multiplier' => $multiplier,
                'rolled_number' => $rolledNumber,
                'win_amount' => $winAmount,
                'profit' => $profit,
                'status' => 'Settle',
                'bet_time' => now(),
                'wager_code' => 'W'.Str::random(12).time(),
                'outcome' => $validated['outcome'],
                'game_type_id' => 1, // Placeholder: Update with actual game_type_id from your DB/config
                'game_name' => 'Digit Bet',
                'game_type' => 'number',
                'game_provider_id' => 'game_provider_id', // Placeholder: Update with actual game_provider_id from your DB/config
                'product_id' => 1, // Placeholder: Update with actual product_id from your DB/config
            ]);

            DB::commit();

            // Refresh user balance after transaction
            $user->refresh();

            // Fetch updated history for the response
            $history = DigitBet::where('user_id', $user->id)
                ->latest('bet_time')
                ->limit(10)
                ->get()
                ->map(function ($bet) {
                    return [
                        'timestamp' => $bet->bet_time->toDateTimeString(),
                        'betType' => $bet->bet_type,
                        'digit' => $bet->digit,
                        'rolledNumber' => $bet->rolled_number,
                        'outcome' => $bet->outcome,
                        'bet_amount' => (float) $bet->bet_amount, // Cast to float for frontend if needed
                        'multiplier' => $bet->multiplier,
                        'win_amount' => (float) $bet->win_amount,
                        'profit' => (float) $bet->profit,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Bet placed and settled successfully!',
                'data' => [
                    'balance' => $user->balanceFloat,
                    'history' => $history,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DigitBet transaction failed for user '.$user->id.':', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during the bet process. Please try again.',
                'error' => $e->getMessage(), // Keep for development, remove in production
            ], 500);
        }
    }

    /**
     * Get the bet history for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $history = DigitBet::where('user_id', $user->id)
            ->latest('bet_time')
            ->limit(10)
            ->get()
            ->map(function ($bet) {
                return [
                    'timestamp' => $bet->bet_time->toDateTimeString(),
                    'betType' => $bet->bet_type,
                    'digit' => $bet->digit,
                    'rolledNumber' => $bet->rolled_number,
                    'outcome' => $bet->outcome,
                    'bet_amount' => (float) $bet->bet_amount,
                    'multiplier' => $bet->multiplier,
                    'win_amount' => (float) $bet->win_amount,
                    'profit' => (float) $bet->profit,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Bet history retrieved successfully.',
            'data' => $history,
        ], 200);
    }
}
