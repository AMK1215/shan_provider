<?php

namespace App\Http\Controllers\Api\V1\gplus\Webhook;

use App\Enums\SeamlessWalletCode;
use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\PlaceBet;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\WalletService;
use Bavix\Wallet\Models\Transaction as WalletTransaction;
use Illuminate\Database\QueryException; // Import PlaceBet model
use Illuminate\Http\Request; // Import DB facade for transactions
use Illuminate\Support\Facades\Config; // Alias for Laravel Wallet's Transaction model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawController extends Controller
{
    protected $walletService;

    private array $allowedCurrencies = ['MMK', 'IDR', 'IDR2', 'KRW2', 'MMK2', 'VND2', 'LAK2', 'KHR2'];
   // private array $allowedCurrencies = ['MMK', 'VND', 'INR', 'MYR', 'AOA', 'EUR', 'IDR', 'PHP', 'THB', 'JPY', 'COP', 'IRR', 'CHF', 'USD', 'MXN', 'ETB', 'CAD', 'BRL', 'NGN', 'KES', 'KRW', 'TND', 'LBP', 'BDT', 'CZK'];

    private array $specialCurrencies = ['IDR2', 'KRW2', 'MMK2', 'VND2', 'LAK2', 'KHR2']; // Define special currencies

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function withdraw(Request $request)
    {
        // Log::info('Withdraw API Request', ['request' => $request->all()]);

        try {
            $request->validate([
                'operator_code' => 'required|string',
                'batch_requests' => 'required|array',
                'sign' => 'required|string',
                'request_time' => 'required|integer',
                'currency' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Withdraw API Validation Failed', ['errors' => $e->errors()]);

            return ApiResponseService::error(
                SeamlessWalletCode::InternalServerError,
                'Validation failed',
                $e->errors()
            );
        }

        $secretKey = Config::get('seamless_key.secret_key');
        $expectedSign = md5(
            $request->operator_code.
            $request->request_time.
            'withdraw'.
            $secretKey
        );

        $isValidSign = strtolower($request->sign) === strtolower($expectedSign);
        $isValidCurrency = in_array($request->currency, $this->allowedCurrencies);

        $responseData = [];

        foreach ($request->batch_requests as $batchRequest) {
            $memberAccount = $batchRequest['member_account'] ?? null;
            $productCode = $batchRequest['product_code'] ?? null;
            $gameType = $batchRequest['game_type'] ?? ''; // Added game_type from batchRequest
            $transactions = $batchRequest['transactions'] ?? [];

            // Handle batch-level errors (invalid signature or currency)
            if (! $isValidSign) {
                Log::warning('Invalid signature for batch', ['member_account' => $memberAccount, 'provided' => $request->sign, 'expected' => $expectedSign]);
                $responseData[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::InvalidSignature, 'Invalid signature', $request->currency);

                // We don't log to place_bets here as it's a request-level signature issue, not a transaction issue.
                continue;
            }

            if (! $isValidCurrency) {
                Log::warning('Invalid currency for batch', ['member_account' => $memberAccount, 'currency' => $request->currency]);
                $responseData[] = $this->buildErrorResponse($memberAccount, $productCode, 0.0, SeamlessWalletCode::InternalServerError, 'Invalid Currency', $request->currency);

                continue;
            }

            $user = User::where('user_name', $memberAccount)->with('wallet')->first();

            if (! $user) {
                Log::warning('Member not found for withdraw/bet request', ['member_account' => $memberAccount]);
                $responseData[] = [
                    'member_account' => $memberAccount,
                    'product_code' => $productCode,
                    'before_balance' => $this->formatBalance(0.00, $request->currency),
                    'balance' => $this->formatBalance(0.00, $request->currency),
                    'code' => SeamlessWalletCode::MemberNotExist->value,
                    'message' => 'Member not found',
                ];

                // No specific transaction to log in place_bets for a non-existent user at this point
                continue;
            }

            foreach ($transactions as $tx) {
                $transactionId = $tx['id'] ?? null;
                $action = $tx['action'] ?? null;
                $amount = $tx['amount'] ?? null;
                $wagerCode = $tx['wager_code'] ?? null;

                if (! $transactionId || ! $action || $amount === null) {
                    Log::warning('Missing crucial data in transaction for withdraw/bet', ['tx' => $tx]);
                    $this->logPlaceBet($batchRequest, $request, $tx, 'failed', $request->request_time, 'Missing transaction data (id, action, or amount)', $user->balanceFloat, $user->balanceFloat);
                    $responseData[] = $this->buildErrorResponse($memberAccount, $productCode, $user->balanceFloat, SeamlessWalletCode::InternalServerError, 'Missing transaction data (id, action, or amount)', $request->currency);

                    continue;
                }

                $currentBalance = $user->balanceFloat;
                $newBalance = $currentBalance;
                $transactionCode = SeamlessWalletCode::Success->value;
                $transactionMessage = '';

                $meta = [
                    'seamless_transaction_id' => $transactionId,
                    'action_type' => $action,
                    'product_code' => $productCode,
                    'wager_code' => $wagerCode,
                    'round_id' => $tx['round_id'] ?? null,
                    'game_code' => $tx['game_code'] ?? null,
                    'channel_code' => $tx['channel_code'] ?? null,
                    'raw_payload' => $tx,
                ];

                $duplicateInPlaceBets = PlaceBet::where('transaction_id', $transactionId)->first();
                $duplicateInWalletTransactions = WalletTransaction::whereJsonContains('meta->seamless_transaction_id', $transactionId)->first();

                if ($duplicateInPlaceBets || $duplicateInWalletTransactions) {
                    Log::warning('Duplicate transaction ID detected for withdraw/bet', ['tx_id' => $transactionId, 'member_account' => $memberAccount]);
                    $this->logPlaceBet($batchRequest, $request, $tx, 'duplicate', $request->request_time, 'Duplicate transaction', $currentBalance, $currentBalance);
                    $responseData[] = $this->buildErrorResponse($memberAccount, $productCode, $currentBalance, SeamlessWalletCode::DuplicateTransaction, 'Duplicate transaction', $request->currency);

                    continue;
                }

                if ($action === 'BET') {
                    $betAmount = abs($amount);

                    if ($betAmount <= 0) {
                        $transactionCode = SeamlessWalletCode::InternalServerError->value;
                        $transactionMessage = 'Bet amount must be positive and greater than zero.';
                        Log::warning('Invalid bet amount received', ['transaction_id' => $transactionId, 'amount' => $amount]);

                        $this->logPlaceBet($batchRequest, $request, $tx, 'failed', $request->request_time, $transactionMessage, $currentBalance, $currentBalance);
                    } elseif ($user->balanceFloat < $betAmount) {
                        $transactionCode = SeamlessWalletCode::InsufficientBalance->value;
                        $transactionMessage = 'Insufficient balance';
                        Log::warning('Insufficient balance for bet', ['member_account' => $memberAccount, 'bet_amount' => $betAmount, 'current_balance' => $currentBalance]);

                        $this->logPlaceBet($batchRequest, $request, $tx, 'failed', $request->request_time, $transactionMessage, $currentBalance, $currentBalance);
                    } else {
                        DB::beginTransaction();
                        try {
                            $user = User::with(['wallet' => function ($query) {
                                $query->lockForUpdate();
                            }])->find($user->id);

                            $beforeTransactionBalance = $user->wallet->balanceFloat;

                            $this->walletService->withdraw($user, $betAmount, TransactionName::Settled, $meta);
                            $newBalance = $user->balanceFloat;

                            $transactionCode = SeamlessWalletCode::Success->value;
                            $transactionMessage = 'Bet processed successfully';

                            $this->logPlaceBet($batchRequest, $request, $tx, 'completed', $request->request_time, $transactionMessage, $beforeTransactionBalance, $newBalance);
                            DB::commit();
                        } catch (\Bavix\Wallet\Exceptions\InsufficientFunds $e) {
                            DB::rollBack();
                            $transactionCode = SeamlessWalletCode::InsufficientBalance->value;
                            $transactionMessage = 'Insufficient balance (Wallet package)';
                            Log::error('Wallet Insufficient Funds for bet', ['transaction_id' => $transactionId, 'error' => $e->getMessage()]);
                            $this->logPlaceBet($batchRequest, $request, $tx, 'failed', $request->request_time, $transactionMessage, $currentBalance, $newBalance);
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            $transactionCode = SeamlessWalletCode::InternalServerError->value;
                            $transactionMessage = 'Failed to process bet transaction: '.$e->getMessage();
                            Log::error('Error processing bet transaction via WalletService', ['transaction_id' => $transactionId, 'error' => $e->getMessage()]);
                            $this->logPlaceBet($batchRequest, $request, $tx, 'failed', $request->request_time, $transactionMessage, $currentBalance, $newBalance);
                        }
                    }
                } else {
                    $transactionCode = SeamlessWalletCode::InternalServerError->value;
                    $transactionMessage = 'Unsupported action type for this endpoint: '.$action;
                    Log::warning('Unsupported action type received on withdraw endpoint', ['transaction_id' => $transactionId, 'action' => $action]);
                    $this->logPlaceBet($batchRequest, $request, $tx, 'failed', $request->request_time, $transactionMessage, $currentBalance, $newBalance);
                }

                $responseData[] = [
                    'member_account' => $memberAccount,
                    'product_code' => $productCode,
                    'before_balance' => $this->formatBalance($currentBalance, $request->currency),
                    'balance' => $this->formatBalance($newBalance, $request->currency),
                    'code' => $transactionCode,
                    'message' => $transactionMessage,
                ];
            }

        }

        return response()->json([
            'code' => SeamlessWalletCode::Success->value,
            'message' => 'Processed batch requests',
            'data' => $responseData,
        ]);
    }

    /**
     * Helper to build a consistent error response.
     */
    private function buildErrorResponse(string $memberAccount, string $productCode, float $balance, SeamlessWalletCode $code, string $message, string $currency): array
    {
        return [
            'member_account' => $memberAccount,
            'product_code' => $productCode,
            'before_balance' => $this->formatBalance($balance, $currency),
            'balance' => $this->formatBalance($balance, $currency),
            'code' => $code->value,
            'message' => $message,
        ];
    }

    /**
     * Formats the balance based on the currency.
     */
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

    // private function formatBalance(float $balance, string $currency): string
    // {
    //     if (in_array($currency, $this->specialCurrencies)) {
    //         // Apply 1:1000 conversion and round to 4 decimal places
    //         return number_format($balance / 1000, 4, '.', '');
    //     } else {
    //         // Round to 2 decimal places
    //         return number_format($balance, 2, '.', '');
    //     }
    // }

    /**
     * Logs the transaction attempt in the place_bets table using updateOrCreate.
     *
     * @param  string  $status  'completed', 'failed', 'duplicate', etc.
     * @param  int|null  $requestTime  The original request_time from the full request (milliseconds)
     */
    // private function logPlaceBet(array $batchRequest, Request $fullRequest, array $transactionRequest, string $status, ?int $requestTime, ?string $errorMessage = null, ?float $beforeBalance = null, ?float $afterBalance = null): void
    // {
    //     // Convert milliseconds to seconds if necessary for timestamp columns
    //     $requestTimeInSeconds = $requestTime ? floor($requestTime / 1000) : null;
    //     $settleAtTime = $transactionRequest['settle_at'] ?? $transactionRequest['settled_at'] ?? null;
    //     $settleAtInSeconds = $settleAtTime ? floor($settleAtTime / 1000) : null;
    //     $createdAtProviderTime = $transactionRequest['created_at'] ?? null;
    //     $createdAtProviderInSeconds = $createdAtProviderTime ? floor($createdAtProviderTime / 1000) : null;
    //     $provider_name = GameList::where('product_code', $batchRequest['product_code'])->value('provider');
    //     $game_name = GameList::where('game_code', $transactionRequest['game_code'])->value('game_name');
    //     $player_id = User::where('user_name', $batchRequest['member_account'])->value('id');
    //     $player_agent_id = User::where('user_name', $batchRequest['member_account'])->value('agent_id');
    //     PlaceBet::updateOrCreate(
    //         ['transaction_id' => $transactionRequest['id'] ?? ''], // Key for finding existing record
    //         [
    //             // Batch-level data (from the main $request and $batchRequest)
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

    //             // Transaction-level data (from $transactionRequest)
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
    //             'created_at_provider' => $createdAtProviderInSeconds ? now()->setTimestamp($createdAtProviderInSeconds) : null, // Assuming this field exists and is needed
    //             'game_code' => $transactionRequest['game_code'] ?? null,
    //             'game_name' => $game_name ?? $transactionRequest['game_code'] ?? null,
    //             'channel_code' => $transactionRequest['channel_code'] ?? null,
    //             'status' => $status, // 'completed', 'failed', 'duplicate', etc.
    //             'before_balance' => $beforeBalance,
    //             'balance' => $afterBalance,
    //         ]
    //     );
    // }

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
}
