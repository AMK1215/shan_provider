<?php

namespace App\Http\Controllers\Api\V1\Shan;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReportTransaction;
use App\Models\User;
use App\Services\WalletService;
use App\Traits\HttpResponses;
use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShanApiTransactionController extends Controller
{
    use HttpResponses;

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function ShanTransactionCreate(Request $request): JsonResponse
    {
        Log::info('ShanTransaction: Request received', [
            'payload' => $request->all(),
        ]);

        $validated = $request->validate([
            'banker' => 'required|array',
            'banker.player_id' => 'required|string',
            'players' => 'required|array',
            'players.*.player_id' => 'required|string',
            'players.*.bet_amount' => 'required|numeric|min:0',
            'players.*.win_lose_status' => 'required|integer|in:0,1',
            // 'game_type_id' => 'required|integer',
        ]);

        $samplePlayerId = collect($validated['players'])
            ->firstWhere('player_id', '!=', $validated['banker']['player_id'])['player_id'] ?? null;

        Log::info('Sample Player ID', ['samplePlayerId' => $samplePlayerId]);

        if (! $samplePlayerId) {
            $samplePlayerId = $validated['banker']['player_id'];
        }

        $player = User::where('user_name', $samplePlayerId)->first();

        if (! $player) {
            return $this->error('Player not found to determine agent', 'Player not found', 404);
        }

        $player_agent_code = $player->shan_agent_code;
        Log::info('Player Agent Code', ['player_agent_code' => $player_agent_code]);
        $agent = User::where('shan_agent_code', $player_agent_code)->first();

        // if (!$agent) {
        //     return $this->error('Agent not found for player\'s agent code', 'Agent not found', 404);
        // }

        $secret_key = $agent->shan_secret_key;
        $callback_url_base = $agent->shan_callback_url;

        // if (!$secret_key) {
        //     return $this->error('Secret Key not set for agent', 'Secret Key not set', 404);
        // }
        // if (!$callback_url_base) {
        //     return $this->error('Callback URL not set for agent', 'Callback URL not set', 404);
        // }

        Log::info('Agent Secret Key Retrieved', [
            'agent_username' => $agent->user_name,
            'secret_key_masked' => Str::mask($secret_key, '*', 0, max(0, strlen($secret_key) - 4)),
            'callback_url_base' => $callback_url_base,
        ]);

        $agent_balance = $agent->wallet->balanceFloat;
        if ($agent_balance < 0) {
            return $this->error('Agent balance is negative', 'Agent balance is negative', 404);
        }

        Log::info('Agent balance', ['agent_balance' => $agent_balance]);

        do {
            $wager_code = Str::random(12);
        } while (ReportTransaction::where('wager_code', $wager_code)->exists());

        // Initialize variables at the top of the try block
        $results = [];
        $callbackPlayers = [];
        $totalPlayerNet = 0;
        $bankerOldBalance = 0;
        $trueTotalPlayerNet = 0; // <--- INITIALIZE HERE AS WELL

        try {
            DB::beginTransaction();

            foreach ($validated['players'] as $playerData) {
                $participant = User::where('user_name', $playerData['player_id'])->first();
                if (! $participant) {
                    throw new \RuntimeException("Participant not found: {$playerData['player_id']}. Transaction aborted.");
                }

                $oldBalance = $participant->wallet->balanceFloat;
                $betAmount = $playerData['bet_amount'];
                $winLose = $playerData['win_lose_status'];

                $amountChanged = ($winLose == 1) ? $betAmount : -$betAmount;
                $totalPlayerNet += $amountChanged;

                if ($amountChanged > 0) {
                    $this->walletService->deposit($participant, $amountChanged, TransactionName::GameWin, [
                        'description' => 'Win from Shan game', 'wager_code' => $wager_code, 'bet_amount' => $betAmount,
                    ]);
                } elseif ($amountChanged < 0) {
                    $this->walletService->withdraw($participant, abs($amountChanged), TransactionName::GameLoss, [
                        'description' => 'Loss in Shan game', 'wager_code' => $wager_code, 'bet_amount' => $betAmount,
                    ]);
                }

                $participant->refresh();

                ReportTransaction::create([
                    'user_id' => $participant->id, 'agent_id' => $participant->agent_id, 'member_account' => $participant->user_name,
                    'transaction_amount' => abs($amountChanged), 'status' => $winLose, 'bet_amount' => $betAmount,
                    'valid_amount' => $betAmount, 'before_balance' => $oldBalance, 'after_balance' => $participant->wallet->balanceFloat,
                    'banker' => ($participant->user_name === $validated['banker']['player_id']) ? 1 : 0,
                    'wager_code' => $wager_code, 'settled_status' => $winLose == 1 ? 'settled_win' : 'settled_loss',
                ]);

                $results[] = [
                    'player_id' => $participant->user_name,
                    'balance' => $participant->wallet->balanceFloat,
                ];

                if ($participant->user_name !== $validated['banker']['player_id']) {
                    $callbackPlayers[] = [
                        'player_id' => $participant->user_name,
                        'balance' => $participant->wallet->balanceFloat,
                    ];
                }
            }

            $bankerUserName = $validated['banker']['player_id'];
            $banker = User::where('user_name', $bankerUserName)->first();

            if (! $banker) {
                Log::error('ShanTransaction: Banker user not found', ['banker_id' => $bankerUserName]);

                return $this->error('Banker not found', 'Banker user not found in the system', 500);
            }

            $bankerOldBalance = $banker->wallet->balanceFloat;

            Log::info('ShanTransaction: Using banker', [
                'banker_id' => $banker->user_name,
                'balance' => $banker->wallet->balanceFloat,
            ]);

            // Re-calculate trueTotalPlayerNet here, as it might be needed for the bankerAmountChange
            // This loop is fine here, as $validated['players'] should always be available.
            $trueTotalPlayerNet = 0;
            foreach ($validated['players'] as $playerData) {
                if ($playerData['player_id'] !== $bankerUserName) {
                    $trueTotalPlayerNet += ($playerData['win_lose_status'] == 1) ? $playerData['bet_amount'] : -$playerData['bet_amount'];
                }
            }

            $bankerAmountChange = -$trueTotalPlayerNet;

            if ($bankerAmountChange > 0) {
                $this->walletService->deposit($banker, $bankerAmountChange, TransactionName::BankerDeposit, [
                    'description' => 'Banker receive (from non-banker players)', 'wager_code' => $wager_code,
                ]);
            } elseif ($bankerAmountChange < 0) {
                $this->walletService->withdraw($banker, abs($bankerAmountChange), TransactionName::BankerWithdraw, [
                    'description' => 'Banker payout (to non-banker players)', 'wager_code' => $wager_code,
                ]);
            }

            $banker->refresh();

            $existingBankerReport = ReportTransaction::where('user_id', $banker->id)
                ->where('wager_code', $wager_code)
                ->where('banker', 1)
                ->first();
            if (! $existingBankerReport) {
                ReportTransaction::create([
                    'user_id' => $banker->id, 'agent_id' => $banker->agent_id ?? null, 'member_account' => $banker->user_name,
                    'transaction_amount' => abs($bankerAmountChange), 'before_balance' => $bankerOldBalance,
                    'after_balance' => $banker->wallet->balanceFloat, 'banker' => 1,
                    'status' => $bankerAmountChange >= 0 ? 1 : 0, 'wager_code' => $wager_code,
                    'settled_status' => $bankerAmountChange >= 0 ? 'settled_win' : 'settled_loss',
                ]);
            } else {
                Log::info('ShanTransaction: Banker transaction already recorded in loop.', [
                    'banker_id' => $banker->user_name, 'wager_code' => $wager_code,
                ]);
            }

            $bankerResultExists = false;
            foreach ($results as &$res) {
                if ($res['player_id'] === $banker->user_name) {
                    $res['balance'] = $banker->wallet->balanceFloat;
                    $bankerResultExists = true;
                    break;
                }
            }
            if (! $bankerResultExists) {
                $results[] = [
                    'player_id' => $banker->user_name,
                    'balance' => $banker->wallet->balanceFloat,
                ];
            }

            DB::commit();

            $callback_url = $callback_url_base.'https://ponewine20x.xyz/api/shan/client/balance-update';

            $callbackPayload = [
                'wager_code' => $wager_code,
                'game_type_id' => 15,
                'players' => $callbackPlayers,
                'banker_balance' => $banker->wallet->balanceFloat,
                'timestamp' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ISO8601),
                'total_player_net' => $trueTotalPlayerNet,
                'banker_amount_change' => $bankerAmountChange,
            ];

            ksort($callbackPayload);
            $signature = hash_hmac('md5', json_encode($callbackPayload), $secret_key);
            $callbackPayload['signature'] = $signature;

            try {
                $client = new Client;
                $response = $client->post($callback_url, [
                    'json' => $callbackPayload,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'timeout' => 10,
                    'connect_timeout' => 5,
                ]);

                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();

                if ($statusCode >= 200 && $statusCode < 300) {
                    Log::info('ShanTransaction: Callback to client site successful', [
                        'callback_url' => $callback_url, 'status_code' => $statusCode,
                        'response_body' => $responseBody, 'wager_code' => $wager_code,
                    ]);
                } else {
                    Log::error('ShanTransaction: Callback to client site failed with non-2xx status', [
                        'callback_url' => $callback_url, 'status_code' => $statusCode,
                        'response_body' => $responseBody, 'payload' => $callbackPayload,
                        'wager_code' => $wager_code,
                    ]);
                }

            } catch (RequestException $e) {
                Log::error('ShanTransaction: Callback to client site failed (Guzzle RequestException)', [
                    'callback_url' => $callback_url, 'error' => $e->getMessage(),
                    'payload' => $callbackPayload, 'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response',
                    'wager_code' => $wager_code,
                ]);
            } catch (\Exception $e) {
                Log::error('ShanTransaction: Callback to client site failed (General Exception)', [
                    'callback_url' => $callback_url, 'error' => $e->getMessage(),
                    'payload' => $callbackPayload, 'wager_code' => $wager_code,
                ]);
            }

            Log::info('ShanTransaction: Transaction completed successfully (including callback attempt)', [
                'wager_code' => $wager_code, 'total_player_net' => $trueTotalPlayerNet,
                'banker_amount_change' => $bankerAmountChange, 'system_wallet_balance' => $banker->wallet->balanceFloat,
                'results' => $results,
            ]);

            return $this->success($results, 'Transaction Successful');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ShanTransaction: Transaction failed', [
                'error' => $e->getMessage(), 'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Transaction failed', $e->getMessage(), 500);
        }
    }
}
