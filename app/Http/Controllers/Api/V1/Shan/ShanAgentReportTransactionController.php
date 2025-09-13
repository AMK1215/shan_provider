<?php

namespace App\Http\Controllers\Api\V1\Shan;

use App\Http\Controllers\Controller;
use App\Models\Admin\ReportTransaction;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShanAgentReportTransactionController extends Controller
{
    use HttpResponses;

    /**
     * Get report transactions grouped by agent_id and member_account
     * Requires Agent_Code for filtering
     */
    public function getReportTransactions(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'agent_code' => 'required|string|exists:users,shan_agent_code',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'member_account' => 'nullable|string',
                'group_by' => 'nullable|in:agent_id,member_account,both',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    $validator->errors(),
                    'Validation failed',
                    422
                );
            }

            $agentCode = $request->input('agent_code');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $memberAccount = $request->input('member_account');
            $groupBy = $request->input('group_by', 'both');

            // Get the agent by agent_code (shan_agent_code)
            $agent = User::where('shan_agent_code', $agentCode)
                        ->where('type', 20) // Ensure it's an agent
                        ->first();

            if (!$agent) {
                return $this->error(
                    'Agent not found',
                    'Agent with code ' . $agentCode . ' not found or is not a valid agent',
                    404
                );
            }

            // Build the base query
            $query = ReportTransaction::query();

            // Filter by agent's players (all descendant players)
            $playerIds = $agent->getAllDescendantPlayers()->pluck('id');
            $query->whereIn('user_id', $playerIds);

            // Date filter
            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59',
                ]);
            }

            // Member account filter
            if ($memberAccount) {
                $query->where('member_account', $memberAccount);
            }

            // Group by logic
            if ($groupBy === 'agent_id') {
                $results = $this->getGroupedByAgent($query);
            } elseif ($groupBy === 'member_account') {
                $results = $this->getGroupedByMemberAccount($query);
            } else {
                // Default: group by both agent_id and member_account
                $results = $this->getGroupedByBoth($query);
            }

            Log::info('ShanAgentReportTransaction: Report generated', [
                'agent_code' => $agentCode,
                'agent_id' => $agent->id,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'member_account' => $memberAccount,
                'group_by' => $groupBy,
                'total_records' => count($results)
            ]);

            return $this->success([
                'agent_info' => [
                    'agent_id' => $agent->id,
                    'agent_code' => $agent->shan_agent_code,
                    'agent_name' => $agent->name,
                ],
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'member_account' => $memberAccount,
                    'group_by' => $groupBy,
                ],
                'report_data' => $results,
                'summary' => $this->getSummary($results, $groupBy)
            ], 'Report transactions retrieved successfully');

        } catch (\Exception $e) {
            Log::error('ShanAgentReportTransaction: Error occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return $this->error(
                'Internal server error',
                'An error occurred while processing the request',
                500
            );
        }
    }

    /**
     * Group by agent_id only
     */
    private function getGroupedByAgent($query)
    {
        return $query->selectRaw('
                agent_id,
                COUNT(*) as total_transactions,
                SUM(transaction_amount) as total_transaction_amount,
                SUM(bet_amount) as total_bet_amount,
                SUM(valid_amount) as total_valid_amount,
                AVG(before_balance) as avg_before_balance,
                AVG(after_balance) as avg_after_balance,
                MIN(created_at) as first_transaction,
                MAX(created_at) as last_transaction
            ')
            ->groupBy('agent_id')
            ->with('agent:id,user_name,name')
            ->orderByDesc('total_transaction_amount')
            ->get();
    }

    /**
     * Group by member_account only
     */
    private function getGroupedByMemberAccount($query)
    {
        return $query->selectRaw('
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
            ->get();
    }

    /**
     * Group by both agent_id and member_account
     */
    private function getGroupedByBoth($query)
    {
        return $query->selectRaw('
                agent_id,
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
            ->groupBy('agent_id', 'member_account')
            ->with('agent:id,user_name,name')
            ->orderByDesc('total_transaction_amount')
            ->get();
    }

    /**
     * Get summary statistics
     */
    private function getSummary($results, $groupBy)
    {
        $totalTransactions = $results->sum('total_transactions');
        $totalTransactionAmount = $results->sum('total_transaction_amount');
        $totalBetAmount = $results->sum('total_bet_amount');
        $totalValidAmount = $results->sum('total_valid_amount');

        $summary = [
            'total_groups' => $results->count(),
            'total_transactions' => $totalTransactions,
            'total_transaction_amount' => number_format($totalTransactionAmount, 2),
            'total_bet_amount' => number_format($totalBetAmount, 2),
            'total_valid_amount' => number_format($totalValidAmount, 2),
        ];

        if ($groupBy === 'agent_id' || $groupBy === 'both') {
            $summary['unique_agents'] = $results->pluck('agent_id')->unique()->count();
        }

        if ($groupBy === 'member_account' || $groupBy === 'both') {
            $summary['unique_members'] = $results->pluck('member_account')->unique()->count();
        }

        return $summary;
    }

    /**
     * Get individual transactions for a specific member account
     */
    public function getMemberTransactions(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'agent_code' => 'required|string|exists:users,shan_agent_code',
                'member_account' => 'required|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    $validator->errors(),
                    'Validation failed',
                    422
                );
            }

            $agentCode = $request->input('agent_code');
            $memberAccount = $request->input('member_account');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $limit = $request->input('limit', 50);

            // Get the agent
            $agent = User::where('shan_agent_code', $agentCode)
                        ->where('type', 20)
                        ->first();

            if (!$agent) {
                return $this->error(
                    'Agent not found',
                    'Agent with code ' . $agentCode . ' not found',
                    404
                );
            }

            // Build query for specific member account
            $query = ReportTransaction::where('member_account', $memberAccount);

            // Filter by agent's players
            $playerIds = $agent->getAllDescendantPlayers()->pluck('id');
            $query->whereIn('user_id', $playerIds);

            // Date filter
            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59',
                ]);
            }

            $transactions = $query->with('agent:id,user_name,name')
                                ->orderByDesc('created_at')
                                ->limit($limit)
                                ->get();

            return $this->success([
                'agent_info' => [
                    'agent_id' => $agent->id,
                    'agent_code' => $agent->shan_agent_code,
                    'agent_name' => $agent->name,
                ],
                'member_account' => $memberAccount,
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'limit' => $limit,
                ],
                'transactions' => $transactions,
                'total_found' => $transactions->count()
            ], 'Member transactions retrieved successfully');

        } catch (\Exception $e) {
            Log::error('ShanAgentReportTransaction: Error in getMemberTransactions', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->error(
                'Internal server error',
                'An error occurred while processing the request',
                500
            );
        }
    }




    public function getPlayerDailyTransactions(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'agent_code' => 'required|string|exists:users,shan_agent_code',
                'member_account' => 'required|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    $validator->errors(),
                    'Validation failed',
                    422
                );
            }

            $agentCode = $request->input('agent_code');
            $memberAccount = $request->input('member_account');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $limit = $request->input('limit', 50);

            // Get the agent
            $agent = User::where('shan_agent_code', $agentCode)
                        ->where('type', 20)
                        ->first();

            if (!$agent) {
                return $this->error(
                    'Agent not found',
                    'Agent with code ' . $agentCode . ' not found',
                    404
                );
            }

            // Build query for specific member account
            $query = ReportTransaction::where('member_account', $memberAccount);

            // Filter by agent's players
            $playerIds = $agent->getAllDescendantPlayers()->pluck('id');
            $query->whereIn('user_id', $playerIds);

            // Date filter
            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59',
                ]);
            }

            $transactions = $query->with('agent:id,user_name,name')
                                ->orderByDesc('created_at')
                                ->limit($limit)
                                ->get();

            return $this->success([
                'agent_info' => [
                    'agent_id' => $agent->id,
                    'agent_code' => $agent->shan_agent_code,
                    'agent_name' => $agent->name,
                ],
                'member_account' => $memberAccount,
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'limit' => $limit,
                ],
                'transactions' => $transactions,
                'total_found' => $transactions->count()
            ], 'Member transactions retrieved successfully');

        } catch (\Exception $e) {
            Log::error('ShanAgentReportTransaction: Error in getMemberTransactions', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->error(
                'Internal server error',
                'An error occurred while processing the request',
                500
            );
        }
    }

    /**
     * Get today's transactions for a specific member account
     */
    public function getTodayTransactions(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'agent_code' => 'required|string|exists:users,shan_agent_code',
                'member_account' => 'required|string',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    $validator->errors(),
                    'Validation failed',
                    422
                );
            }

            $agentCode = $request->input('agent_code');
            $memberAccount = $request->input('member_account');
            $limit = $request->input('limit', 50);

            // Get the agent
            $agent = User::where('shan_agent_code', $agentCode)
                        ->where('type', 20)
                        ->first();

            if (!$agent) {
                return $this->error(
                    'Agent not found',
                    'Agent with code ' . $agentCode . ' not found',
                    404
                );
            }

            // Get today's date range (Asia/Yangon timezone)
            $today = now('Asia/Yangon')->toDateString();
            $startOfDay = $today . ' 00:00:00';
            $endOfDay = $today . ' 23:59:59';

            // Build query for specific member account - TODAY ONLY
            $query = ReportTransaction::where('member_account', $memberAccount)
                                    ->whereBetween('created_at', [$startOfDay, $endOfDay]);

            // Filter by agent's players
            $playerIds = $agent->getAllDescendantPlayers()->pluck('id');
            $query->whereIn('user_id', $playerIds);

            $transactions = $query->with('agent:id,user_name,name')
                                ->orderByDesc('created_at')
                                ->limit($limit)
                                ->get();

            // Get today's summary statistics
            $todaySummary = ReportTransaction::where('member_account', $memberAccount)
                                            ->whereIn('user_id', $playerIds)
                                            ->whereBetween('created_at', [$startOfDay, $endOfDay])
                                            ->selectRaw('
                                                COUNT(*) as total_transactions,
                                                SUM(transaction_amount) as total_transaction_amount,
                                                SUM(bet_amount) as total_bet_amount,
                                                SUM(valid_amount) as total_valid_amount,
                                                AVG(before_balance) as avg_before_balance,
                                                AVG(after_balance) as avg_after_balance
                                            ')
                                            ->first();

            return $this->success([
                'agent_info' => [
                    'agent_id' => $agent->id,
                    'agent_code' => $agent->shan_agent_code,
                    'agent_name' => $agent->name,
                ],
                'member_account' => $memberAccount,
                'date_info' => [
                    'date' => $today,
                    'timezone' => 'Asia/Yangon',
                    'start_time' => $startOfDay,
                    'end_time' => $endOfDay,
                ],
                'filters' => [
                    'limit' => $limit,
                ],
                'today_summary' => [
                    'total_transactions' => $todaySummary->total_transactions ?? 0,
                    'total_transaction_amount' => number_format($todaySummary->total_transaction_amount ?? 0, 2),
                    'total_bet_amount' => number_format($todaySummary->total_bet_amount ?? 0, 2),
                    'total_valid_amount' => number_format($todaySummary->total_valid_amount ?? 0, 2),
                    'avg_before_balance' => number_format($todaySummary->avg_before_balance ?? 0, 2),
                    'avg_after_balance' => number_format($todaySummary->avg_after_balance ?? 0, 2),
                ],
                'transactions' => $transactions,
                'total_found' => $transactions->count()
            ], 'Today\'s transactions retrieved successfully');

        } catch (\Exception $e) {
            Log::error('ShanAgentReportTransaction: Error in getTodayTransactions', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->error(
                'Internal server error',
                'An error occurred while processing the request',
                500
            );
        }
    }
}
