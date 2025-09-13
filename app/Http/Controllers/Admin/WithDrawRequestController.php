<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithDrawRequest;
use App\Services\WalletService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WithDrawRequestController extends Controller
{
    protected const SUB_AGENT_ROLE = 'SubAgent';

    public function index(Request $request)
    {
        $user = Auth::user();
        $isSubAgent = $user->hasRole(self::SUB_AGENT_ROLE);
        $agent = $isSubAgent ? $user->agent : $user;

        $sub_acc_id = $user->agent;

        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

        $withdraws = WithDrawRequest::with(['user', 'paymentType'])
            ->where('agent_id', $agent->id)
            ->when($isSubAgent, function ($query) use ($sub_acc_id) {
                $query->where('agent_id', $sub_acc_id->id);
            })
            ->when($request->filled('status') && $request->input('status') !== 'all', function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('id', 'desc')
            ->get();

        $totalWithdraws = $withdraws->sum('amount');

        return view('admin.withdraw_request.index', compact('withdraws', 'totalWithdraws', 'isSubAgent'));
    }

    public function statusChangeIndex(Request $request, WithDrawRequest $withdraw)
    {
        Log::info('Withdraw status change started', [
            'withdraw_id' => $withdraw->id,
            'request_status' => $request->status,
            'request_player' => $request->player,
            'user_id' => Auth::id(),
        ]);

        $user = Auth::user();
        $isSubAgent = $user->hasRole(self::SUB_AGENT_ROLE);
        $agent = $isSubAgent ? $user->agent : $user;
        $player = User::find($request->player);

        Log::info('User and agent info', [
            'user_id' => $user->id,
            'user_name' => $user->user_name,
            'is_sub_agent' => $isSubAgent,
            'agent_id' => $agent ? $agent->id : null,
            'agent_name' => $agent ? $agent->user_name : null,
            'player_id' => $player ? $player->id : null,
            'player_name' => $player ? $player->user_name : null,
            'player_balance' => $player ? $player->balanceFloat : null,
        ]);

        if ($request->status == 1 && $player->balanceFloat < $request->amount) {
            Log::warning('Insufficient balance for withdraw', [
                'player_balance' => $player->balanceFloat,
                'request_amount' => $request->amount,
                'withdraw_id' => $withdraw->id,
            ]);

            return redirect()->back()->with('error', 'Insufficient Balance!');
        }

        $note = 'Withdraw request approved by '.$user->user_name.' on '.Carbon::now()->timezone('Asia/Yangon')->format('d-m-Y H:i:s');

        Log::info('Updating withdraw request', [
            'withdraw_id' => $withdraw->id,
            'status' => $request->status,
            'sub_agent_id' => $user->id,
            'sub_agent_name' => $user->user_name,
            'note' => $note,
        ]);

        $withdraw->update([
            'status' => $request->status,
            'sub_agent_id' => $user->id,
            'sub_agent_name' => $user->user_name,
            'note' => $note,
        ]);

        if ($request->status == 1) {
            Log::info('Processing withdraw approval', [
                'withdraw_id' => $withdraw->id,
                'amount' => $request->amount,
                'player_old_balance' => $player->balanceFloat,
            ]);

            $old_balance = $player->balanceFloat;

            try {
                app(WalletService::class)->transfer($player, $agent, $request->amount,
                    TransactionName::Withdraw, [
                        'old_balance' => $old_balance,
                        'new_balance' => $old_balance - $request->amount,
                    ]);

                Log::info('Wallet transfer completed successfully', [
                    'withdraw_id' => $withdraw->id,
                    'transfer_amount' => $request->amount,
                    'player_old_balance' => $old_balance,
                    'player_new_balance' => $old_balance - $request->amount,
                ]);
            } catch (\Exception $e) {
                Log::error('Wallet transfer failed', [
                    'withdraw_id' => $withdraw->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            try {
                \App\Models\TransferLog::create([
                    'from_user_id' => $player->id,
                    'to_user_id' => $agent->id,
                    'sub_agent_id' => $isSubAgent ? $user->id : null,
                    'sub_agent_name' => $isSubAgent ? $user->user_name : null,
                    'amount' => $request->amount,
                    'type' => 'withdraw',
                    'description' => 'Withdraw request '.$withdraw->id.' approved by '.$user->user_name,
                    'meta' => [
                        'withdraw_request_id' => $withdraw->id,
                        'player_old_balance' => $old_balance,
                        'player_new_balance' => $old_balance - $request->amount,
                    ],
                ]);

                Log::info('Transfer log created successfully', [
                    'withdraw_id' => $withdraw->id,
                    'transfer_log_created' => true,
                ]);
            } catch (\Exception $e) {
                Log::error('Transfer log creation failed', [
                    'withdraw_id' => $withdraw->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        Log::info('Withdraw status change completed successfully', [
            'withdraw_id' => $withdraw->id,
            'final_status' => $request->status,
        ]);

        return redirect()->route('admin.agent.withdraw')->with('success', 'Withdraw status updated successfully!');
    }

    public function statusChangeReject(Request $request, WithDrawRequest $withdraw)
    {
        $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $user = Auth::user();
        $isSubAgent = $user->hasRole(self::SUB_AGENT_ROLE);
        $agent = $isSubAgent ? $user->agent : $user;

        try {
            $note = 'Withdraw request rejected by '.$user->user_name.' on '.Carbon::now()->timezone('Asia/Yangon')->format('d-m-Y H:i:s');

            $withdraw->update([
                'status' => $request->status,
                'sub_agent_id' => $user->id,
                'sub_agent_name' => $user->user_name,
                'note' => $note,
            ]);

            \App\Models\TransferLog::create([
                'from_user_id' => $withdraw->user_id,
                'to_user_id' => $agent->id,
                'sub_agent_id' => $isSubAgent ? $user->id : null,
                'sub_agent_name' => $isSubAgent ? $user->user_name : null,
                'amount' => $withdraw->amount,
                'type' => 'withdraw',
                'description' => 'Withdraw request '.$withdraw->id.' rejected by '.$user->user_name,
                'meta' => [
                    'withdraw_request_id' => $withdraw->id,
                    'status' => 'rejected',
                ],
            ]);

            return redirect()->route('admin.agent.withdraw')->with('success', 'Withdraw status updated successfully!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function isExistingAgent($userId)
    {
        $user = User::find($userId);

        return $user && $user->hasRole(self::SUB_AGENT_ROLE) ? $user->parent : null;
    }

    private function getAgent()
    {
        return $this->isExistingAgent(Auth::id());
    }

    // log withdraw request
    public function WithdrawShowLog(WithDrawRequest $withdraw)
    {
        return view('admin.withdraw_request.view', ['withdraw' => $withdraw]);
    }
}
