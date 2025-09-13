<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReportTransaction;
use App\Models\User;
use App\Services\WalletService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; // Import for specific exception handling

class ProviderTransactionCallbackController extends Controller
{
    use HttpResponses; // Assumes this trait provides success() and error() methods

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle incoming provider transaction callbacks.
     */
    // public function __invoke(Request $request) // Using __invoke for single action controller
    public function handle(Request $request)
    {
        try {
            $this->validateTransactionKey($request);
            $validatedData = $this->validateRequest($request);

            Log::info('Provider callback: Received transaction', ['data' => $validatedData]);

            DB::beginTransaction();

            $player = $this->findPlayer($validatedData['player_id']);
            $this->processWalletUpdate($player, $validatedData);
            $this->createReportTransaction($player, $validatedData);

            DB::commit();

            return $this->success([
                'player_id' => $player->user_name,
                'balance' => $player->balanceFloat,
            ], 'Callback processed successfully.');

        } catch (ValidationException $e) {
            Log::warning('Provider callback: Validation failed', ['errors' => $e->errors(), 'request' => $request->all()]);

            return $this->error('', $e->getMessage(), 422);
        } catch (InsufficientFunds $e) { // Catch specific insufficient funds exception
            DB::rollBack(); // Ensure transaction is rolled back
            Log::warning('Provider callback: Insufficient funds for transaction', [
                'player_id' => $validatedData['player_id'] ?? 'N/A',
                'amount_changed' => $validatedData['amount_changed'] ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Log trace for debugging
                'request' => $request->all(),
            ]);

            // Return a specific error code/message suitable for the game provider
            // A 400 Bad Request is common for client-side errors like insufficient funds.
            return $this->error('', 'Insufficient player funds for this transaction.', 400);
        } catch (\Exception $e) { // Catch other general exceptions
            DB::rollBack();
            Log::error('Provider callback: Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error('', 'Failed to process callback due to internal error.', 500);
        }
    }

    /**
     * Validate the provider transaction key.
     *
     * @throws \Exception If the key is invalid.
     */
    protected function validateTransactionKey(Request $request): void
    {
        $providedKey = $request->header('X-Provider-Transaction-Key');
        $expectedKey = config('shan_key.transaction_key');

        if ($providedKey !== $expectedKey) {
            Log::warning('Provider callback: Invalid transaction key', ['provided' => $providedKey]);
            throw new \Exception('Unauthorized. Invalid transaction key.');
        }
    }

    /**
     * Validate the incoming request data.
     *
     * @throws ValidationException If validation fails.
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'player_id' => 'required|string',
            'bet_amount' => 'required|numeric',
            'amount_changed' => 'required|numeric',
            'win_lose_status' => 'required|integer|in:0,1',
            'game_type_id' => 'required|integer',
        ]);
    }

    /**
     * Find the user by player ID.
     *
     * @throws \Exception If the player is not found.
     */

    /**
     * Find the user by player ID.
     *
     * @throws PlayerNotFoundException If the player is not found.
     */
    protected function findPlayer(string $playerId): User
    {
        $player = User::where('user_name', $playerId)->first();
        if (! $player) {
            Log::warning('Provider callback: Player not found', ['player_id' => $playerId]);
            throw new PlayerNotFoundException('Player not found.'); // Throw custom exception
        }

        return $player;
    }

    /**
     * Process the wallet balance update based on win/lose status.
     */
    protected function processWalletUpdate(User $player, array $validatedData): void
    {
        $transactionMeta = [
            'provider_callback' => true,
            'game_type_id' => $validatedData['game_type_id'],
            'bet_amount' => $validatedData['bet_amount'],
        ];

        if ($validatedData['win_lose_status'] == 1) { // Win
            $this->walletService->deposit(
                $player,
                $validatedData['amount_changed'],
                TransactionName::Win,
                $transactionMeta
            );
        } else { // Loss (status 0)
            $this->walletService->withdraw(
                $player,
                $validatedData['amount_changed'],
                TransactionName::Loss,
                $transactionMeta
            );
        }
    }

    /**
     * Create a report transaction record.
     */
    protected function createReportTransaction(User $player, array $validatedData): void
    {
        ReportTransaction::create([
            'user_id' => $player->id,
            'game_type_id' => $validatedData['game_type_id'],
            'transaction_amount' => $validatedData['amount_changed'],
            'status' => $validatedData['win_lose_status'],
            'bet_amount' => $validatedData['bet_amount'],
            'valid_amount' => $validatedData['bet_amount'],
        ]);
    }
    // public function handle(Request $request)
    // {
    //     try {
    //         $this->validateTransactionKey($request);
    //         $validatedData = $this->validateRequest($request);

    //         Log::info('Provider callback: Received transaction', ['data' => $validatedData]);

    //         DB::beginTransaction();

    //         $player = $this->findPlayer($validatedData['player_id']);
    //         $this->processWalletUpdate($player, $validatedData);
    //         $this->createReportTransaction($player, $validatedData);

    //         DB::commit();

    //         return $this->success([
    //             'player_id' => $player->user_name,
    //             'balance' => $player->balanceFloat,
    //         ], 'Callback processed successfully.');

    //     } catch (ValidationException $e) {
    //         Log::warning('Provider callback: Validation failed', ['errors' => $e->errors(), 'request' => $request->all()]);
    //         return $this->error('', $e->getMessage(), 422); // 422 Unprocessable Entity for validation errors
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Provider callback: Exception occurred', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'request' => $request->all(),
    //         ]);
    //         return $this->error('', 'Failed to process callback due to internal error.', 500);
    //     }
    // }

    /**
     * Validate the provider transaction key.
     *
     * @throws \Exception If the key is invalid.
     */
    // protected function validateTransactionKey(Request $request): void
    // {
    //     $providedKey = $request->header('X-Provider-Transaction-Key');
    //     $expectedKey = config('shan_key.transaction_key');

    //     if ($providedKey !== $expectedKey) {
    //         Log::warning('Provider callback: Invalid transaction key', ['provided' => $providedKey]);
    //         throw new \Exception('Unauthorized. Invalid transaction key.'); // Throwing an exception to be caught by the main try-catch
    //     }
    // }

    /**
     * Validate the incoming request data.
     *
     * @throws ValidationException If validation fails.
     */
    // protected function validateRequest(Request $request): array
    // {
    //     return $request->validate([
    //         'player_id' => 'required|string',
    //         'bet_amount' => 'required|numeric',
    //         'amount_changed' => 'required|numeric',
    //         'win_lose_status' => 'required|integer|in:0,1',
    //         'game_type_id' => 'required|integer',
    //     ]);
    // }

    /**
     * Find the user by player ID.
     *
     * @throws \Exception If the player is not found.
     */
    // protected function findPlayer(string $playerId): User
    // {
    //     $player = User::where('user_name', $playerId)->first();
    //     if (!$player) {
    //         Log::warning('Provider callback: Player not found', ['player_id' => $playerId]);
    //         throw new \Exception('Player not found.');
    //     }
    //     return $player;
    // }

    /**
     * Process the wallet balance update based on win/lose status.
     */
    // protected function processWalletUpdate(User $player, array $validatedData): void
    // {
    //     $transactionMeta = [
    //         'provider_callback' => true,
    //         'game_type_id' => $validatedData['game_type_id'],
    //         'bet_amount' => $validatedData['bet_amount'], // Added bet_amount to meta for more context
    //     ];

    //     if ($validatedData['win_lose_status'] == 1) { // Win
    //         $this->walletService->deposit(
    //             $player,
    //             $validatedData['amount_changed'],
    //             TransactionName::Win,
    //             $transactionMeta
    //         );
    //     } else { // Loss (status 0)
    //         // Consider if amount_changed for loss should always be positive,
    //         // or if it could be negative from the provider.
    //         // Assuming it's always a positive amount to be withdrawn.
    //         $this->walletService->withdraw(
    //             $player,
    //             $validatedData['amount_changed'],
    //             TransactionName::Loss,
    //             $transactionMeta
    //         );
    //     }
    // }

    /**
     * Create a report transaction record.
     */
    // protected function createReportTransaction(User $player, array $validatedData): void
    // {
    //     ReportTransaction::create([
    //         'user_id' => $player->id,
    //         'game_type_id' => $validatedData['game_type_id'],
    //         'transaction_amount' => $validatedData['amount_changed'],
    //         'status' => $validatedData['win_lose_status'],
    //         'bet_amount' => $validatedData['bet_amount'],
    //         'valid_amount' => $validatedData['bet_amount'], // Assuming valid_amount is also bet_amount
    //     ]);
    // }
}

// namespace App\Http\Controllers\Api\V1\Game;

// use App\Enums\TransactionName;
// use App\Http\Controllers\Controller;
// use App\Models\Admin\ReportTransaction;
// use App\Models\User;
// use App\Services\WalletService;
// use App\Traits\HttpResponses;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;

// class ProviderTransactionCallbackController extends Controller
// {
//     use HttpResponses;

//     public function handle(Request $request, WalletService $walletService)
//     {
//         // 1. Check Provider Key
//         $providedKey = $request->header('X-Provider-Transaction-Key');
//         $expectedKey = config('shan_key.transaction_key');
//         if ($providedKey !== $expectedKey) {
//             Log::warning('Provider callback: Invalid transaction key', ['provided' => $providedKey]);

//             return $this->error('', 'Unauthorized. Invalid transaction key.', 401);
//         }

//         // 2. Validate input
//         $validated = $request->validate([
//             'player_id' => 'required|string',
//             'bet_amount' => 'required|numeric',
//             'amount_changed' => 'required|numeric',
//             'win_lose_status' => 'required|integer|in:0,1',
//             'game_type_id' => 'required|integer',
//         ]);

//         Log::info('Provider callback: Received transaction', ['data' => $validated]);

//         DB::beginTransaction();
//         try {
//             // 3. Find player (DO NOT auto-create)
//             $player = User::where('user_name', $validated['player_id'])->first();
//             if (! $player) {
//                 Log::warning('Provider callback: Player not found', ['player_id' => $validated['player_id']]);

//                 return $this->error('', 'Player not found', 404);
//             }

//             // 4. Update balance via WalletService
//             if ($validated['win_lose_status'] == 1) {
//                 $walletService->deposit(
//                     $player,
//                     $validated['amount_changed'],
//                     TransactionName::Win,
//                     [
//                         'provider_callback' => true,
//                         'game_type_id' => $validated['game_type_id'],
//                     ]
//                 );
//             } else {
//                 $walletService->withdraw(
//                     $player,
//                     $validated['amount_changed'],
//                     TransactionName::Loss,
//                     [
//                         'provider_callback' => true,
//                         'game_type_id' => $validated['game_type_id'],
//                     ]
//                 );
//             }

//             // 5. Store transaction record
//             ReportTransaction::create([
//                 'user_id' => $player->id,
//                 'game_type_id' => $validated['game_type_id'],
//                 'transaction_amount' => $validated['amount_changed'],
//                 'status' => $validated['win_lose_status'],
//                 'bet_amount' => $validated['bet_amount'],
//                 'valid_amount' => $validated['bet_amount'],
//             ]);

//             DB::commit();

//             return $this->success([
//                 'player_id' => $player->user_name,
//                 'balance' => $player->balanceFloat,
//             ], 'Callback processed');
//         } catch (\Exception $e) {
//             DB::rollBack();
//             Log::error('Provider callback: Exception', ['error' => $e->getMessage()]);

//             return $this->error('', 'Failed to process callback', 500);
//         }
//     }
// }
