<?php

namespace App\Enums;

enum TransactionName: string
{
    use HasLabelTrait;

    case CapitalDeposit = 'capital_deposit';
    case CapitalWithdraw = 'capital_withdraw';

    case Stake = 'stake';
    case Payout = 'payout';
    case Bonus = 'bonus';
    case Cancel = 'cancel';
    case Rollback = 'rollback';
    case BuyIn = 'buy_in';
    case BuyOut = 'buy_out';

    case Commission = 'commission';
    case Refund = 'refund';

    case CreditTransfer = 'credit_transfer';
    case DebitTransfer = 'debit_transfer';

    case CreditAdjustment = 'credit_adjustment';
    case DebitAdjustment = 'debit_adjustment';
    case Win = 'win';
    case Loss = 'loss';

    case Deposit = 'deposit';
    case Withdraw = 'withdraw';

    case Settled = 'settled';
    case Jackpot = 'jackpot';
    case Promo = 'promo';
    case Leaderboard = 'leaderboard';
    case Freebet = 'freebet';
    case PreserveRefund = 'preserve_refund';
    case TopUp = 'top_up';
    case TopUpReject = 'top_up_reject';
    case DigitBet = 'digit_bet';
    case GameLoss = 'game_loss';
    case GAME_BET = 'game_bet';
    case GameWin = 'game_win';
    case BankerDeposit = 'banker_deposit';
    case BankerWithdraw = 'banker_withdraw';
    case PoneWineBet = 'pone_wine_bet';
    case PoneWineWin = 'pone_wine_win';
    case PoneWineLoss = 'pone_wine_loss';
    case PoneWineCancel = 'pone_wine_cancel';
    case PoneWineRollback = 'pone_wine_rollback';
    case PoneWineBuyIn = 'pone_wine_buy_in';
    case PoneWineBuyOut = 'pone_wine_buy_out';
    case PoneWineBetInfo = 'pone_wine_bet_info';
    case PoneWinePlayerBet = 'pone_wine_player_bet';
    case PoneWineSettled = 'pone_wine_settled';

}
