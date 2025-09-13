<?php

namespace App\Enums;

enum WagerStatus: string
{
    case Bet = 'BET'; // The bet is in the betting stage
    case Bonus = 'BONUS'; // Multiple prizes are distributed in the same round
    case Settled = 'SETTLED'; // Bet settled
    case Resettled = 'RESETTLED'; // Bets have been resettled
    case Void = 'VOID'; // Bets are void
}
