<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShanApiService
{
    private string $apiUrl;

    private string $transactionKey;

    private string $agentCode;

    private string $currency;

    public function __construct()
    {
        $baseUrl = config('shan_key.api_url');
        $this->apiUrl = $baseUrl.'/transactions';
        $this->transactionKey = config('shan_key.transaction_key');
        $this->agentCode = config('shan_key.agent_code');
        $this->currency = config('shan_key.api_currency');
    }

    public function processTransaction(array $gameData): array
    {
        Log::info('Calling Shan API for transaction', [
            'game_data' => $gameData,
            'api_url' => $this->apiUrl,
        ]);

        try {
            $response = Http::withHeaders([
                'X-Transaction-Key' => $this->transactionKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $gameData);

            if (! $response->successful()) {
                Log::error('Shan API call failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'request_data' => $gameData,
                ]);
                throw new \RuntimeException('Failed to process transaction with Shan API');
            }

            Log::info('Shan API call successful', [
                'response' => $response->json(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error calling Shan API', [
                'error' => $e->getMessage(),
                'game_data' => $gameData,
            ]);
            throw $e;
        }
    }

    public function formatTransactionData(int $gameTypeId, array $players): array
    {
        return [
            'game_type_id' => $gameTypeId,
            'agent_code' => $this->agentCode,
            'currency' => $this->currency,
            'players' => array_map(function ($player) {
                return [
                    'player_id' => $player['player_id'],
                    'bet_amount' => $player['bet_amount'],
                    'amount_changed' => $player['amount_changed'],
                    'win_lose_status' => $player['win_lose_status'],
                ];
            }, $players),
        ];
    }
}
