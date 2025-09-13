<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransferLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransferLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $relatedIds = $this->getDirectlyRelatedUserIds($user);

        $baseQuery = TransferLog::where(function ($q) use ($user, $relatedIds) {
            $q->where(function ($q2) use ($user, $relatedIds) {
                $q2->where('from_user_id', $user->id)
                    ->whereIn('to_user_id', $relatedIds);
            })
                ->orWhere(function ($q2) use ($user, $relatedIds) {
                    $q2->where('to_user_id', $user->id)
                        ->whereIn('from_user_id', $relatedIds);
                });
        });

        // All-time totals (unfiltered)
        $allTimeTotalDeposit = $baseQuery->clone()->where('type', 'top_up')->sum('amount');
        $allTimeTotalWithdraw = $baseQuery->clone()->where('type', 'withdraw')->sum('amount');
        $allTimeProfit = $allTimeTotalDeposit - $allTimeTotalWithdraw;

        // Query for the table, with all filters applied
        $tableQuery = $baseQuery->clone()->with(['fromUser', 'toUser']);
        if ($request->filled('type')) {
            $tableQuery->where('type', $request->type);
        }

        // Query for filtered info boxes, which is only filtered by date
        $dateFilteredQuery = $baseQuery->clone();
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = $request->date_from.' 00:00:00';
            $to = $request->date_to.' 23:59:59';
            $tableQuery->whereBetween('created_at', [$from, $to]);
            $dateFilteredQuery->whereBetween('created_at', [$from, $to]);
        }

        // Daily totals based on date-filtered query
        $dailyTotalDeposit = $dateFilteredQuery->clone()->where('type', 'top_up')->sum('amount');
        $dailyTotalWithdraw = $dateFilteredQuery->clone()->where('type', 'withdraw')->sum('amount');
        $dailyProfit = $dailyTotalDeposit - $dailyTotalWithdraw;

        $transferLogs = $tableQuery->latest()->paginate(20);

        return view('admin.transfer_logs.index', compact(
            'transferLogs',
            'dailyTotalDeposit',
            'dailyTotalWithdraw',
            'allTimeTotalDeposit',
            'allTimeTotalWithdraw',
            'dailyProfit',
            'allTimeProfit'
        ));
    }

    /**
     * Get only directly related user IDs according to the hierarchy:
     * Owner → Agent → SubAgent, Agent→Player, SubAgent→Player
     */
    private function getDirectlyRelatedUserIds(User $user): array
    {
        $relatedIds = [];
        if ($user->hasRole('Owner')) {
            // Owner: direct agents
            $relatedIds = $user->children()->whereHas('roles', function ($q) {
                $q->where('title', 'Agent');
            })->pluck('id')->toArray();
        } elseif ($user->hasRole('Agent')) {
            // Agent: direct players, direct subagents, parent owner
            $playerIds = $user->children()->whereHas('roles', function ($q) {
                $q->where('title', 'Player');
            })->pluck('id')->toArray();
            $subAgentIds = $user->children()->whereHas('roles', function ($q) {
                $q->where('title', 'SubAgent');
            })->pluck('id')->toArray();
            $parentOwnerId = $user->agent_id ? [$user->agent_id] : [];
            $relatedIds = array_merge($playerIds, $subAgentIds, $parentOwnerId);
        } elseif ($user->hasRole('SubAgent')) {
            // SubAgent: direct players, parent agent
            $playerIds = $user->children()->whereHas('roles', function ($q) {
                $q->where('title', 'Player');
            })->pluck('id')->toArray();
            $parentAgentId = $user->agent_id ? [$user->agent_id] : [];
            $relatedIds = array_merge($playerIds, $parentAgentId);
        }

        return array_unique($relatedIds);
    }

    public function PlayertransferLog($relatedUserId)
    {
        $user = Auth::user();
        $relatedUser = \App\Models\User::findOrFail($relatedUserId);

        // (Optionally) Only allow if related user is a child or parent, or else abort
        // ...classic check here...

        // Show all logs just between $user and $relatedUser
        $transferLogs = \App\Models\TransferLog::with(['fromUser', 'toUser'])
            ->where(function ($q) use ($user, $relatedUser) {
                $q->where('from_user_id', $user->id)
                    ->where('to_user_id', $relatedUser->id);
            })
            ->orWhere(function ($q) use ($user, $relatedUser) {
                $q->where('from_user_id', $relatedUser->id)
                    ->where('to_user_id', $user->id);
            })
            ->latest()
            ->get();

        return view('admin.transfer_logs.player_transfer_log_index', compact('transferLogs', 'relatedUser'));
    }
}
