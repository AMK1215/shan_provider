<?php

namespace App\Http\Controllers\Api\V1\gplus\Webhook;

use App\Enums\SeamlessWalletCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\PlaceBet;
use App\Models\Transaction as WalletTransaction;
use App\Models\TransactionLog;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\WalletService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    /**
     * @var array Allowed currencies for deposit.
     */
    private array $allowedCurrencies = ['MMK', 'IDR', 'IDR2', 'KRW2', 'MMK2', 'VND2', 'LAK2', 'KHR2'];

    /**
     * @var array Actions considered as deposits.
     */
    private array $depositActions = ['WIN', 'SETTLED', 'JACKPOT', 'BONUS', 'PROMO', 'LEADERBOARD', 'FREEBET', 'PRESERVE_REFUND', 'CANCEL'];

    /**
     * @var array Allowed wager statuses.
     */
    private array $allowedWagerStatuses = ['SETTLED', 'UNSETTLED', 'PENDING', 'CANCELLED', 'VOID'];

    /**
     * Handle incoming deposit requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deposit(Request $request)
    {
        try {
            $request->validate([
                'batch_requests' => 'required|array',
                'operator_code' => 'required|string',
                'currency' => 'required|string',
                'sign' => 'required|string',
                'request_time' => 'required|integer',
            ]);
            Log::info('Deposit API Request', ['request' => $request->all()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Deposit API Validation Failed', ['errors' => $e->errors()]);

            return ApiResponseService::error(
                SeamlessWalletCode::InternalServerError,
                'Validation failed',
                $e->errors()
            );
        }

        $results = $this->processTransactions($request, true);

        TransactionLog::create([
            'type' => 'deposit',
            'batch_request' => $request->all(),
            'response_data' => $results,
            'status' => collect($results)->every(fn ($r) => $r['code'] === SeamlessWalletCode::Success->value) ? 'success' : 'partial_success_or_failure',
        ]);

        return ApiResponseService::success($results);
    }

    /**
     * Centralized logic for processing seamless wallet transactions (withdraw/deposit).
     *
     * @throws Exception
     */
    private function processTransactions(Request $request, bool $isDeposit): array
    {
        $secretKey = Config::get('seamless_key.secret_key');
        // $operatorCode = Config::get('seamless_key.operator_code'); // Not used directly in this method

        $expectedSign = md5(
            $request->operator_code.
            $request->request_time.
            ($isDeposit ? 'deposit' : 'withdraw').
            $secretKey
        );
        $isValidSign = strtolower($request->sign) === strtolower($expectedSign);
        $isValidCurrency = in_array($request->currency, $this->allowedCurrencies);

        $results = [];
        $walletService = app(WalletService::class);
        $admin = User::adminUser();
        if (! $admin) {
            throw new Exception('Admin user not configured properly.');
        }

        foreach ($request->batch_requests as $batchRequest) {
            Log::info('Deposit Batch Request', ['batchRequest' => $batchRequest]);

            $memberAccount = $batchRequest['member_account'] ?? null;
            $productCode = $batchRequest['product_code'] ?? null;
            $gameType = $batchRequest['game_type'] ?? '';

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

                $initialBalance = $user->wallet->balanceFloat;
                $currentBalance = $initialBalance;

                foreach ($batchRequest['transactions'] ?? [] as $transactionRequest) {
                    $transactionId = $transactionRequest['id'] ?? null;
                    $action = strtoupper($transactionRequest['action'] ?? '');
                    $wagerCode = $transactionRequest['wager_code'] ?? $transactionRequest['round_id'] ?? null;
                    $amount = round(floatval($transactionRequest['amount'] ?? 0), 4);
                    $gameCode = $transactionRequest['game_code'] ?? null;

                    // Determine game_type, prioritizing batchRequest, then DB lookup
                    $transactionGameType = $batchRequest['game_type'] ?? null;
                    if (empty($transactionGameType) && $gameCode) {
                        $transactionGameType = GameList::where('game_code', $gameCode)->value('game_type');
                    }

                    if (empty($transactionGameType)) {
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
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'failed', $request->request_time, 'Missing game_type');

                        continue;
                    }

                    // Duplicate check
                    $isDuplicate = PlaceBet::where('transaction_id', $transactionId)->exists() ||
                        WalletTransaction::whereJsonContains('meta->seamless_transaction_id', $transactionId)->exists();

                    if ($isDuplicate) {
                        Log::warning('Duplicate transaction ID detected in place_bets or wallet_transactions', ['tx_id' => $transactionId, 'member_account' => $memberAccount]);
                        $results[] = $this->buildErrorResponse($memberAccount, $productCode, $currentBalance, SeamlessWalletCode::DuplicateTransaction, 'Duplicate transaction', $request->currency);
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'duplicate', $request->request_time, 'Duplicate transaction');

                        continue;
                    }

                    // Validate action and wager status
                    if (! $this->isValidActionForDeposit($action) || ! $this->isValidWagerStatus($transactionRequest['wager_status'] ?? null)) {
                        Log::warning('Invalid action or wager status for deposit endpoint', ['action' => $action, 'wager_status' => $transactionRequest['wager_status'] ?? 'N/A', 'member_account' => $memberAccount]);
                        $results[] = $this->buildErrorResponse($memberAccount, $productCode, $currentBalance, SeamlessWalletCode::BetNotExist, 'Invalid action type or wager status for deposit', $request->currency);
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'failed', $request->request_time, 'Invalid action type or wager status for deposit');

                        continue;
                    }

                    // Specific handling for 'CANCEL' action
                    if ($action === 'CANCEL') {
                        $originalBet = PlaceBet::where('wager_code', $wagerCode)
                            ->where('member_account', $memberAccount)
                            ->first();

                        if (! $originalBet) {
                            Log::warning('Original bet not found for CANCEL action', ['wager_code' => $wagerCode, 'member_account' => $memberAccount, 'transaction_id' => $transactionId]);
                            $results[] = $this->buildErrorResponse($memberAccount, $productCode, $currentBalance, SeamlessWalletCode::BetNotExist, 'Original bet not found for cancellation', $request->currency);
                            $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'failed', $request->request_time, 'Original bet not found for cancellation');

                            continue;
                        }
                    }

                    DB::beginTransaction();
                    try {
                        $user->refresh();
                        $userWithWallet = User::with(['wallet' => function ($query) {
                            $query->lockForUpdate();
                        }])->find($user->id);

                        if (! $userWithWallet || ! $userWithWallet->wallet) {
                            throw new Exception('User or wallet not found during transaction locking.');
                        }

                        $beforeTransactionBalance = $userWithWallet->wallet->balanceFloat;
                        $convertedAmount = $this->toDecimalPlaces($amount * $this->getCurrencyValue($request->currency));

                        // Skip SETTLED with zero or negative amount if it means no actual credit
                        // if ($action === 'SETTLED' && $convertedAmount <= 0) {
                        //     Log::info('Skipping SETTLED with zero/negative amount – no credit needed', [
                        //         'transaction_id' => $transactionId,
                        //         'member_account' => $memberAccount,
                        //         'converted_amount' => $convertedAmount,
                        //         'action' => $action,
                        //     ]);
                        //     $this->logPlaceBet(
                        //         $batchRequest,
                        //         $request,
                        //         $transactionRequest,
                        //         'loss',
                        //         $request->request_time,
                        //         'SETTLED with 0 amount — skipping',
                        //         $beforeTransactionBalance,
                        //         $beforeTransactionBalance
                        //     );
                        //     DB::commit(); // Commit the transaction for this specific skip
                        //     continue;
                        // }

                        // Handle other actions with zero/negative amount if they should be logged but not affect balance
                        // if ($convertedAmount <= 0) {
                        //      Log::info('Logging zero/negative amount transaction without balance change', [
                        //         'transaction_id' => $transactionId,
                        //         'member_account' => $memberAccount,
                        //         'action' => $action,
                        //         'converted_amount' => $convertedAmount,
                        //     ]);
                        //     $this->logPlaceBet(
                        //         $batchRequest,
                        //         $request,
                        //         $transactionRequest,
                        //         'info', // Or a more specific status like 'zero_amount'
                        //         $request->request_time,
                        //         'Zero/negative amount transaction processed without balance change',
                        //         $beforeTransactionBalance,
                        //         $beforeTransactionBalance
                        //     );
                        //     DB::commit(); // Commit the transaction for this specific skip
                        //     continue;
                        // }

                        $walletService->deposit($userWithWallet, $convertedAmount, TransactionName::Deposit, [
                            'seamless_transaction_id' => $transactionId,
                            'action' => $action,
                            'wager_code' => $wagerCode,
                            'product_code' => $productCode,
                            'game_type' => $transactionGameType,
                            'from_admin' => $admin->id,
                        ]);

                        $afterTransactionBalance = $userWithWallet->wallet->balanceFloat;

                        $results[] = [
                            'member_account' => $memberAccount,
                            'product_code' => (int) $productCode,
                            'before_balance' => round($beforeTransactionBalance / $this->getCurrencyValue($request->currency), 4),
                            'balance' => round($afterTransactionBalance / $this->getCurrencyValue($request->currency), 4),
                            'code' => SeamlessWalletCode::Success->value,
                            'message' => '',
                        ];

                        $currentBalance = $afterTransactionBalance;
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'completed', $request->request_time, null, $beforeTransactionBalance, $afterTransactionBalance);

                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        Log::error('Transaction processing exception', ['error' => $e->getMessage(), 'member_account' => $memberAccount, 'request_transaction' => $transactionRequest]);
                        $code = SeamlessWalletCode::InternalServerError;
                        if (str_contains($e->getMessage(), 'amount must be positive')) {
                            $code = SeamlessWalletCode::InsufficientBalance; // Re-using 1001 for invalid amount, as per Java's use
                        }
                        $results[] = $this->buildErrorResponse(
                            $memberAccount,
                            $productCode,
                            $currentBalance,
                            $code,
                            $e->getMessage(),
                            $request->currency
                        );
                        $this->logPlaceBet($batchRequest, $request, $transactionRequest, 'failed', $request->request_time, $e->getMessage(), $beforeTransactionBalance ?? null, $afterTransactionBalance ?? null);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Batch processing exception for member', ['member_account' => $memberAccount, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                $results[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::InternalServerError, 'An unexpected error occurred during batch processing.', $request->currency);
            }
        }

        return $results;
    }

    /**
     * Helper to build a consistent error response.
     */
    private function buildErrorResponse(string $memberAccount, string $productCode, float $balance, SeamlessWalletCode $code, string $message, string $currency): array
    {
        $formattedBalance = round($balance / $this->getCurrencyValue($currency), 4);

        return [
            'member_account' => $memberAccount,
            'product_code' => (int) $productCode,
            'before_balance' => $formattedBalance,
            'balance' => $formattedBalance,
            'code' => $code->value,
            'message' => $message,
        ];
    }

    /**
     * Converts a float to a specified number of decimal places.
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
        return match ($currency) {
            'IDR2' => 100, // Example multiplier
            'KRW2' => 10,
            'MMK2' => 1000,
            'VND2' => 1000,
            'LAK2' => 10,
            'KHR2' => 100,
            default => 1,
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
        if (is_null($wagerStatus)) {
            return true;
        }

        return in_array($wagerStatus, $this->allowedWagerStatuses);
    }

    /**
     * Logs the transaction attempt in the place_bets table.
     *
     * @param  array  $batchRequest  The current batch request being processed.
     * @param  Request  $fullRequest  The full incoming HTTP request.
     * @param  array  $transactionRequest  The individual transaction details from the batch.
     * @param  string  $status  The status of the transaction ('completed', 'failed', 'duplicate', 'info', 'loss').
     * @param  int|null  $requestTime  The original request_time from the full request (milliseconds).
     * @param  string|null  $errorMessage  Optional error message.
     * @param  float|null  $beforeBalance  Optional balance before the transaction.
     * @param  float|null  $afterBalance  Optional balance after the transaction.
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
        $requestTimeInSeconds = $requestTime ? floor($requestTime / 1000) : null;
        $settleAtTime = $transactionRequest['settle_at'] ?? $transactionRequest['settled_at'] ?? null;
        $settleAtInSeconds = $settleAtTime ? floor($settleAtTime / 1000) : null;
        $createdAtProviderTime = $transactionRequest['created_at'] ?? null;
        $createdAtProviderInSeconds = $createdAtProviderTime ? floor($createdAtProviderTime / 1000) : null;

        $providerName = GameList::where('product_code', $batchRequest['product_code'])->value('provider');
        $gameName = GameList::where('game_code', $transactionRequest['game_code'])->value('game_name');
        $playerId = User::where('user_name', $batchRequest['member_account'])->value('id');
        $playerAgentId = User::where('user_name', $batchRequest['member_account'])->value('agent_id');

        try {
            PlaceBet::create([
                'transaction_id' => $transactionRequest['id'] ?? '',
                'member_account' => $batchRequest['member_account'] ?? '',
                'player_id' => $playerId,
                'player_agent_id' => $playerAgentId,
                'product_code' => $batchRequest['product_code'] ?? 0,
                'provider_name' => $providerName ?? $batchRequest['product_code'] ?? null,
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
                'game_name' => $gameName ?? $transactionRequest['game_code'] ?? null,
                'channel_code' => $transactionRequest['channel_code'] ?? null,
                'status' => $status,
                'before_balance' => $beforeBalance,
                'balance' => $afterBalance,
                // 'error_message' => $errorMessage,
            ]);
        } catch (QueryException $e) {
            if (in_array($e->getCode(), ['23000', '23505'])) { // SQLSTATE for unique constraint violation
                Log::warning('Duplicate transaction detected when logging to PlaceBet, preventing re-insertion.', [
                    'transaction_id' => $transactionRequest['id'] ?? '',
                    'member_account' => $batchRequest['member_account'] ?? '',
                    'error' => $e->getMessage(),
                ]);
            } else {
                throw $e;
            }
        }
    }
}
