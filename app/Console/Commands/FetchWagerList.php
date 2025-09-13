<?php

namespace App\Console\Commands;

use App\Models\Wager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchWagerList extends Command
{
    protected $signature = 'wager:fetch';

    protected $description = 'Fetch wager list from API and store in DB';

    public function handle()
    {
        $operator_code = config('seamless_key.agent_code');
        $secret_key = config('seamless_key.secret_key');
        $api_url = config('seamless_key.api_url');

        $lastFetch = Cache::get('wager_last_fetch', now()->subMinutes(10)->timestamp * 1000);
        $now = now()->timestamp * 1000;
        Log::info('lastFetch: '.$lastFetch);
        Log::info('now: '.$now);

        $request_time = now()->timestamp;
        $sign = md5($request_time.$secret_key.'getwagers'.$operator_code);

        $params = [
            'operator_code' => $operator_code,
            'start' => $lastFetch,
            'end' => $now,
            'sign' => $sign,
            'request_time' => $request_time,
            'size' => 1000,
        ];

        $response = Http::get($api_url.'/api/operators/wagers', $params);
        $data = $response->json();

        if (! empty($data['wagers'])) {
            foreach ($data['wagers'] as $wager) {
                Wager::updateOrCreate(
                    ['id' => $wager['id']],
                    [
                        'code' => $wager['code'] ?? null,
                        'member_account' => $wager['member_account'],
                        'round_id' => $wager['round_id'],
                        'currency' => $wager['currency'],
                        'provider_id' => $wager['provider_id'],
                        'provider_line_id' => $wager['provider_line_id'],
                        'provider_product_id' => $wager['provider_product_id'] ?? null,
                        'provider_product_oid' => $wager['provider_product_oid'] ?? null,
                        'game_type' => $wager['game_type'],
                        'game_code' => $wager['game_code'],
                        'valid_bet_amount' => $wager['valid_bet_amount'] ?? 0,
                        'bet_amount' => $wager['bet_amount'] ?? 0,
                        'prize_amount' => $wager['prize_amount'] ?? 0,
                        'status' => $wager['status'],
                        'payload' => $wager['payload'] ?? null,
                        'settled_at' => $wager['settled_at'] ?? null,
                        'created_at_api' => $wager['created_at'],
                        'updated_at_api' => $wager['updated_at'],
                    ]
                );
            }
            $this->info('Wagers updated: '.count($data['wagers']));
        } else {
            $this->info('No new wagers found.');
        }

        // Only update last fetch if successful
        Cache::put('wager_last_fetch', $now);
    }
}
