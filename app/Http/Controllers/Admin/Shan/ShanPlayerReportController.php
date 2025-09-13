<?php

namespace App\Http\Controllers\Admin\Shan;

use App\Http\Controllers\Controller;
use App\Models\Admin\ReportTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShanPlayerReportController extends Controller
{
    /**
     * Show the player report for the current user (owner or agent).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $reportsQuery = ReportTransaction::query();

        // Date filter (optional)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $reportsQuery->whereBetween('created_at', [
                $request->input('date_from').' 00:00:00',
                $request->input('date_to').' 23:59:59',
            ]);
        }

        // Member account filter (optional)
        if ($request->filled('member_account')) {
            $reportsQuery->where('member_account', $request->input('member_account'));
        }

        // OWNER: see all agents' and all players' reports
        if ($user->hasRole('Owner')) {
            // No restriction
        }
        // AGENT: see only his related player reports (all descendant players)
        elseif ($user->hasRole('Agent')) {
            $playerIds = $user->getAllDescendantPlayers()->pluck('id');
            $reportsQuery->whereIn('user_id', $playerIds);
        }
        // PLAYER: see only self
        else {
            $reportsQuery->where('user_id', $user->id);
        }

        // Pagination (10 per page, optional)
        $reports = $reportsQuery->orderByDesc('created_at')->paginate(10);

        return view('admin.shan.report_index', compact('reports'));
    }

    /**
     * Show grouped player reports by member_account.
     */
    public function groupByMemberAccount(Request $request)
    {
        $user = Auth::user();
        $reportsQuery = ReportTransaction::query();

        // Date filter (optional)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $reportsQuery->whereBetween('created_at', [
                $request->input('date_from').' 00:00:00',
                $request->input('date_to').' 23:59:59',
            ]);
        }

        // Member account filter (optional)
        if ($request->filled('member_account')) {
            $reportsQuery->where('member_account', $request->input('member_account'));
        }

        // OWNER: see all agents' and all players' reports
        if ($user->hasRole('Owner')) {
            // No restriction
        }
        // AGENT: see only his related player reports (all descendant players)
        elseif ($user->hasRole('Agent')) {
            $playerIds = $user->getAllDescendantPlayers()->pluck('id');
            $reportsQuery->whereIn('user_id', $playerIds);
        }
        // PLAYER: see only self
        else {
            $reportsQuery->where('user_id', $user->id);
        }

        // Group by member_account and get aggregated data
        $groupedReports = $reportsQuery
            ->selectRaw('
                member_account,
                COUNT(*) as total_transactions,
                SUM(transaction_amount) as total_transaction_amount,
                SUM(bet_amount) as total_bet_amount,
                SUM(valid_amount) as total_valid_amount,
                AVG(before_balance) as avg_before_balance,
                AVG(after_balance) as avg_after_balance,
                MIN(created_at) as first_transaction,
                MAX(created_at) as last_transaction
            ')
            ->groupBy('member_account')
            ->orderByDesc('total_transaction_amount')
            ->paginate(10);

        return view('admin.shan.report_grouped', compact('groupedReports'));
    }

    /**
     * Show individual player transaction details for a specific member account.
     */
    public function playerDetail(Request $request, $memberAccount)
    {
        $user = Auth::user();
        $reportsQuery = ReportTransaction::where('member_account', $memberAccount);

        // Date filter (optional)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $reportsQuery->whereBetween('created_at', [
                $request->input('date_from').' 00:00:00',
                $request->input('date_to').' 23:59:59',
            ]);
        }

        // OWNER: see all agents' and all players' reports
        if ($user->hasRole('Owner')) {
            // No restriction
        }
        // AGENT: see only his related player reports (all descendant players)
        elseif ($user->hasRole('Agent')) {
            $playerIds = $user->getAllDescendantPlayers()->pluck('id');
            $reportsQuery->whereIn('user_id', $playerIds);
        }
        // PLAYER: see only self
        else {
            $reportsQuery->where('user_id', $user->id);
        }

        // Get individual transactions for this member account
        $transactions = $reportsQuery->orderByDesc('created_at')->paginate(20);

        // Get summary statistics for this member account
        $summary = ReportTransaction::where('member_account', $memberAccount)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(transaction_amount) as total_transaction_amount,
                SUM(bet_amount) as total_bet_amount,
                SUM(valid_amount) as total_valid_amount,
                AVG(before_balance) as avg_before_balance,
                AVG(after_balance) as avg_after_balance,
                MIN(created_at) as first_transaction,
                MAX(created_at) as last_transaction
            ')
            ->first();

        return view('admin.shan.player_detail', compact('transactions', 'memberAccount', 'summary'));
    }


    /* 
    public function index(Request $request)
    {
        $user = Auth::user();
        $reportsQuery = ReportTransaction::query();

        // Date filter (optional)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $reportsQuery->whereBetween('created_at', [
                $request->input('date_from').' 00:00:00',
                $request->input('date_to').' 23:59:59',
            ]);
        }

        // Member account filter (optional)
        if ($request->filled('member_account')) {
            $reportsQuery->where('member_account', $request->input('member_account'));
        }

        // OWNER: see all agents' and all players' reports
        if ($user->hasRole('Owner')) {
            // No restriction
        }
        // AGENT: see only his related player reports (all descendant players)
        elseif ($user->hasRole('Agent')) {
            $playerIds = $user->getAllDescendantPlayers()->pluck('id');
            $reportsQuery->whereIn('user_id', $playerIds);
        }
        // PLAYER: see only self
        else {
            $reportsQuery->where('user_id', $user->id);
        }

        // Pagination (10 per page, optional)
        $reports = $reportsQuery->orderByDesc('created_at')->paginate(10);

        return view('admin.shan.report_index', compact('reports'));
    }
    */
}
