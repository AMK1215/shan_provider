<?php

namespace App\Http\Controllers\Api\V1\gplus\Webhook;

use App\Enums\SeamlessWalletCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\PlaceBet;
use App\Models\Transaction as WalletTransaction;
use App\Models\TransactionLog;
use App\Models\User; // Assuming WalletTransaction for main wallet transactions
use App\Services\ApiResponseService;
use App\Services\WalletService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config; // Import the Exception class for better clarity
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    private array $allowedCurrencies = ['MMK', 'IDR', 'IDR2', 'KRW2', 'MMK2', 'VND2', 'LAK2', 'KHR2'];
    //private array $allowedCurrencies = ['MMK', 'VND', 'INR', 'MYR', 'AOA', 'EUR', 'IDR', 'PHP', 'THB', 'JPY', 'COP', 'IRR', 'CHF', 'USD', 'MXN', 'ETB', 'CAD', 'BRL', 'NGN', 'KES', 'KRW', 'TND', 'LBP', 'BDT', 'CZK'];
    // All possible actions, including those that might be refunds/credits
    private array $depositActions = ['WIN', 'SETTLED', 'JACKPOT', 'BONUS', 'PROMO', 'LEADERBOARD', 'FREEBET', 'PRESERVE_REFUND', 'CANCEL']; // Added CANCEL

    private array $allowedWagerStatuses = ['SETTLED', 'UNSETTLED', 'PENDING', 'CANCELLED', 'VOID']; // Added VOID

    public function deposit(Request $request)
    {
        // Log::info('Deposit API Request', ['request' => $request->all()]);

        try {
            $request->validate([
                'batch_requests' => 'required|array',
                'operator_code' => 'required|string',
                'currency' => 'required|string',
                'sign' => 'required|string',
                'request_time' => 'required|integer',
            ]);
            // validate batch_requests log the request
            Log::info('Deposit API Request', ['request' => $request->all()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Deposit API Validation Failed', ['errors' => $e->errors()]);

            return ApiResponseService::error(
                SeamlessWalletCode::InternalServerError,
                'Validation failed',
                $e->errors()
            );
        }

        $results = $this->processTransactions($request, true); // true for deposit

        // Log the transaction details
        TransactionLog::create([
            'type' => 'deposit',
            'batch_request' => $request->all(),
            'response_data' => $results,
            'status' => collect($results)->every(fn ($r) => $r['code'] === SeamlessWalletCode::Success->value) ? 'success' : 'partial_success_or_failure',
        ]);

        // Log::info('Deposit API Response', ['response' => $results]);

        return ApiResponseService::success($results);
    }

    /**
     * Centralized logic for processing seamless wallet transactions (withdraw/deposit).
     * Maps the Java processTransactions method.
     */
    private function processTransactions(Request $request, bool $isDeposit): array
    {
        $secretKey = Config::get('seamless_key.secret_key');
        $operatorCode = Config::get('seamless_key.operator_code'); // Use operator code from config

        // Correctly calculate expected sign, ensuring 'deposit'/'withdraw' string is used
        $expectedSign = md5(
            $request->operator_code.
            $request->request_time.
            ($isDeposit ? 'deposit' : 'withdraw'). // This string matters for signature
            $secretKey
        );
        $isValidSign = strtolower($request->sign) === strtolower($expectedSign);
        $isValidCurrency = in_array($request->currency, $this->allowedCurrencies);

        $results = [];
        $walletService = app(WalletService::class);
        $admin = User::adminUser(); // Assuming User::adminUser() exists and returns an admin user for deposits
        if (! $admin) {
            throw new \Exception('Admin user not configured properly.');
        }

        foreach ($request->batch_requests as $batchRequest) {

            Log::info('Deposit Batch Request', ['batchRequest' => $batchRequest]);

            $memberAccount = $batchRequest['member_account'] ?? null;
            $productCode = $batchRequest['product_code'] ?? null;
            $gameType = $batchRequest['game_type'] ?? '';



            // Handle batch-level errors (if signature/currency are invalid for the whole request)
            if (! $isValidSign) {
                Log::warning('Invalid signature for batch', ['member_account' => $memberAccount, 'provided' => $request->sign, 'expected' => $expectedSign]);
                $results[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::InvalidSignature, 'Invalid signature', $request->currency);

                continue;
            }

            if (! $isValidCurrency) {
                Log::warning('Invalid currency for batch', ['member_account' => $memberAccount, 'currency' => $request->currency]);
                $results[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::InternalServerError, 'Invalid Currency', $request->currency);

                continue;
            }

            try {
                $user = User::where('user_name', $memberAccount)->first();
                if (! $user) {
                    Log::warning('Member not found', ['member_account' => $memberAccount]);
                    $results[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::MemberNotExist, 'Member not found', $request->currency);

                    continue;
                }

                if (! $user->wallet) {
                    Log::warning('Wallet missing for member', ['member_account' => $memberAccount]);
                    $results[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::MemberNotExist, 'Member wallet missing', $request->currency);

                    continue;
                }

                $initialBalance = $user->wallet->balanceFloat; // Get initial balance before any transactions in this batch
                $currentBalance = $initialBalance; // Track current balance within the batch

                foreach ($batchRequest['transactions'] ?? [] as $transactionRequest) {
                    $transactionId = $transactionRequest['id'] ?? null;
                    $action = strtoupper($transactionRequest['action'] ?? '');
                    $wagerCode = $transactionRequest['wager_code'] ?? $transactionRequest['round_id'] ?? null;
                    // $amount = floatval($transactionRequest['amount'] ?? 0);
                    $amount = round(floatval($transactionRequest['amount'] ?? 0), 4);

                    $gameCode = $transactionRequest['game_code'] ?? null;
                        $transactionId = $transactionRequest['id'] ?? null;

                        // Correctly get game_type from batchRequest
                        $gameType = $batchRequest['game_type'] ?? null;

                        // Fallback to DB lookup if needed
                        if (empty($gameType) && $gameCode) {
                            $gameType = GameList::where('game_code', $gameCode)->value('game_type');
                        }

                        if (empty($gameType)) {
                            Log::warning('Missing game_type from batch_request and fallback lookup', [
                                'member_account' => $memberAccount,
                                'product_code' => $productCode,
                                'game_code' => $gameCode,
                                'transaction_id' => $transactionId,
                            ]);

                            $results[] = $this->buildErrorResponse(
                                $memberAccount,
                                $productCode,
                                0.0,
                                SeamlessWalletCode::InternalServerError,
                                'Missing game_type',
                                $request->currency
                            );
                            continue;
                        }


                    // Duplicate check by transaction_id in PlaceBet table
                    $duplicateInPlaceBets = PlaceBet::where('transaction_id', $transactionId)->first();
                    // Also check if the transaction is already recorded in the wallet's internal transactions
                    $duplicateInWalletTransactions = WalletTransaction::whereJsonContains('meta->seamless_transaction_id', $transactionId)->first();

                    if ($duplicateInPlaceBets || $duplicateInWalletTransactions) {
                        Log::warning('Duplicate transaction ID detected in place_bets or wallet_transactions', ['tx_id' => $transactionId, 'member_account' => $memberAccount]);
                        $results[] = $this->buildErrorResponse($memberAccount, $productCode, $currentBalance, SeamlessWalletCode::DuplicateTransaction, 'Duplicate transaction', $request->currency);
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'duplicate', $request->request_time); // Log duplicate attempt

                        continue; // Skip processing this duplicate transaction
                    }

                    // Check for invalid action type or wager status
                    if (! $this->isValidActionForDeposit($action) || ! $this->isValidWagerStatus($transactionRequest['wager_status'] ?? null)) {
                        Log::warning('Invalid action or wager status for deposit endpoint', ['action' => $action, 'wager_status' => $transactionRequest['wager_status'] ?? 'N/A', 'member_account' => $memberAccount]);
                        $results[] = $this->buildErrorResponse($memberAccount, $productCode, $currentBalance, SeamlessWalletCode::BetNotExist, 'Invalid action type or wager status for deposit', $request->currency);
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'failed', $request->request_time, 'Invalid action type or wager status for deposit');

                        continue;
                    }

                    // Specific handling for 'CANCEL' action: Check if the original bet exists
                    if ($action === 'CANCEL') {
                        // For a CANCEL action, the 'wager_code' in the request refers to the original bet that is being cancelled.
                        // We need to check if this original bet exists in our 'place_bets' table.
                        $originalBet = PlaceBet::where('wager_code', $wagerCode)
                            ->where('member_account', $memberAccount)
                            ->first();

                        if (! $originalBet) {
                            Log::warning('Original bet not found for CANCEL action', ['wager_code' => $wagerCode, 'member_account' => $memberAccount, 'transaction_id' => $transactionId]);
                            $results[] = $this->buildErrorResponse($memberAccount, $productCode, $currentBalance, SeamlessWalletCode::BetNotExist, 'Original bet not found for cancellation', $request->currency);
                            $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'failed', $request->request_time, 'Original bet not found for cancellation');

                            continue; // Skip processing this CANCEL if original bet doesn't exist
                        }
                        // Optionally, you might want to check the status of the original bet here (e.g., if it's already settled or cancelled)
                        // If ($originalBet->status === 'CANCELLED') { ... return duplicate or already cancelled error ... }
                    }

                    // Start a database transaction for each individual transaction request
                    DB::beginTransaction();
                    try {
                        // Re-fetch user and lock wallet inside transaction for isolation
                        $user->refresh(); // Get the latest state of the user and their wallet
                        // $user->wallet->lockForUpdate();
                        // $beforeTransactionBalance = $user->wallet->balanceFloat;

                        // Properly lock the wallet row inside a fresh DB transaction
                        $user = User::with(['wallet' => function ($query) {
                            $query->lockForUpdate();
                        }])->find($user->id);

                        if (! $user || ! $user->wallet) {
                            throw new \Exception('User or wallet not found during transaction locking.');
                        }

                        $beforeTransactionBalance = $user->wallet->balanceFloat;

                        $convertedAmount = $this->toDecimalPlaces($amount * $this->getCurrencyValue($request->currency));

                        if ($action === 'SETTLED' && $convertedAmount <= 0) {
                            Log::info('Skipping SETTLED with zero amount — no credit needed', [
                                'transaction_id' => $transactionId,
                                'member_account' => $memberAccount,
                                'converted_amount' => $convertedAmount,
                                'action' => $action,
                            ]);
                        
                            $this->logPlaceBet(
                                $batchRequest,
                                $request,
                                $transactionRequest,
                                'loss',
                                $request->request_time,
                                'SETTLED with 0 amount — skipping',
                                $beforeTransactionBalance,
                                $beforeTransactionBalance
                            );
                        
                            continue;
                        }
                        
                       
                        // Specific logic for deposit endpoint
                        // if ($convertedAmount <= 0) {
                        //     throw new \Exception('Deposit amount must be positive.');
                        // }
                       

                        if ($convertedAmount <= 0) {
                            Log::info('Logging loss (zero-amount transaction)', [
                                'transaction_id' => $transactionId,
                                'member_account' => $memberAccount,
                                'action' => $action
                            ]);

                            $this->logPlaceBet(
                                $batchRequest,
                                $request,
                                $transactionRequest,
                                'loss', // custom status for analytics
                                $request->request_time,
                                'No payout — losing round',
                                $beforeTransactionBalance ?? null,
                                $beforeTransactionBalance ?? null
                            );

                            continue; // skip deposit
                        }

                        
                        $walletService->deposit($user, $convertedAmount, TransactionName::Deposit, [
                            'seamless_transaction_id' => $transactionId,
                            'action' => $action,
                            'wager_code' => $wagerCode,
                            'product_code' => $productCode,
                            'game_type' => $gameType,
                            'from_admin' => $admin->id, // If admin is involved in deposits
                        ]);

                        // Get balance after successful transaction
                        $afterTransactionBalance = $user->wallet->balanceFloat;

                        // Log success and add to results
                        // Log::info('Transaction successful', ['member_account' => $memberAccount, 'action' => $action, 'before' => $beforeTransactionBalance, 'after' => $afterTransactionBalance]);
                        
                        $results[] = [
                            'member_account' => $memberAccount,
                            'product_code' => (int) $productCode, // ✅ important fix
                            'before_balance' => round($beforeTransactionBalance / $this->getCurrencyValue($request->currency), 4),
                            'balance' => round($afterTransactionBalance / $this->getCurrencyValue($request->currency), 4),
                            'code' => SeamlessWalletCode::Success->value,
                            'message' => '',
                        ];
                        
                        $currentBalance = $afterTransactionBalance; // Update current balance for next transaction in batch
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'completed', $request->request_time, null, $beforeTransactionBalance, $afterTransactionBalance);

                        DB::commit(); // Commit inner transaction
                    } catch (\Exception $e) {
                        DB::rollBack(); // Rollback inner transaction
                        Log::error('Transaction processing exception', ['error' => $e->getMessage(), 'member_account' => $memberAccount, 'request_transaction' => $transactionRequest]);
                        $code = SeamlessWalletCode::InternalServerError;
                        if (str_contains($e->getMessage(), 'amount must be positive')) {
                            $code = SeamlessWalletCode::InsufficientBalance; // Re-using 1001 for invalid amount, as per Java's use
                        }
                        $results[] = $this->buildErrorResponse(
                            $memberAccount,
                            $productCode,
                            $currentBalance, // Pass the float to buildErrorResponse
                            $code,
                            $e->getMessage(),
                            $request->currency // Pass currency to buildErrorResponse
                        );
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'failed', $request->request_time, $e->getMessage(), $beforeTransactionBalance ?? null, $afterTransactionBalance ?? null);
                    }
                }
            } catch (\Throwable $e) {
                // Catch any unexpected errors that might occur outside the inner transaction loop
                Log::error('Batch processing exception for member', ['member_account' => $memberAccount, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                $results[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::InternalServerError, 'An unexpected error occurred during batch processing.', $request->currency);
            }
        }

        return $results;
    }

    /**
     * Helper to build a consistent error response.
     * Added $currency parameter to ensure consistent formatting even for errors.
     */
    private function buildErrorResponse(string $memberAccount, string $productCode, float $balance, SeamlessWalletCode $code, string $message, string $currency): array
    {
        // Apply number_format here as well for consistency
        // $formattedBalance = number_format($balance / $this->getCurrencyValue($currency), 4, '.', '');
        $formattedBalance = round($balance / $this->getCurrencyValue($currency), 4);


        return [
            'member_account' => $memberAccount,
            'product_code' => (int) $productCode, // ✅ Force int
            'before_balance' => $formattedBalance,
            'balance' => $formattedBalance,
            'code' => $code->value,
            'message' => $message,
        ];
        
    }

    /**
     * Converts a float to a specified number of decimal places.
     * This emulates Java's `toDecimalPlaces`.
     * You might need a more robust solution for financial calculations.
     */
    private function toDecimalPlaces(float $value, int $precision = 4): float
    {
        return round($value, $precision);
    }

    /**
     * Gets the currency conversion value.
     * This is a placeholder; you'd implement actual currency rates here.
     */
    private function getCurrencyValue(string $currency): int
    {
        // Example: If IDR2 means 1 unit = 100 IDR
        return match ($currency) {
            'IDR2' => 100, // Example multiplier
            'KRW2' => 10,
            'MMK2' => 1000,
            'VND2' => 1000,
            'LAK2' => 10,
            'KHR2' => 100,
            default => 1, // Default for IDR, etc.
        };
    }

    /**
     * Check if the action is valid specifically for the deposit endpoint.
     */
    private function isValidActionForDeposit(string $action): bool
    {
        return in_array($action, $this->depositActions);
    }

    /**
     * Check if the wager status is valid.
     */
    private function isValidWagerStatus(?string $wagerStatus): bool
    {
        // If wagerStatus is null, it might be allowed for certain actions or situations
        if (is_null($wagerStatus)) {
            return true; // Or false, depending on your API spec
        }

        return in_array($wagerStatus, $this->allowedWagerStatuses);
    }

    private function formatBalance(float $balance, string $currency): float
    {
        if (in_array($currency, $this->specialCurrencies)) {
            // Apply 1:1000 conversion and round to 4 decimal places
            return round($balance / 1000, 4);
        } else {
            // Round to 2 decimal places
            return round($balance, 2);
        }
    }

    /**
     * Logs the transaction attempt in the place_bets table.
     *
     * @param  int|null  $requestTime  The original request_time from the full request
     */
    private function logPlaceBet(
        array $batchRequest,
        Request $fullRequest,
        array $transactionRequest,
        string $status,
        ?int $requestTime,
        ?string $errorMessage = null,
        ?float $beforeBalance = null,
        ?float $afterBalance = null
    ): void {
        // Convert milliseconds to seconds if necessary for timestamp columns
        $requestTimeInSeconds = $requestTime ? floor($requestTime / 1000) : null;
        $settleAtTime = $transactionRequest['settle_at'] ?? $transactionRequest['settled_at'] ?? null;
        $settleAtInSeconds = $settleAtTime ? floor($settleAtTime / 1000) : null;
        $createdAtProviderTime = $transactionRequest['created_at'] ?? null;
        $createdAtProviderInSeconds = $createdAtProviderTime ? floor($createdAtProviderTime / 1000) : null;
        $provider_name = GameList::where('product_code', $batchRequest['product_code'])->value('provider');
        $game_name = GameList::where('game_code', $transactionRequest['game_code'])->value('game_name');
        $player_id = User::where('user_name', $batchRequest['member_account'])->value('id');
        $player_agent_id = User::where('user_name', $batchRequest['member_account'])->value('agent_id');

        try {
            PlaceBet::create([
                'transaction_id' => $transactionRequest['id'] ?? '',
                'member_account' => $batchRequest['member_account'] ?? '',
                'player_id' => $player_id,
                'player_agent_id' => $player_agent_id,
                'product_code' => $batchRequest['product_code'] ?? 0,
                'provider_name' => $provider_name ?? $batchRequest['product_code'] ?? null,
                'game_type' => $batchRequest['game_type'] ?? '',
                'operator_code' => $fullRequest->operator_code,
                'request_time' => $requestTimeInSeconds ? now()->setTimestamp($requestTimeInSeconds) : null,
                'sign' => $fullRequest->sign,
                'currency' => $fullRequest->currency,
                'action' => $transactionRequest['action'] ?? '',
                'amount' => $transactionRequest['amount'] ?? 0,
                'valid_bet_amount' => $transactionRequest['valid_bet_amount'] ?? null,
                'bet_amount' => $transactionRequest['bet_amount'] ?? null,
                'prize_amount' => $transactionRequest['prize_amount'] ?? null,
                'tip_amount' => $transactionRequest['tip_amount'] ?? null,
                'wager_code' => $transactionRequest['wager_code'] ?? null,
                'wager_status' => $transactionRequest['wager_status'] ?? null,
                'round_id' => $transactionRequest['round_id'] ?? null,
                'payload' => isset($transactionRequest['payload']) ? json_encode($transactionRequest['payload']) : null,
                'settle_at' => $settleAtInSeconds ? now()->setTimestamp($settleAtInSeconds) : null,
                'created_at_provider' => $createdAtProviderInSeconds ? now()->setTimestamp($createdAtProviderInSeconds) : null,
                'game_code' => $transactionRequest['game_code'] ?? null,
                'game_name' => $game_name ?? $transactionRequest['game_code'] ?? null,
                'channel_code' => $transactionRequest['channel_code'] ?? null,
                'status' => $status,
                'before_balance' => $beforeBalance,
                'balance' => $afterBalance,
                'error_message' => $errorMessage, // Optional, if you have this field
            ]);
        } catch (QueryException $e) {
            // MySQL: 23000, PostgreSQL: 23505
            if (in_array($e->getCode(), ['23000', '23505'])) {
                // Duplicate detected: log it, but do NOT overwrite existing record
                Log::warning('Duplicate transaction detected in logPlaceBet', [
                    'transaction_id' => $transactionRequest['id'] ?? '',
                    'member_account' => $batchRequest['member_account'] ?? '',
                    'error' => $e->getMessage(),
                ]);
                // You might want to do something else, e.g. fire an event or count duplicates
                // But do NOT insert/update anything here for audit safety
            } else {
                // Other DB errors: let them bubble up or handle as you see fit
                throw $e;
            }
        }
    }

    // private function logPlaceBet(array $batchRequest, Request $fullRequest, array $transactionRequest, string $status, ?int $requestTime, ?string $errorMessage = null, ?float $beforeBalance = null, ?float $afterBalance = null): void
    // {
    //     // Convert milliseconds to seconds if necessary
    //     $requestTimeInSeconds = $requestTime ? floor($requestTime / 1000) : null;
    //     $settleAtTime = $transactionRequest['settle_at'] ?? $transactionRequest['settled_at'] ?? null;
    //     $settleAtInSeconds = $settleAtTime ? floor($settleAtTime / 1000) : null;
    //     // Assuming 'created_at' from provider is also a timestamp in milliseconds
    //     $createdAtProviderTime = $transactionRequest['created_at'] ?? null;
    //     $createdAtProviderInSeconds = $createdAtProviderTime ? floor($createdAtProviderTime / 1000) : null;

    //     $provider_name = GameList::where('product_code', $batchRequest['product_code'])->value('provider');
    //     $game_name = GameList::where('game_code', $transactionRequest['game_code'])->value('game_name');

    //     $player_id = User::where('user_name', $batchRequest['member_account'])->value('id');
    //     $player_agent_id = User::where('user_name', $batchRequest['member_account'])->value('agent_id');
    //     PlaceBet::updateOrCreate(
    //         ['transaction_id' => $transactionRequest['id'] ?? ''], // Use transaction_id for uniqueness
    //         [
    //             // Batch-level
    //             'member_account' => $batchRequest['member_account'] ?? '',
    //             'player_id' => $player_id,
    //             'player_agent_id' => $player_agent_id,
    //             'product_code' => $batchRequest['product_code'] ?? 0,
    //             'provider_name' => $provider_name ?? $batchRequest['product_code'] ?? null,
    //             'game_type' => $batchRequest['game_type'] ?? '',
    //             'operator_code' => $fullRequest->operator_code,
    //             'request_time' => $requestTimeInSeconds ? now()->setTimestamp($requestTimeInSeconds) : null,
    //             'sign' => $fullRequest->sign,
    //             'currency' => $fullRequest->currency,

    //             // Transaction-level
    //             'action' => $transactionRequest['action'] ?? '',
    //             'amount' => $transactionRequest['amount'] ?? 0,
    //             'valid_bet_amount' => $transactionRequest['valid_bet_amount'] ?? null,
    //             'bet_amount' => $transactionRequest['bet_amount'] ?? null,
    //             'prize_amount' => $transactionRequest['prize_amount'] ?? null,
    //             'tip_amount' => $transactionRequest['tip_amount'] ?? null,
    //             'wager_code' => $transactionRequest['wager_code'] ?? null,
    //             'wager_status' => $transactionRequest['wager_status'] ?? null,
    //             'round_id' => $transactionRequest['round_id'] ?? null,
    //             'payload' => isset($transactionRequest['payload']) ? json_encode($transactionRequest['payload']) : null,
    //             'settle_at' => $settleAtInSeconds ? now()->setTimestamp($settleAtInSeconds) : null,
    //             'created_at_provider' => $createdAtProviderInSeconds ? now()->setTimestamp($createdAtProviderInSeconds) : null,
    //             'game_code' => $transactionRequest['game_code'] ?? null,
    //             'game_name' => $game_name ?? $transactionRequest['game_code'] ?? null,
    //             'channel_code' => $transactionRequest['channel_code'] ?? null,
    //             'status' => $status,
    //             'before_balance' => $beforeBalance,
    //             'balance' => $afterBalance,

    //         ]
    //     );
    // }
}
