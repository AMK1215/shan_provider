<?php

namespace App\Services\Slot;

use App\Enums\SeamlessWalletCode;

class BalanceResponseService
{
    /**
     * Build a successful batch balance response
     *
     * @param  array  $balanceData  Array of member balance data
     */
    public static function buildSuccessResponse(array $balanceData): array
    {
        return [
            'data' => array_map(function ($item) {
                return [
                    'member_account' => $item['member_account'],
                    'product_code' => $item['product_code'],
                    'balance' => $item['balance'],
                    'code' => SeamlessWalletCode::SUCCESS->value,
                    'message' => '',
                ];
            }, $balanceData),
        ];
    }

    /**
     * Build an error response for a specific member
     */
    public static function buildMemberErrorResponse(
        string $memberAccount,
        int $productCode,
        SeamlessWalletCode $responseCode,
        ?string $message = null
    ): array {
        return [
            'member_account' => $memberAccount,
            'product_code' => $productCode,
            'balance' => 0,
            'code' => $responseCode->value,
            'message' => $message ?? $responseCode->name,
        ];
    }

    /**
     * Build a global error response
     */
    public static function buildGlobalErrorResponse(
        SeamlessWalletCode $responseCode,
        ?string $message = null
    ): array {
        return [
            'error' => [
                'code' => $responseCode->value,
                'message' => $message ?? $responseCode->name,
            ],
        ];
    }

    /**
     * Build a single member balance response
     */
    public static function buildSingleMemberResponse(
        string $memberAccount,
        int $productCode,
        float $balance,
        float $beforeBalance
    ): array {
        return [
            'member_account' => $memberAccount,
            'product_code' => $productCode,
            'balance' => $balance,
            'before_balance' => $beforeBalance,
            'code' => SeamlessWalletCode::SUCCESS->value,
            'message' => '',
        ];
    }
}
