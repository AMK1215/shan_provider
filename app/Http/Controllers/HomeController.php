<?php

namespace App\Http\Controllers;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Models\Admin\UserLog;
use App\Models\Transaction;
use App\Models\TransferLog;
use App\Models\User;
use App\Services\WalletService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $roleTitle = $user->roles->pluck('title')->first(); // 'Owner', 'Agent', 'SubAgent', 'Player'

        $totalWinlose = 0;
        $todayWinlose = 0;
        $todayDeposit = 0;
        $todayWithdraw = 0;
        $playerBalance = 0;

        // Fetch balance from all children users (based on agent_id)
        $totalBalance = DB::table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('wallets', 'wallets.holder_id', '=', 'users.id')
            ->when(in_array($roleTitle, ['Owner', 'Agent', 'SubAgent']), function ($query) use ($user) {
                return $query->where('users.agent_id', $user->id);
            })
            ->select(DB::raw('COALESCE(SUM(wallets.balance), 0) as balance'))
            ->first();

        // Agent-specific metrics
        if (in_array($roleTitle, ['Agent', 'SubAgent'])) {
            $todayWinlose = $this->getWinLose($user->id, true);
            $totalWinlose = $this->getWinLose($user->id);
            $todayDeposit = $this->fetchTotalTransactions($user->id, 'deposit');
            $todayWithdraw = $this->fetchTotalTransactions($user->id, 'withdraw');
        }

        // Player balances (only for roles above them)
        if (in_array($roleTitle, ['Owner', 'Agent', 'SubAgent'])) {
            $childType = UserType::childUserType(UserType::from($user->type));
            $playerBalance = DB::table('users')
                ->join('wallets', 'wallets.holder_id', '=', 'users.id')
                ->where('users.agent_id', $user->id)
                ->where('users.type', $childType->value)
                ->select(DB::raw('COALESCE(SUM(wallets.balance), 0) as balance'))
                ->value('balance');
        }

        $userCounts = $this->userCountGet($user);

        return view('admin.dashboard', [
            'user' => $user,
            'role' => $roleTitle,
            'totalBalance' => $totalBalance->balance ?? 0,
            'playerBalance' => $playerBalance / 100,
            'totalOwner' => $userCounts['totalOwner'] ?? 0,
            'totalAgent' => $userCounts['totalAgent'] ?? 0,
            'totalSubAgent' => $userCounts['totalSubAgent'] ?? 0,
            'totalPlayer' => $userCounts['totalPlayer'] ?? 0,
            'totalWinlose' => $totalWinlose,
            'todayWinlose' => $todayWinlose,
            'todayDeposit' => $todayDeposit,
            'todayWithdraw' => $todayWithdraw,
        ]);
    }

    private function fetchTotalTransactions($id, string $type): float
    {
        $user = User::find($id);
        if (! $user) {
            return 0;
        }

        $query = TransferLog::query();

        if ($type === 'deposit') {
            $query->where('type', 'deposit-approve');
            // If user is SubAgent, they see only their transactions
            // If user is Agent, they see all transactions from themself and their subagents, which from_user_id is agent_id
            if ($user->hasRole('SubAgent')) {
                $query->where('sub_agent_id', $id);
            } else {
                $query->where('from_user_id', $id);
            }
        } elseif ($type === 'withdraw') {
            $query->where('type', 'withdraw-approve');
            // If user is SubAgent, they see only their transactions
            // If user is Agent, they see all transactions from themself and their subagents, which to_user_id is agent_id
            if ($user->hasRole('SubAgent')) {
                $query->where('sub_agent_id', $id);
            } else {
                $query->where('to_user_id', $id);
            }
        } else {
            return 0;
        }

        $sum = $query->whereDate('created_at', today())->sum('amount');

        return (float) $sum;
    }

    private function userCountGet($user): array
    {
        $totalOwner = $totalAgent = $totalSubAgent = $totalPlayer = 0;

        switch (true) {
            case $user->hasRole('Owner'):
                $owners = User::where('type', UserType::Owner->value)->pluck('id');
                $agents = User::whereIn('agent_id', $owners)->where('type', UserType::Agent->value)->pluck('id');
                $subAgents = User::whereIn('agent_id', $agents)->where('type', UserType::SubAgent->value)->pluck('id');
                $totalPlayer = User::whereIn('agent_id', $subAgents)->where('type', UserType::Player->value)->count();

                $totalOwner = $owners->count();
                $totalAgent = $agents->count();
                $totalSubAgent = $subAgents->count();
                break;

            case $user->hasRole('Agent'):
                $subAgents = User::where('agent_id', $user->id)->where('type', UserType::SubAgent->value)->pluck('id');
                $totalPlayer = User::whereIn('agent_id', $subAgents)->where('type', UserType::Player->value)->count();

                $totalSubAgent = $subAgents->count();
                break;

            case $user->hasRole('SubAgent'):
                $totalPlayer = User::where('agent_id', $user->id)->where('type', UserType::Player->value)->count();
                break;
        }

        return compact('totalOwner', 'totalAgent', 'totalSubAgent', 'totalPlayer');
    }

    private function getWinLose($id, $todayOnly = false): float
    {
        $query = DB::table('reports')
            ->select(
                DB::raw('COALESCE(SUM(reports.bet_amount), 0) as total_bet_amount'),
                DB::raw('COALESCE(SUM(reports.payout_amount), 0) as total_payout_amount')
            )
            ->where('reports.agent_id', $id);

        if ($todayOnly) {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        $report = $query->first();

        return $report->total_bet_amount - $report->total_payout_amount;
    }

    public function balanceUp(Request $request)
    {
        abort_if(
            Gate::denies('senior_access'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot Access this page because you do not have permission'
        );

        $request->validate([
            'balance' => 'required|numeric',
        ]);

        // Get the current user (admin)
        $admin = Auth::user();

        // Get the current balance before the update
        $openingBalance = $admin->wallet->balanceFloat;

        // Update the balance using the WalletService
        app(WalletService::class)->deposit($admin, $request->balance, TransactionName::CapitalDeposit);

        // Record the transaction in the transactions table
        Transaction::create([
            'payable_type' => get_class($admin),
            'payable_id' => $admin->id,
            'wallet_id' => $admin->wallet->id,
            'type' => 'deposit',
            'amount' => $request->balance,
            'confirmed' => true,
            'meta' => json_encode([
                'name' => TransactionName::CapitalDeposit,
                'opening_balance' => $openingBalance,
                'new_balance' => $admin->wallet->balanceFloat,
                'target_user_id' => $admin->id,
            ]),
            'uuid' => Str::uuid()->toString(),
        ]);

        return back()->with('success', 'Add New Balance Successfully.');
    }

    public function changePassword(Request $request, User $user)
    {
        return view('admin.change_password', compact('user'));
    }

    public function changePlayerSite(Request $request, User $user)
    {
        return view('admin.change_player_site', compact('user'));
    }

    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('home')->with('success', 'Password has been changed Successfully.');
    }

    public function updatePlayerSiteLink(Request $request, User $user)
    {
        $request->validate([
            'site_link' => 'required|string',
        ]);
        $user->update([
            'site_link' => $request->site_link,
        ]);

        return redirect()->route('home')->with('success', 'Player Site Link has been changed Successfully.');
    }

    public function logs($id)
    {
        $logs = UserLog::with('user')->where('user_id', $id)->get();

        return view('admin.logs', compact('logs'));
    }

    public function playerList()
    {
        $user = Auth::user();
        $role = $user->roles->pluck('title');
        $users = User::where('type', UserType::Player)
            ->when($role[0] === 'Agent', function ($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->get();

        return view('admin.player_list', compact('users'));
    }

    // updated by KS

}
