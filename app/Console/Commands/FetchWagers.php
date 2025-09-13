<?php

namespace App\Console\Commands;

use App\Models\WagerList;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchWagers extends Command
{
    protected $signature = 'wagers:fetch';

    protected $description = 'Fetch wagers from Seamless API and store them in the database';

    public function handle()
    {
        Log::debug('Starting FetchWagers command...');

        $operator_code = config('seamless_key.agent_code');
        $secret_key = config('seamless_key.secret_key');
        $api_url = config('seamless_key.api_url');

        Log::debug('API Config', [
            'operator_code' => $operator_code,
            'api_url' => $api_url,
        ]);

        if (empty($operator_code) || empty($secret_key) || empty($api_url)) {
            Log::error('Seamless API configuration is missing');

            return;
        }

        $start = Carbon::now()->subMinutes(2);
        $end = $start->copy()->addMinutes(5);

        $startTimestamp = $start->timestamp * 1000;
        $endTimestamp = $end->timestamp * 1000;
        // $requestTime = Carbon::now()->timestamp * 1000;
        $request_time = now()->timestamp;
        $sign = md5($request_time.$secret_key.'getwagers'.$operator_code);

        Log::debug('Request Parameters', [
            'start' => $startTimestamp,
            'end' => $endTimestamp,
            'request_time' => $request_time,
            'sign' => $sign,
        ]);

        $url = "{$api_url}/api/operators/wagers";
        Log::debug("Sending GET request to: {$url}");

        $response = Http::get($url, [
            'operator_code' => $operator_code,
            'start' => $startTimestamp,
            'end' => $endTimestamp,
            'request_time' => $request_time,
            'sign' => $sign,
            'size' => 100,
        ]);

        Log::debug('API Response Status', [
            'status' => $response->status(),
            'successful' => $response->successful(),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Log::debug('API Response Data', [
                'data' => $data,
            ]);

            if (isset($data['wagers'])) {
                Log::info('Processing wagers...', [
                    'count' => count($data['wagers']),
                ]);

                foreach ($data['wagers'] as $wager) {
                    Log::debug('Processing wager', [
                        'wager_id' => $wager['id'] ?? 'N/A',
                        'member_account' => $wager['member_account'] ?? 'N/A',
                    ]);

                    WagerList::updateOrCreate(
                        ['id' => $wager['id']], // update if exists
                        [
                            'member_account' => $wager['member_account'] ?? '',
                            'round_id' => $wager['round_id'] ?? '',
                            'currency' => $wager['currency'] ?? '',
                            'provider_id' => $wager['provider_id'] ?? 0,
                            'provider_line_id' => $wager['provider_line_id'] ?? 0,
                            'game_type' => $wager['game_type'] ?? '',
                            'game_code' => $wager['game_code'] ?? '',
                            'valid_bet_amount' => $wager['valid_bet_amount'] ?? 0,
                            'bet_amount' => $wager['bet_amount'] ?? 0,
                            'prize_amount' => $wager['prize_amount'] ?? 0,
                            'status' => $wager['status'] ?? '',
                            'settled_at' => isset($wager['settled_at']) ? Carbon::createFromTimestampMs($wager['settled_at']) : null,
                            'created_at' => isset($wager['created_at']) ? Carbon::createFromTimestampMs($wager['created_at']) : now(),
                            'updated_at' => isset($wager['updated_at']) ? Carbon::createFromTimestampMs($wager['updated_at']) : now(),
                        ]
                    );
                }

                $this->info('Wagers fetched and stored successfully.');
            } else {
                Log::warning('No wagers found in the response.');
                $this->warn('No wagers found in the response.');
            }
        } else {
            Log::error('Failed to fetch wagers', ['response' => $response->body()]);
            $this->error('Failed to fetch wagers.');
        }

        Log::debug('FetchWagers command finished.');
    }
}
