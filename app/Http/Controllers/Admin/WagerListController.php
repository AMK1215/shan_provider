<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WagerListController extends Controller
{
    public function index()
    {
        return view('admin.wager_list.index');
    }

    public function fetch(Request $request)
    {
        $operator_code = config('seamless_key.agent_code');
        $secret_key = config('seamless_key.secret_key');
        $api_url = config('seamless_key.api_url');
        $start = strtotime($request->input('start_date')) * 1000;
        $end = strtotime($request->input('end_date')) * 1000;
        $offset = $request->input('offset', 0);
        $size = $request->input('size', 100);
        $request_time = now()->timestamp;
        $sign = md5($request_time.$secret_key.'getwagers'.$operator_code);
        $member_account = $request->input('member_account');
        $status = $request->input('status');

        $params = [
            'operator_code' => $operator_code,
            'start' => $start,
            'end' => $end,
            'offset' => $offset,
            'size' => $size,
            'sign' => $sign,
            'request_time' => $request_time,
        ];
        if ($member_account) {
            $params['member_account'] = $member_account;
        }
        if ($status) {
            $params['status'] = $status;
        }

        try {
            $response = Http::get($api_url.'/api/operators/wagers', $params);
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['wagers' => [], 'pagination' => ['size' => $size, 'total' => 0], 'error' => 'API error'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['wagers' => [], 'pagination' => ['size' => $size, 'total' => 0], 'error' => $e->getMessage()], 500);
        }
    }

    public function exportCsv(Request $request)
    {
        $operator_code = config('seamless_key.agent_code');
        $secret_key = config('seamless_key.secret_key');
        $api_url = config('seamless_key.api_url');
        $start = strtotime($request->input('start_date')) * 1000;
        $end = strtotime($request->input('end_date')) * 1000;
        $member_account = $request->input('member_account');
        $status = $request->input('status');
        $request_time = now()->timestamp;
        $sign = md5($request_time.$secret_key.'getwagers'.$operator_code);

        $params = [
            'operator_code' => $operator_code,
            'start' => $start,
            'end' => $end,
            'size' => 10000, // Export up to 10,000 records
            'sign' => $sign,
            'request_time' => $request_time,
        ];
        if ($member_account) {
            $params['member_account'] = $member_account;
        }
        if ($status) {
            $params['status'] = $status;
        }

        $response = Http::get($api_url.'/api/operators/wagers', $params);
        $data = $response->json();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wager_list.csv"',
        ];

        $columns = [
            'id', 'member_account', 'round_id', 'currency', 'provider_id', 'provider_line_id',
            'game_type', 'game_code', 'valid_bet_amount',
            'bet_amount', 'prize_amount', 'status', 'settled_at', 'created_at', 'updated_at',
        ];

        return new StreamedResponse(function () use ($data, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($data['wagers'] as $wager) {
                $row = [];
                foreach ($columns as $col) {
                    $val = $wager[$col] ?? '';
                    if (in_array($col, ['payload'])) {
                        $val = is_array($val) ? json_encode($val) : $val;
                    }
                    $row[] = $val;
                }
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function show($id)
    {
        $operator_code = config('seamless_key.agent_code');
        $secret_key = config('seamless_key.secret_key');
        $api_url = config('seamless_key.api_url');
        $request_time = now()->timestamp;
        $sign = md5($request_time.$secret_key.'getwager'.$operator_code);

        $params = [
            'operator_code' => $operator_code,
            'sign' => $sign,
            'request_time' => $request_time,
        ];

        $response = Http::get($api_url."/api/operators/wagers/{$id}", $params);
        $data = $response->json();
        $wager = $data['wager'] ?? null;

        return view('admin.wager_list.wager', compact('wager'));
    }

    public function gameHistory($wager_code)
    {
        $operator_code = config('seamless_key.agent_code');
        $secret_key = config('seamless_key.secret_key');
        $api_url = config('seamless_key.api_url');
        $request_time = now()->timestamp;
        $sign = md5($request_time.$secret_key.'productlist'.$operator_code);

        $params = [
            'operator_code' => $operator_code,
            'sign' => $sign,
            'request_time' => $request_time,
        ];

        $response = Http::get($api_url."/api/operators/{$wager_code}/game-history", $params);
        $data = $response->json();
        $content = $data['content'] ?? '';

        return view('admin.wager_list.game_history', compact('content'));
    }
}
