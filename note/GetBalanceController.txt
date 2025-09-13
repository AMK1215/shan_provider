<?php

namespace App\Http\Controllers\Api\V1\gplus\Webhook;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Enums\SeamlessWalletCode;

class GetBalanceController extends Controller
{
    public function getBalance(Request $request)
    {
        // Validate request
        $request->validate([
            'batch_requests' => 'required|array',
            'operator_code' => 'required|string',
            'currency' => 'required|string',
            'sign' => 'required|string',
            'request_time' => 'required|integer',
        ]);

        // Signature check
        $secretKey = Config::get('seamless_key.secret_key');
        $expectedSign = md5(
            $request->operator_code .
            $request->request_time .
            'getbalance' .
            $secretKey
        );
        $isValidSign = strtolower($request->sign) === strtolower($expectedSign);

        // Allowed currencies
        $allowedCurrencies = ['IDR', 'IDR2', 'KRW2', 'MMK2', 'VND2', 'LAK2', 'KHR2'];
        $isValidCurrency = in_array($request->currency, $allowedCurrencies);

        $results = [];
        $specialCurrencies = ['IDR2', 'KRW2', 'MMK2', 'VND2', 'LAK2', 'KHR2'];
        foreach ($request->batch_requests as $req) {
            if (!$isValidSign) {
                $results[] = [
                    'member_account' => $req['member_account'],
                    'product_code' => $req['product_code'],
                    'balance' => '0.00',
                    'code' => \App\Enums\SeamlessWalletCode::InvalidSignature->value,
                    'message' => 'Incorrect Signature',
                ];
                continue;
            }

            if (!$isValidCurrency) {
                $results[] = [
                    'member_account' => $req['member_account'],
                    'product_code' => $req['product_code'],
                    'balance' => '0.00',
                    'code' => \App\Enums\SeamlessWalletCode::InternalServerError->value,
                    'message' => 'Invalid Currency',
                ];
                continue;
            }

            $user = User::where('user_name', $req['member_account'])->first();
            if ($user && $user->wallet) {
                $balance = $user->wallet->balanceFloat;
                if (in_array($request->currency, $specialCurrencies)) {
                    $balance = number_format($balance / 1000, 4, '.', '');
                } else {
                    $balance = number_format($balance, 2, '.', '');
                }
                $results[] = [
                    'member_account' => $req['member_account'],
                    'product_code' => $req['product_code'],
                    'balance' => $balance,
                    'code' => \App\Enums\SeamlessWalletCode::Success->value,
                    'message' => 'Success',
                ];
            } else {
                $results[] = [
                    'member_account' => $req['member_account'],
                    'product_code' => $req['product_code'],
                    'balance' => '0.00',
                    'code' => \App\Enums\SeamlessWalletCode::MemberNotExist->value,
                    'message' => 'Member not found',
                ];
            }
        }

        return ApiResponseService::success($results);
    }
} 