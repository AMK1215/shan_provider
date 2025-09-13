<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Models\PlaceBet;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GameLogController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        $player = Auth::user();

        $query = PlaceBet::where('player_id', $player->id)
            ->where('wager_status', 'SETTLED')
            ->select(
                'game_name',
                DB::raw('COUNT(*) as spin_count'),
                DB::raw('SUM(bet_amount) as turnover'),
                DB::raw('SUM(prize_amount) as total_payout'),
                DB::raw('SUM(prize_amount) - SUM(bet_amount) as win_loss')
            )
            ->groupBy('game_name')
            ->orderBy('game_name');

        $from = $request->input('from');
        $to = $request->input('to');

        // If no dates are provided, default to today.
        if (! $from || ! $to) {
            $from = Carbon::today()->toDateString();
            $to = Carbon::today()->toDateString();
        }

        $query->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);

        $gameLogs = $query->get();

        $gameLogs->transform(function ($log) use ($from, $to) {
            $log->from = $from;
            $log->to = $to;

            return $log;
        });

        return $this->success($gameLogs, 'Player game logs retrieved successfully.');
    }
}
