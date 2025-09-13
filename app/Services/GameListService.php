<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GameListService
{
    public static function getGameList(int $product_code, string $operator_code, ?string $game_type = null, int $offset = 0, ?int $size = null)
    {
        $secret_key = config('seamless_key.secret_key');
        $api_url = rtrim(config('seamless_key.api_url'), '/');
        $date = new \DateTime('now', new \DateTimeZone('Asia/Shanghai'));
        $request_time = $date->getTimestamp();
        $sign_str = $request_time.$secret_key.'gamelist'.$operator_code;
        $sign = md5($sign_str);

        // Debug logging for signature generation
        \Log::info('GameListService Signature Debug', [
            'request_time' => $request_time,
            'secret_key' => $secret_key,
            'operator_code' => $operator_code,
            'sign_str' => $sign_str,
            'sign' => $sign,
        ]);

        $params = [
            'product_code' => $product_code,
            'operator_code' => $operator_code,
            'sign' => $sign,
            'request_time' => $request_time,
            'offset' => $offset,
        ];
        if ($size !== null) {
            $params['size'] = $size;
        }
        if ($game_type !== null) {
            $params['game_type'] = $game_type;
        }

        $response = Http::get("{$api_url}/api/operators/provider-games", $params);

        return $response->json();
    }
}
