<?php

namespace App\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';

    public static function fromAction(string $action, float $amount): self
    {
        $depositActions = [
            'SETTLED', 'JACKPOT', 'BONUS', 'PROMO', 'LEADERBOARD', 'FREEBET', 'PRESERVE_REFUND',
        ];
        $withdrawActions = [
            'BET', 'TIP', 'BET_PRESERVE',
        ];
        $adjustableActions = ['ADJUSTMENT', 'ROLLBACK', 'CANCEL'];

        if (in_array($action, $depositActions, true)) {
            return self::Deposit;
        }
        if (in_array($action, $withdrawActions, true)) {
            return self::Withdraw;
        }
        if (in_array($action, $adjustableActions, true)) {
            return $amount > 0 ? self::Deposit : self::Withdraw;
        }

        // Default fallback
        return $amount >= 0 ? self::Deposit : self::Withdraw;
    }
}
