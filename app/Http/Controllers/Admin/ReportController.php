<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\PlaceBet;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $agent = Auth::user();
        // $agent = $this->getAgent() ?? Auth::user();

        $report = $this->buildQuery($request, $agent);

        $totalstake = $report->sum('stake_count');
        $totalBetAmt = $report->sum('total_bet');
        $totalWinAmt = $report->sum('total_win');

        $total = [
            'totalstake' => $totalstake,
            'totalBetAmt' => $totalBetAmt,
            'totalWinAmt' => $totalWinAmt,
        ];

        return view('admin.report.index', compact('report', 'total'));
    }

    public function getReportDetails(Request $request, $member_account)
    {
        $player = User::where('user_name', $member_account)->first();
        if (! $player) {
            abort(404, 'Player not found');
        }

        $details = $this->getPlayerDetails($member_account, $request);
        $productTypes = Product::where('status', 1)->get();

        return view('admin.report.detail', compact('details', 'productTypes', 'member_account'));
    }

    private function getAgent()
    {
        $user = Auth::user();

        return $user;
    }

    private function buildQuery(Request $request, $agent)
    {
        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

        $query = PlaceBet::query()
            ->select(
                'place_bets.member_account',
                'agent_user.user_name as agent_name',
                DB::raw("COUNT(CASE WHEN wager_status = 'SETTLED' THEN 1 END) as stake_count"),
                DB::raw("
                SUM(CASE
                    WHEN wager_status = 'SETTLED' THEN
                        CASE
                            WHEN place_bets.currency = 'MMK2' THEN COALESCE(bet_amount, amount, 0) * 1000
                            ELSE COALESCE(bet_amount, amount, 0)
                        END
                    ELSE 0
                END) as total_bet
            "),
                DB::raw("
                SUM(CASE
                    WHEN wager_status = 'SETTLED' THEN
                        CASE
                            WHEN place_bets.currency = 'MMK2' THEN prize_amount * 1000
                            ELSE prize_amount
                        END
                    ELSE 0
                END) as total_win
            ")
            )
            ->leftJoin('users as agent_user', 'place_bets.player_agent_id', '=', 'agent_user.id')
            ->whereBetween('place_bets.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        // Apply agent/user hierarchy filtering based on role
        if ($agent->type === UserType::Owner->value) {
            $query->whereNotNull('place_bets.player_agent_id');
        } elseif ($agent->type === UserType::Agent->value) {
            // Agent should only see players directly under them.
            $playerIds = User::where('agent_id', $agent->id)
                ->where('type', UserType::Player)
                ->pluck('id');
            $query->whereIn('place_bets.player_id', $playerIds);
        } elseif ($agent->type === UserType::SubAgent->value) {
            $playerIds = $agent->children()->where('type', UserType::Player)->pluck('id');
            $query->whereIn('place_bets.player_id', $playerIds);
        } elseif ($agent->type === UserType::Player->value) {
            $query->where('place_bets.player_id', $agent->id);
        }

        if ($request->filled('member_account')) {
            $query->where('member_account', $request->member_account);
        }

        return $query->groupBy('place_bets.member_account', 'agent_user.user_name')->get();
    }

    // private function buildQuery(Request $request, $agent)
    // {
    //     $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
    //     $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

    //     $query = PlaceBet::query()
    //         ->select(
    //             'place_bets.member_account',
    //             'agent_user.user_name as agent_name',
    //             DB::raw("COUNT(CASE WHEN action = 'BET' THEN 1 END) as stake_count"),
    //             DB::raw("SUM(CASE WHEN action = 'BET' THEN COALESCE(bet_amount, amount, 0) ELSE 0 END) as total_bet"),
    //             DB::raw("SUM(CASE WHEN wager_status = 'SETTLED' THEN prize_amount ELSE 0 END) as total_win")
    //         )
    //         ->leftJoin('users as agent_user', 'place_bets.player_agent_id', '=', 'agent_user.id')
    //         ->whereBetween('place_bets.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

    //     // Apply agent/user hierarchy filtering based on role
    //     if ($agent->type === UserType::Owner->value) {
    //         $query->whereNotNull('place_bets.player_agent_id');
    //     } elseif ($agent->type === UserType::Agent->value) {
    //         // Agent should only see players directly under them.
    //         $playerIds = User::where('agent_id', $agent->id)
    //             ->where('type', UserType::Player)
    //             ->pluck('id');
    //         $query->whereIn('place_bets.player_id', $playerIds);
    //     } elseif ($agent->type === UserType::SubAgent->value) {
    //         $playerIds = $agent->children()->where('type', UserType::Player)->pluck('id');
    //         $query->whereIn('place_bets.player_id', $playerIds);
    //     } elseif ($agent->type === UserType::Player->value) {
    //         $query->where('place_bets.player_id', $agent->id);
    //     }

    //     if ($request->filled('member_account')) {
    //         $query->where('member_account', $request->member_account);
    //     }

    //     return $query->groupBy('place_bets.member_account', 'agent_user.user_name')->get();
    // }

    private function getPlayerDetails($member_account, $request)
    {
        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

        return PlaceBet::where('member_account', $member_account)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function show(Request $request, $member_account)
    {
        $query = PlaceBet::query()->where('member_account', $member_account);

        // Get the player user record
        $player = User::where('user_name', $member_account)->first();
        if (! $player) {
            abort(404, 'Player not found');
        }

        $bets = $query->orderByDesc('created_at')->paginate(50);

        return view('admin.report.show', compact('bets', 'member_account'));
    }

    public function dailyWinLossReport(Request $request)
    {
        $agent = Auth::user();
        $playerIds = $agent->getAllDescendantPlayers()->pluck('id');

        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $dailyReports = PlaceBet::whereIn('player_id', $playerIds)
            ->where('place_bets.wager_status', 'SETTLED')
            ->whereDate('place_bets.created_at', $date)
            ->join('users', 'place_bets.player_id', '=', 'users.id')
            ->select(
                'users.user_name',
                'place_bets.player_id',
                DB::raw('SUM(CASE WHEN place_bets.currency = \'MMK2\' THEN place_bets.bet_amount * 1000 ELSE place_bets.bet_amount END) as total_turnover'),
                DB::raw('SUM(CASE WHEN place_bets.currency = \'MMK2\' THEN place_bets.prize_amount * 1000 ELSE place_bets.prize_amount END) as total_payout')
            )
            ->groupBy('users.user_name', 'place_bets.player_id')
            ->get();

        $totalTurnover = $dailyReports->sum('total_turnover');
        $totalPayout = $dailyReports->sum('total_payout');
        $totalWinLoss = $totalPayout - $totalTurnover;

        return view('admin.reports.daily_win_loss', compact('dailyReports', 'date', 'totalTurnover', 'totalPayout', 'totalWinLoss'));
    }

    // public function dailyWinLossReport(Request $request)
    // {
    //     $agent = Auth::user();
    //     $playerIds = $agent->getAllDescendantPlayers()->pluck('id');

    //     $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

    //     $dailyReports = PlaceBet::whereIn('player_id', $playerIds)
    //         ->where('place_bets.wager_status', 'SETTLED')
    //         ->whereDate('place_bets.created_at', $date)
    //         ->join('users', 'place_bets.player_id', '=', 'users.id')
    //         ->select(
    //             'users.user_name',
    //             'place_bets.player_id',
    //             DB::raw('SUM(place_bets.bet_amount) as total_turnover'),
    //             DB::raw('SUM(place_bets.prize_amount) as total_payout')
    //         )
    //         ->groupBy('users.user_name', 'place_bets.player_id')
    //         ->get();

    //     $totalTurnover = $dailyReports->sum('total_turnover');
    //     $totalPayout = $dailyReports->sum('total_payout');
    //     $totalWinLoss = $totalPayout - $totalTurnover;

    //     return view('admin.reports.daily_win_loss', compact('dailyReports', 'date', 'totalTurnover', 'totalPayout', 'totalWinLoss'));
    // }

    public function gameLogReport(Request $request)
    {
        $agent = Auth::user();
        $playerIds = $agent->getAllDescendantPlayers()->pluck('id');

        $query = PlaceBet::whereIn('player_id', $playerIds)
            ->where('wager_status', 'SETTLED')
            ->select(
                'game_name',
                DB::raw('COUNT(*) as spin_count'),
                DB::raw('SUM(bet_amount) as turnover'),
                DB::raw('SUM(prize_amount) - SUM(bet_amount) as win_loss')
            )
            ->groupBy('game_name')
            ->orderBy('game_name');

        if ($request->has('from') && $request->has('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        } else {
            // Default to today
            $query->whereDate('created_at', Carbon::today());
        }

        $gameLogs = $query->get();
        $from = $request->from ?? Carbon::today()->toDateString();
        $to = $request->to ?? Carbon::today()->toDateString();

        return view('admin.report.game_log_report', compact('gameLogs', 'from', 'to'));
    }
}
