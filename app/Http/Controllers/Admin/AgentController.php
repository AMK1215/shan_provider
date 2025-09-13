<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRequest;
use App\Http\Requests\TransferLogRequest;
// use App\Models\Admin\TransferLog;
use App\Models\Admin\Permission;
use App\Models\Operator;
use App\Models\PaymentType;
use App\Models\TransferLog;
use App\Models\User;
use App\Services\WalletService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private const AGENT_ROLE = 2;

    private const PLAYER_ROLE = 4;

    private const PERMISSION_GROUPS = [
        'view_only' => [
            'view_player_list',
            'view_player_report',
            'view_transaction_log',
        ],

        // Player Creation Group
        'player_creation' => [
            'view_player_list',
            'create_player',
            'edit_player',
            'change_player_password',
            'view_player_report',
            'view_transaction_log',
        ],

        // Deposit/Withdraw Group
        'deposit_withdraw' => [
            'view_player_list',
            'view_player_report',
            'view_transaction_log',
            'view_withdraw_requests',
            'view_deposit_requests',
            'process_withdraw',
            'process_deposit',
        ],
        'agent' => [
            'subagent_access',
            'subagent_index',
            'subagent_create',
            'subagent_edit',
            'subagent_delete',
            'player_access',
            'agent_change_password_access',
            'transfer_log',
            'make_transfer',
            'bank',
            'deposit',
            'withdraw',
            'contact',
        ],
    ];

    public function index(): View
    {
        // if (! Gate::allows('agent_index')) {
        //     abort(403);
        // }

        $agents = User::with(['roles', 'children.poneWinePlayer'])->whereHas('roles', fn ($q) => $q->where('role_id', self::AGENT_ROLE))
            ->select('id', 'name', 'user_name', 'phone', 'status', 'referral_code')
            ->where('agent_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $reportData = DB::table('users as a')
            ->join('users as p', 'p.agent_id', '=', 'a.id')          // player
            ->join('place_bets', 'place_bets.member_account', '=', 'p.user_name')
            ->groupBy('a.id')
            ->selectRaw('a.id as agent_id,SUM(place_bets.bet_amount) as total_bet_amount,SUM(place_bets.prize_amount) as total_payout_amount')
            ->get()
            ->keyBy('agent_id');

        // dd($reportData);
        $users = $agents->map(function ($agent) use ($reportData) {
            $report = $reportData->get($agent->id);
            $poneWineTotalAmt = $agent->children->flatMap->poneWinePlayer->sum('win_lose_amt');

            return (object) [
                'id' => $agent->id,
                'name' => $agent->name,
                'user_name' => $agent->user_name,
                'referral_code' => $agent->referral_code,
                'phone' => $agent->phone,
                'balanceFloat' => $agent->balanceFloat,
                'status' => $agent->status,
                'win_lose' => (($report->total_payout_amount ?? 0) - ($report->total_bet_amount ?? 0)) + $poneWineTotalAmt,
            ];
        });

        return view('admin.agent.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // if (! Gate::allows('agent_create')) {
        //     abort(403);
        // }

        $agent_name = $this->generateRandomString();
        $paymentTypes = PaymentType::all();
        $referral_code = $this->generateReferralCode();
        $shan_secret_key = $this->generateShanSecretKey();

        return view('admin.agent.create', compact('agent_name', 'paymentTypes', 'referral_code', 'shan_secret_key'));
    }

    // generate ShanSecretkey string 15
    private function generateShanSecretKey()
    {
        return Str::random(15);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws ValidationException
     */
    public function store(AgentRequest $request): RedirectResponse
    {
        // if (! Gate::allows('agent_create')) {
        //     abort(403);
        // }

        $owner = Auth::user();
        $inputs = $request->validated();

        // Check if owner has sufficient balance for transfer
        if (isset($inputs['amount']) && $inputs['amount'] > $owner->balanceFloat) {
            return redirect()->back()->with('error', 'Balance Insufficient');
        }

        $transfer_amount = $inputs['amount'] ?? 0;

        // Create the agent
        $userPrepare = array_merge(
            $inputs,
            [
                'password' => Hash::make($inputs['password']),
                'agent_id' => Auth::id(),
                'type' => UserType::Agent->value,
                'shan_agent_code' => $inputs['shan_agent_code'],
                'shan_agent_name' => $inputs['shan_agent_name'],
                'shan_secret_key' => $inputs['shan_secret_key'],
                'shan_callback_url' => $inputs['shan_callback_url'],
            ]
        );

        $agent = User::create($userPrepare);
        $operator = Operator::create([
            'code' => $inputs['shan_agent_code'],
            'secret_key' => $inputs['shan_secret_key'],
            'active' => 1,
            'callback_url' => $inputs['shan_callback_url'],
        ]);

        $agent->roles()->sync(self::AGENT_ROLE);

        $permissions = Permission::whereIn('group', ['agent', 'player_creation', 'deposit_withdraw', 'view_only'])->get();
        $agent->permissions()->sync($permissions->pluck('id'));

        // Handle transfer if amount is provided
        if ($transfer_amount > 0) {
            try {
                DB::beginTransaction();

                // Perform the transfer
                app(WalletService::class)->transfer(
                    $owner,
                    $agent,
                    $transfer_amount,
                    TransactionName::CreditTransfer,
                    [
                        'old_balance' => $agent->balanceFloat,
                        'new_balance' => $agent->balanceFloat + $transfer_amount,
                    ]
                );

                // Log the transfer
                TransferLog::create([
                    'from_user_id' => $owner->id,
                    'to_user_id' => $agent->id,
                    'amount' => $transfer_amount,
                    'type' => 'top_up',
                    'description' => 'Initial Top Up from Owner to new agent',
                    'meta' => [
                        'transaction_type' => TransactionName::CreditTransfer->value,
                        'old_balance' => $agent->balanceFloat,
                        'new_balance' => $agent->balanceFloat + $transfer_amount,
                    ],
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during agent creation and transfer: '.$e->getMessage());

                return redirect()->back()->with('error', 'Error occurred during transfer. Please try again.');
            }
        }

        return redirect()->route('admin.agent.index')
            ->with('successMessage', 'Agent created successfully')
            ->with('password', $request->password)
            ->with('username', $agent->user_name)
            ->with('amount', $transfer_amount)
            ->with('shan_agent_code', $inputs['shan_agent_code'])
            ->with('shan_agent_name', $inputs['shan_agent_name'])
            ->with('shan_secret_key', $inputs['shan_secret_key'])
            ->with('shan_callback_url', $inputs['shan_callback_url']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        // if (! Gate::allows('agent_edit')) {
        //     abort(403);
        // }

        $agent = User::find($id);
        $paymentTypes = PaymentType::all();

        return view('admin.agent.edit', compact('agent', 'paymentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // if (! Gate::allows('agent_edit')) {
        //     abort(403);
        // }

        $user = User::find($id);
        
        if (!$user) {
            return redirect()->route('admin.agent.index')
                ->with('error', 'Agent not found');
        }

        // Validate all the fields according to users table structure
        $validatedData = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'profile' => 'nullable|string|max:2000',
            'agent_logo' => 'nullable|string|max:255',
            
            // Financial Settings
            'max_score' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0|max:100',
            'payment_type_id' => 'nullable|exists:payment_types,id',
            
            // Banking Information
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            
            // Contact & Site Information
            'line_id' => 'nullable|string|max:255',
            'referral_code' => 'nullable|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'site_link' => 'nullable|url|max:255',
            
            // Shan Game Configuration
            'shan_agent_code' => 'required|string|max:255|unique:users,shan_agent_code,' . $id,
            'shan_agent_name' => 'nullable|string|max:255',
            'shan_secret_key' => 'required|string|max:255',
            'shan_callback_url' => 'required|url|max:255',
            
            // Status Settings
            'status' => 'required|integer|in:0,1',
            'is_changed_password' => 'required|integer|in:0,1',
            'type' => 'required|string', // Don't allow changing type
        ]);

        // Remove sensitive fields that shouldn't be mass assigned
        unset($validatedData['type']); // Keep original type
        
        // Update user with validated data
        $user->update($validatedData);

        return redirect()->route('admin.agent.index')
            ->with('success', 'Agent updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function getCashIn(string $id): View
    {
        // if (! Gate::allows('make_transfer')) {
        //     abort(403);
        // }
        $agent = User::find($id);

        return view('admin.agent.cash_in', compact('agent'));
    }

    public function getCashOut(string $id): View
    {
        // if (! Gate::allows('make_transfer')) {
        //     abort(403);
        // }
        // Assuming $id is the user ID
        $agent = User::findOrFail($id);

        return view('admin.agent.cash_out', compact('agent'));
    }

    public function makeCashIn(Request $request, $id): RedirectResponse
    {
        // if (! Gate::allows('make_transfer')) {
        //     abort(403);
        // }

        try {
            $agent = User::findOrFail($id);
            $admin = Auth::user();
            if ($request->amount > $admin->balanceFloat) {
                throw new \Exception('You do not have enough balance to transfer!');
            }

            app(WalletService::class)->transfer(
                $admin,
                $agent,
                $request->amount,
                TransactionName::CreditTransfer,
                [
                    'note' => $request->note,
                    'old_balance' => $agent->balanceFloat,
                    'new_balance' => $agent->balanceFloat + $request->amount,
                ]
            );

            // Create transfer log
            TransferLog::create([
                'from_user_id' => $admin->id,
                'to_user_id' => $agent->id,
                'amount' => $request->amount,
                'type' => 'top_up',
                'description' => $request->note ?? 'TopUp from owner to '.$agent->user_name,
                'meta' => [
                    'transaction_type' => TransactionName::CreditTransfer->value,
                    'old_balance' => $agent->balanceFloat,
                    'new_balance' => $agent->balanceFloat + $request->amount,
                ],
            ]);

            return redirect()->route('admin.agent.index')->with('success', 'Money fill request submitted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function makeCashOut(TransferLogRequest $request, string $id): RedirectResponse
    {
        // if (! Gate::allows('make_transfer')) {
        //     abort(403);
        // }

        try {
            $agent = User::findOrFail($id);
            $admin = Auth::user();
            $cashOut = $request->amount;

            if ($cashOut > $agent->balanceFloat) {
                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            // Transfer money
            app(WalletService::class)->transfer(
                $agent,
                $admin,
                $request->amount,
                TransactionName::DebitTransfer,
                [
                    'note' => $request->note,
                    'old_balance' => $agent->balanceFloat,
                    'new_balance' => $agent->balanceFloat - $request->amount,
                ]
            );

            // Create transfer log
            TransferLog::create([
                'from_user_id' => $agent->id,
                'to_user_id' => $admin->id,
                'amount' => $request->amount,
                'type' => 'withdraw',
                'description' => $request->note ?? 'Withdraw from '.$agent->user_name.' to owner',
                'meta' => [
                    'transaction_type' => TransactionName::DebitTransfer->value,
                    'old_balance' => $agent->balanceFloat,
                    'new_balance' => $agent->balanceFloat - $request->amount,
                ],
            ]);

            return redirect()->back()->with('success', 'Money fill request submitted successfully!');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getTransferDetail($id)
    {
        // abort_if(
        //     Gate::denies('make_transfer') || ! $this->ifChildOfParent(request()->user()->id, $id),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission'
        // );
        $transfer_detail = TransferLog::where('from_user_id', $id)
            ->orWhere('to_user_id', $id)
            ->get();

        return view('admin.agent.transfer_detail', compact('transfer_detail'));
    }

    private function generateRandomString()
    {
        $randomNumber = mt_rand(10000000, 99999999);

        return 'AG'.$randomNumber;
    }

    public function banAgent($id): RedirectResponse
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
        // abort_if(
        //     Gate::denies('owner_access') || ! $this->ifChildOfParent(request()->user()->id, $id),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission'
        // );

        $agent = User::find($id);

        return view('admin.agent.change_password', compact('agent'));
    }

    public function makeChangePassword($id, Request $request)
    {
        // abort_if(
        //     Gate::denies('owner_access') || ! $this->ifChildOfParent(request()->user()->id, $id),
        //     Response::HTTP_FORBIDDEN,
        //     '403 Forbidden |You cannot  Access this page because you do not have permission'
        // );

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $agent = User::find($id);
        $agent->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.agent.index')
            ->with('successMessage', 'Agent Change Password successfully')
            ->with('password', $request->password)
            ->with('username', $agent->user_name);
    }

    public function showAgentLogin($id)
    {
        $agent = User::findOrFail($id);

        return view('auth.agent_login', compact('agent'));
    }

    public function AgentToPlayerDepositLog()
    {
        $transactions = DB::table('transactions')
            ->join('users as players', 'players.id', '=', 'transactions.payable_id')
            ->join('users as agents', 'agents.id', '=', 'players.agent_id')
            ->where('transactions.type', 'deposit')
            ->where('transactions.name', 'credit_transfer')
            ->where('agents.id', '<>', 1) // Exclude agent_id 1
            ->groupBy('agents.id', 'players.id', 'agents.name', 'players.name', 'agents.commission')
            ->select(
                'agents.id as agent_id',
                'agents.name as agent_name',
                'players.id as player_id',
                'players.name as player_name',
                'agents.commission as agent_commission', // Get the commission percentage
                DB::raw('count(transactions.id) as total_deposits'),
                DB::raw('sum(transactions.amount) as total_amount')
            )
            ->get();

        return view('admin.agent.agent_to_play_dep_log', compact('transactions'));
    }

    public function AgentToPlayerDetail($agent_id, $player_id)
    {
        // Retrieve detailed information about the agent and player
        $transactionDetails = DB::table('transactions')
            ->join('users as players', 'players.id', '=', 'transactions.payable_id')
            ->join('users as agents', 'agents.id', '=', 'players.agent_id')
            ->where('agents.id', $agent_id)
            ->where('players.id', $player_id)
            ->where('transactions.type', 'deposit')
            ->where('transactions.name', 'credit_transfer')
            ->select(
                'agents.name as agent_name',
                'players.name as player_name',
                'transactions.amount',
                'transactions.created_at',
                'agents.commission as agent_commission'
            )
            ->get();

        return view('admin.agent.agent_to_player_detail', compact('transactionDetails'));
    }

    public function AgentWinLoseReport(Request $request)
    {
        $query = DB::table('reports')
            ->join('users', 'reports.agent_id', '=', 'users.id')
            ->select(
                'reports.agent_id',
                'users.name as agent_name',
                DB::raw('COUNT(DISTINCT reports.id) as qty'),
                DB::raw('SUM(reports.bet_amount) as total_bet_amount'),
                DB::raw('SUM(reports.valid_bet_amount) as total_valid_bet_amount'),
                DB::raw('SUM(reports.payout_amount) as total_payout_amount'),
                DB::raw('SUM(reports.commission_amount) as total_commission_amount'),
                DB::raw('SUM(reports.jack_pot_amount) as total_jack_pot_amount'),
                DB::raw('SUM(reports.jp_bet) as total_jp_bet'),
                DB::raw('(SUM(reports.payout_amount) - SUM(reports.valid_bet_amount)) as win_or_lose'),
                DB::raw('COUNT(*) as stake_count'),
                DB::raw('DATE_FORMAT(reports.created_at, "%Y %M") as report_month_year')
            );

        // Apply the date filter if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('reports.created_at', [$request->start_date, $request->end_date]);
        } elseif ($request->has('month_year')) {
            // Filter by month and year if provided
            $monthYear = Carbon::parse($request->month_year);
            $query->whereMonth('reports.created_at', $monthYear->month)
                ->whereYear('reports.created_at', $monthYear->year);
        }

        $agentReports = $query->groupBy('reports.agent_id', 'users.name', 'report_month_year')->get();

        return view('admin.agent.agent_report_index', compact('agentReports'));
    }

    public function AgentWinLoseDetails($agent_id, $month)
    {
        $details = DB::table('reports')
            ->join('users', 'reports.agent_id', '=', 'users.id')
            ->where('reports.agent_id', $agent_id)
            ->whereMonth('reports.created_at', Carbon::parse($month)->month)
            ->whereYear('reports.created_at', Carbon::parse($month)->year)
            ->select(
                'reports.*',
                'users.name as agent_name',
                'users.commission as agent_comm',
                DB::raw('(reports.payout_amount - reports.valid_bet_amount) as win_or_lose') // Calculating win_or_lose
            )
            ->get();

        return view('admin.agent.win_lose_details', compact('details'));
    }

    public function AuthAgentWinLoseReport(Request $request)
    {
        $agentId = Auth::user()->id;  // Get the authenticated user's agent_id

        $query = DB::table('reports')
            ->join('users', 'reports.agent_id', '=', 'users.id')
            ->select(
                'reports.agent_id',
                'users.name as agent_name',
                DB::raw('COUNT(DISTINCT reports.id) as qty'),
                DB::raw('SUM(reports.bet_amount) as total_bet_amount'),
                DB::raw('SUM(reports.valid_bet_amount) as total_valid_bet_amount'),
                DB::raw('SUM(reports.payout_amount) as total_payout_amount'),
                DB::raw('SUM(reports.commission_amount) as total_commission_amount'),
                DB::raw('SUM(reports.jack_pot_amount) as total_jack_pot_amount'),
                DB::raw('SUM(reports.jp_bet) as total_jp_bet'),
                DB::raw('(SUM(reports.payout_amount) - SUM(reports.valid_bet_amount)) as win_or_lose'),
                DB::raw('COUNT(*) as stake_count'),
                DB::raw('DATE_FORMAT(reports.created_at, "%Y %M") as report_month_year')
            )
            ->where('reports.agent_id', $agentId);  // Filter by authenticated user's agent_id

        // Apply the date filter if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('reports.created_at', [$request->start_date, $request->end_date]);
        } elseif ($request->has('month_year')) {
            // Filter by month and year if provided
            $monthYear = Carbon::parse($request->month_year);
            $query->whereMonth('reports.created_at', $monthYear->month)
                ->whereYear('reports.created_at', $monthYear->year);
        }

        $agentReports = $query->groupBy('reports.agent_id', 'users.name', 'report_month_year')->get();

        return view('admin.agent.auth_agent_report_index', compact('agentReports'));
    }

    public function AuthAgentWinLoseDetails($agent_id, $month)
    {
        $details = DB::table('reports')
            ->join('users', 'reports.agent_id', '=', 'users.id')
            ->where('reports.agent_id', $agent_id)
            ->whereMonth('reports.created_at', Carbon::parse($month)->month)
            ->whereYear('reports.created_at', Carbon::parse($month)->year)
            ->select(
                'reports.*',
                'users.name as agent_name',
                'users.commission as agent_comm',
                DB::raw('(reports.payout_amount - reports.valid_bet_amount) as win_or_lose') // Calculating win_or_lose
            )
            ->get();

        return view('admin.agent.auth_win_lose_details', compact('details'));
    }

    public function getPlayerReports($id)
    {
        $users = User::with('roles', 'poneWinePlayer', 'results', 'betNResults')
            ->whereHas('roles', function ($query) {
                $query->where('role_id', self::PLAYER_ROLE);
            })
            ->where('agent_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.agent.player_report', compact('users'));
    }

    public function agentReportIndex($id)
    {
        // Eager-load agent, roles, and children (downlines) with their poneWinePlayer relationship
        $agent = User::with([
            'roles',
            'children.poneWinePlayer',
        ])->findOrFail($id);

        // Sum all win_lose_amt for all poneWinePlayer of all children
        $poneWineTotalAmt = $agent->children
            ->flatMap(function ($child) {
                return $child->poneWinePlayer;
            })
            ->sum('win_lose_amt');

        // Query total bet and payout for the agent's downline players (only slot games, if you want)
        $reportData = DB::table('users as a')
            ->join('users as p', 'p.agent_id', '=', 'a.id')
            ->join('place_bets', 'place_bets.member_account', '=', 'p.user_name')
            ->where('a.id', $id)
            ->groupBy('a.id')
            ->selectRaw('
            a.id as agent_id,
            SUM(place_bets.bet_amount) as total_bet_amount,
            SUM(place_bets.prize_amount) as total_payout_amount
        ')
            ->first();

        // Add agent info to the report
        $report = [
            'agent_name' => $agent->name,
            'agent_user_name' => $agent->user_name,
            'win_lose' => ($reportData->total_bet_amount ?? 0) - ($reportData->total_payout_amount ?? 0),
            'total_win_lose_pone_wine' => $poneWineTotalAmt ?? 0,
        ];

        return view('admin.agent.report_index', compact('report'));
    }

    private function generateReferralCode($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    // agent profile
    public function agentProfile($id)
    {
        $subAgent = User::findOrFail($id);

        return view('admin.agent.agent_profile', compact('subAgent'));
    }
}
