<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ShanGetBalanceController extends Controller
{
    public function getBalance(Request $request)
    {
        // Validate request
        $request->validate([
            'batch_requests' => 'required|array',
            'operator_code' => 'required|string',
            'currency' => 'required|string', // Still required in the payload, but only MMK used
            'sign' => 'required|string',
            'request_time' => 'required|integer',
        ]);

        // Signature check
        $secretKey = Config::get('shan_key.secret_key');
        $expectedSign = md5(
            $request->operator_code.
            $request->request_time.
            'getbalance'.
            $secretKey
        );
        $isValidSign = strtolower($request->sign) === strtolower($expectedSign);

        $results = [];
        foreach ($request->batch_requests as $req) {
            if (! $isValidSign) {
                $results[] = [
                    'member_account' => $req['member_account'],
                    'product_code' => $req['product_code'],
                    'balance' => '0.00',
                    'code' => \App\Enums\SeamlessWalletCode::InvalidSignature->value,
                    'message' => 'Incorrect Signature',
                ];

                continue;
            }

            // Only MMK is allowed, but if you want to enforce, add a check (optional):
            if (strtoupper($request->currency) !== 'MMK') {
                $results[] = [
                    'member_account' => $req['member_account'],
                    'product_code' => $req['product_code'],
                    'balance' => '0.00',
                    'code' => \App\Enums\SeamlessWalletCode::InternalServerError->value,
                    'message' => 'Invalid Currency, only MMK allowed',
                ];

                continue;
            }

            $user = User::with('wallet')->where('user_name', $req['member_account'])->first();
            if ($user && $user->wallet) {
                $balance = round($user->wallet->balanceFloat, 2);
                $results[] = [
                    'member_account' => $req['member_account'],
                    'product_code' => $req['product_code'],
                    'balance' => (float) $balance,
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
