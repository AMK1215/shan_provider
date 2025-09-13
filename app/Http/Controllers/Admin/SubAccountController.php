<?php

namespace App\Http\Controllers\Admin;

use Amp\Parallel\Worker\Execution;
use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerRequest;
use App\Http\Requests\TransferLogRequest;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use App\Models\TransferLog;
use App\Models\User;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

// use App\Models\PlaceBet;

class SubAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected const SUB_AGENT_ROLE = 3;

    private const PLAYER_ROLE = 4;

    // protected const SUB_AGENT_PROFILE = 'subagent_permission';
    protected const SUB_AGENT_PERMISSIONS = [
        'subagent_access',
        'player_view',
        'subagent_player_create',
        'subagent_withdraw',
        'subagent_deposit',
    ];

    private const PERMISSION_GROUPS = [
        // 'view_only' => 'View Only',
        'player_creation' => 'Player Creation',
        'deposit_withdraw' => 'Deposit/Withdraw',
    ];

    public function index()
    {
        $users = User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->where('role_id', self::SUB_AGENT_ROLE);
            })
            ->where('agent_id', auth()->id())
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.sub_acc.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $agent_name = $this->generateRandomString();
        $permission_groups = self::PERMISSION_GROUPS;

        return view('admin.sub_acc.create', compact('agent_name', 'permission_groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_name' => 'required|unique:users,user_name',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:6',
                'permission_group' => 'required|in:'.implode(',', array_keys(self::PERMISSION_GROUPS)),
            ]);

            $agent = User::create([
                'user_name' => $request->user_name,
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'type' => UserType::SubAgent,
                'agent_id' => Auth::id(),
            ]);

            $agent->roles()->sync(self::SUB_AGENT_ROLE);

            // Get permissions based on selected group
            $permissions = Permission::where('group', $request->permission_group)->get();
            $agent->permissions()->sync($permissions->pluck('id'));

            return redirect()->route('admin.subacc.index')
                ->with('success', 'Sub-agent created successfully with '.self::PERMISSION_GROUPS[$request->permission_group].' permissions');

        } catch (Exception $e) {
            Log::error('Error creating sub-agent: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create sub-agent. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find($id);

        return view('admin.sub_acc.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return redirect()->route('admin.subacc.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function banSubAcc($id)
    {
        $user = User::find($id);
        $user->update(['status' => $user->status == 1 ? 0 : 1]);

        return redirect()->back()->with(
            'success',
            'User '.($user->status == 1 ? 'activate' : 'inactive').' successfully'
        );
    }

    public function getChangePassword($id)
    {
        $agent = User::find($id);

        return view('admin.sub_acc.change_password', compact('agent'));
    }

    public function makeChangePassword($id, Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $agent = User::find($id);
        $agent->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.subacc.index')
            ->with('successMessage', 'Agent Change Password successfully')
            ->with('password', $request->password)
            ->with('username', $agent->user_name);
    }

    private function generateRandomString($length = 8)
    {
        // $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // $charactersLength = strlen($characters);
        // $randomString = '';
        // for ($i = 0; $i < $length; $i++) {
        //     $randomString .= $characters[rand(0, $charactersLength - 1)];
        // }

        // return $randomString;
        $randomNumber = mt_rand(10000000, 99999999);

        return 'SAG'.$randomNumber;
    }

    public function permission($id)
    {
        $subAgent = User::findOrFail($id);

        // Ensure the current user is the parent agent
        if ($subAgent->agent_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this sub-agent.');
        }

        // Only permissions in the 'subagent' group
        $permissions = \App\Models\Admin\Permission::where('group', 'subagent')->get()->groupBy('group');
        $subAgentPermissions = $subAgent->permissions->pluck('id')->toArray();

        return view('admin.sub_acc.sub_acc_permission', compact('subAgent', 'permissions', 'subAgentPermissions'));
    }

    public function updatePermission(Request $request, $id)
    {
        $subAgent = User::findOrFail($id);

        // Ensure the current user is the parent agent
        if ($subAgent->agent_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this sub-agent.');
        }

        $permissions = $request->input('permissions', []);
        $subAgent->permissions()->sync($permissions);

        return redirect()->back()->with('success', 'Permissions updated successfully.');
    }

    // sub agent profile
    public function subAgentProfile($id)
    {
        $subAgent = User::findOrFail($id);

        return view('admin.sub_acc.sub_acc_profile', compact('subAgent'));
    }

    public function agentPlayers(Request $request)
    {
        $subAgent = auth()->user();

        if (! $subAgent->hasRole('SubAgent')) {
            abort(403, 'Only subagents can access this page.');
        }

        $agent = $subAgent->agent;

        $query = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('title', 'Player');
        })
            ->where('agent_id', $agent->id);

        // Search by name or username
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%$search%")
                    ->orWhere('user_name', 'ILIKE', "%$search%")
                    ->orWhere('phone', 'ILIKE', "%$search%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $players = $query->orderBy('id', 'desc')->paginate(10)->appends($request->all());

        // For each player, get their totals
        foreach ($players as $player) {
            $totals = \App\Models\PlaceBet::where('member_account', $player->user_name)
                ->selectRaw('
                COUNT(id) as total_stake,
                SUM(bet_amount) as total_bet,
                SUM(prize_amount) as total_payout,
                MIN(before_balance) as min_before_balance,
                MAX(balance) as max_balance
            ')
                ->first();

            $player->total_stake = $totals->total_stake ?? 0;
            $player->total_bet = $totals->total_bet ?? 0;
            $player->total_payout = $totals->total_payout ?? 0;
            $player->min_before_balance = $totals->min_before_balance ?? 0;
            $player->max_balance = $totals->max_balance ?? 0;
        }

        return view('admin.sub_acc.agent_players', compact('players', 'agent'));
    }

    public function playerReport(Request $request, $id)
    {
        $player = \App\Models\User::findOrFail($id);

        $query = \App\Models\PlaceBet::where('member_account', $player->user_name);

        // Robust provider_name filter (case-insensitive, trimmed)
        if ($request->filled('provider_name')) {
            $query->whereRaw('LOWER(TRIM(provider_name)) = ?', [strtolower(trim($request->provider_name))]);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date.' 00:00:00');
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date.' 23:59:59');
        }

        // Fetch bets. It's generally better to perform aggregates in the DB for large datasets,
        // but since you're already getting all 'bets', we'll adjust the sum on the collection.
        $bets = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals with MMK2 conversion
        $total_stake = $bets->where('wager_status', 'SETTLED')->count(); // Count doesn't need currency conversion

        // Use a custom sum for total_bet and total_win to apply MMK2 conversion
        $total_bet = $bets->where('wager_status', 'SETTLED')->sum(function ($bet) {
            return $bet->currency == 'MMK2' ? $bet->bet_amount * 1000 : $bet->bet_amount;
        });

        $total_win = $bets->where('wager_status', 'SETTLED')->sum(function ($bet) {
            return $bet->currency == 'MMK2' ? $bet->prize_amount * 1000 : $bet->prize_amount;
        });

        $net_win = $total_win - $total_bet; // Calculated after conversion

        // The logic for total_lost, is_win, is_lost remains the same based on net_win
        $total_lost = abs(min(0, $net_win)); // Only positive if it's a net loss
        $is_win = $net_win > 0;
        $is_lost = $net_win < 0;

        // Provider dropdown - this is fine as is
        $providers = \App\Models\PlaceBet::where('member_account', $player->user_name)
            ->select('provider_name')
            ->distinct()
            ->pluck('provider_name');

        return view('admin.sub_acc.player_report_detail', compact(
            'player', 'bets', 'total_stake', 'total_bet', 'total_win', 'total_lost', 'providers', 'net_win', 'is_win', 'is_lost'
        ));
    }

    // public function playerReport(Request $request, $id)
    // {
    //     $player = \App\Models\User::findOrFail($id);

    //     $query = \App\Models\PlaceBet::where('member_account', $player->user_name);

    //     // Robust provider_name filter (case-insensitive, trimmed)
    //     if ($request->filled('provider_name')) {
    //         $query->whereRaw('LOWER(TRIM(provider_name)) = ?', [strtolower(trim($request->provider_name))]);
    //     }

    //     // Date range filter
    //     if ($request->filled('start_date')) {
    //         $query->whereDate('created_at', '>=', $request->start_date.' 00:00:00');
    //     }
    //     if ($request->filled('end_date')) {
    //         $query->whereDate('created_at', '<=', $request->end_date.' 23:59:59');
    //     }

    //     $bets = $query->orderBy('created_at', 'desc')->get();

    //     // Totals
    //     $total_stake = $bets->where('wager_status', 'SETTLED')->count();
    //     $total_bet = $bets->where('wager_status', 'SETTLED')->sum('bet_amount');
    //     // $total_bet = $bets->sum('bet_amount');
    //     $total_win = $bets->where('wager_status', 'SETTLED')->sum('prize_amount');
    //     $total_lost = $total_bet - $total_win;
    //     $net_win = $total_win - $total_bet; // Positive if won, negative if lost

    //     $is_win = $net_win > 0;
    //     $is_lost = $net_win < 0;

    //     // Provider dropdown
    //     $providers = \App\Models\PlaceBet::where('member_account', $player->user_name)
    //         ->select('provider_name')
    //         ->distinct()
    //         ->pluck('provider_name');

    //     return view('admin.sub_acc.player_report_detail', compact(
    //         'player', 'bets', 'total_stake', 'total_bet', 'total_win', 'total_lost', 'providers', 'net_win', 'is_win', 'is_lost'
    //     ));
    // }

    // ----------------- Transaction method -------------
    public function getCashIn(User $player)
    {
        // abort_if(
        //     Gate::denies('process_deposit'),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission || ဤလုပ်ဆောင်ချက်အား သင့်မှာ လုပ်ဆောင်ပိုင်ခွင့်မရှိပါ, ကျေးဇူးပြု၍ သက်ဆိုင်ရာ Agent များထံ ဆက်သွယ်ပါ'
        // );

        $subAgent = Auth::user();
        $agent = $subAgent->agent;

        return view('admin.sub_acc.cash_in', compact('player', 'agent'));
    }

    public function makeCashIn(TransferLogRequest $request, User $player)
    {
        // abort_if(
        //     Gate::denies('process_deposit'),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission || ဤလုပ်ဆောင်ချက်အား သင့်မှာ လုပ်ဆောင်ပိုင်ခွင့်မရှိပါ, ကျေးဇူးပြု၍ သက်ဆိုင်ရာ Agent များထံ ဆက်သွယ်ပါ'
        // );

        try {
            DB::beginTransaction();
            $inputs = $request->validated();
            $inputs['refrence_id'] = $this->getRefrenceId();

            $subAgent = Auth::user();      // The subagent making the request
            $agent = $subAgent->agent;     // The parent agent (who owns the balance)

            $cashIn = $inputs['amount'];

            if ($cashIn > $agent->balanceFloat) {

                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            app(WalletService::class)->transfer($agent, $player, $request->validated('amount'),
                TransactionName::CreditTransfer, [
                    'note' => $request->note,
                    'old_balance' => $player->balanceFloat,
                    'new_balance' => $player->balanceFloat + $request->amount,
                ]);
            // Log the transfer
            TransferLog::create([
                'from_user_id' => $agent->id,
                'to_user_id' => $player->id,
                'sub_agent_id' => $subAgent->id,
                'sub_agent_name' => $subAgent->user_name,
                'amount' => $request->amount,
                'type' => 'top_up',
                'description' => 'TopUp transfer from '.$agent->user_name.' to player',
                'meta' => [
                    'transaction_type' => TransactionName::Deposit->value,
                    'note' => $request->note,
                    'old_balance' => $player->balanceFloat,
                    'new_balance' => $player->balanceFloat + $request->amount,
                ],
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'CashIn submitted successfully!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getCashOut(User $player)
    {
        // abort_if(
        //     Gate::denies('process_withdraw'),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission || ဤလုပ်ဆောင်ချက်အား သင့်မှာ လုပ်ဆောင်ပိုင်ခွင့်မရှိပါ, ကျေးဇူးပြု၍ သက်ဆိုင်ရာ Agent များထံ ဆက်သွယ်ပါ'
        // );
        $subAgent = Auth::user();
        $agent = $subAgent->agent;

        return view('admin.sub_acc.cash_out', compact('player', 'agent'));
    }

    public function makeCashOut(TransferLogRequest $request, User $player)
    {
        // abort_if(
        //     Gate::denies('process_withdraw'),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission || ဤလုပ်ဆောင်ချက်အား သင့်မှာ လုပ်ဆောင်ပိုင်ခွင့်မရှိပါ, ကျေးဇူးပြု၍ သက်ဆိုင်ရာ Agent များထံ ဆက်သွယ်ပါ'
        // );

        try {
            DB::beginTransaction();
            $inputs = $request->validated();
            $inputs['refrence_id'] = $this->getRefrenceId();

            $subAgent = Auth::user();      // The subagent making the request
            $agent = $subAgent->agent;     // The parent agent (who owns the balance)

            $cashOut = $inputs['amount'];

            if ($cashOut > $player->balanceFloat) {

                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            app(WalletService::class)->transfer($player, $agent, $request->validated('amount'),
                TransactionName::DebitTransfer, [
                    'note' => $request->note,
                    'old_balance' => $player->balanceFloat,
                    'new_balance' => $player->balanceFloat - $request->amount,
                ]);
            // Log the transfer
            TransferLog::create([
                'from_user_id' => $player->id,
                'to_user_id' => $agent->id,
                'sub_agent_id' => $subAgent->id,
                'sub_agent_name' => $subAgent->user_name,
                'amount' => $request->amount,
                'type' => 'withdraw',
                'description' => 'Transfer from player to '.$agent->user_name,
                'meta' => [
                    'transaction_type' => TransactionName::Withdraw->value,
                    'note' => $request->note,
                    'old_balance' => $player->balanceFloat,
                    'new_balance' => $player->balanceFloat - $request->amount,
                ],
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'CashOut submitted successfully!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function getRefrenceId($prefix = 'REF')
    {
        return uniqid($prefix);
    }

    // transfer log
    public function SubAgentTransferLog(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('SubAgent')) {
            $query = TransferLog::with(['fromUser', 'toUser'])
                ->where('sub_agent_id', $user->id);
        } else {
            // Get subagent IDs for this agent
            $subAgentIds = User::where('agent_id', $user->id)
                ->whereHas('roles', function ($q) {
                    $q->where('title', 'SubAgent');
                })->pluck('id')->toArray();

            $query = TransferLog::with(['fromUser', 'toUser'])
                ->where(function ($q) use ($user, $subAgentIds) {
                    $q->where('from_user_id', $user->id)
                        ->orWhereIn('sub_agent_id', $subAgentIds);
                });
        }

        // Apply filters if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        // if ($request->has('date_from') && $request->has('date_to')) {
        //     $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        // }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = $request->date_from.' 00:00:00';
            $to = $request->date_to.' 23:59:59';
            $query->whereBetween('created_at', [$from, $to]);
        }

        $transferLogs = $query->latest()->paginate(20);
        $types = TransferLog::distinct()->pluck('type');

        return view('admin.transfer_logs.subacc_log', compact('transferLogs', 'types'));
    }

    public function PlayerCreate()
    {
        // abort_if(
        //     Gate::denies('create_player'),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission'
        // );
        $player_name = $this->PlayergenerateRandomString();
        // $agent = $this->getAgent() ?? Auth::user();
        $subAgent = Auth::user();      // The subagent making the request
        $agent = $subAgent->agent;
        // $owner_id = User::where('agent_id', $agent->agent_id)->first();
        // Get the related owner of the agent
        $owner = User::where('id', $agent->agent_id)->first(); // Assuming `agent_id` refers to the owner's ID

        // return $owner;

        return view('admin.sub_acc.player_create', compact('player_name', 'owner'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function PlayerStore(PlayerRequest $request)
    {
        // Gate::allows('create_player');

        // $agent = $this->getAgent() ?? Auth::user();
        $subAgent = Auth::user();      // The subagent making the request
        $agent = $subAgent->agent;     // The parent agent (who owns the balance)

        $siteLink = $agent->parent->site_link ?? 'null';

        $inputs = $request->validated();
        $inputs['amount'] = $inputs['amount'] ?? 0;

        try {
            DB::beginTransaction();
            if (isset($inputs['amount']) && $inputs['amount'] > $agent->balanceFloat) {
                return redirect()->back()->with('error', 'Balance Insufficient');
            }

            $user = User::create([
                'name' => $inputs['name'],
                'user_name' => $inputs['user_name'],
                'password' => Hash::make($inputs['password']),
                'phone' => $inputs['phone'],
                'agent_id' => $agent->id,
                'type' => UserType::Player,
            ]);

            $user->roles()->sync(self::PLAYER_ROLE);

            if (isset($inputs['amount'])) {
                app(WalletService::class)->transfer($agent, $user, $inputs['amount'],
                    TransactionName::CreditTransfer, [
                        'old_balance' => $user->balanceFloat,
                        'new_balance' => $user->balanceFloat + $request->amount,
                    ]);
            }

            // Log the transfer
            TransferLog::create([
                'from_user_id' => $agent->id,
                'to_user_id' => $user->id,
                'amount' => $inputs['amount'],
                'type' => 'top_up',
                'description' => 'Initial Top Up from agent to new player',
                'meta' => [
                    'transaction_type' => TransactionName::CreditTransfer->value,
                    'old_balance' => $user->balanceFloat,
                    'new_balance' => $user->balanceFloat + $inputs['amount'],
                ],
            ]);

            DB::commit();

            return redirect()->back()
                ->with('successMessage', 'Player created successfully')
                ->with('amount', $request->amount)
                ->with('password', $request->password)
                ->with('site_link', $siteLink)
                ->with('user_name', $user->user_name);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while creating the player.');
        }
    }

    private function PlayergenerateRandomString()
    {
        $randomNumber = mt_rand(10000000, 99999999);

        return 'P'.$randomNumber;
    }

    public function viewPermissions($id)
    {
        $subAgent = User::findOrFail($id);

        // Ensure the current user is the parent agent
        if ($subAgent->agent_id !== Auth::id()) {
            abort(403, 'You do not have permission to view this sub-agent.');
        }

        $permissions = Permission::all();
        $subAgentPermissions = $subAgent->permissions->pluck('id')->toArray();
        $permission_groups = self::PERMISSION_GROUPS;

        return view('admin.sub_acc.view_permissions', compact('subAgent', 'permissions', 'subAgentPermissions', 'permission_groups'));
    }

    public function updatePermissions(Request $request, $id)
    {
        try {
            $subAgent = User::findOrFail($id);

            // Validate the request
            $request->validate([
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            // Sync the permissions
            $subAgent->permissions()->sync($request->permissions ?? []);

            return redirect()
                ->route('admin.subacc.permissions.view', $id)
                ->with('success', 'Permissions updated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update permissions: '.$e->getMessage());
        }
    }
}
