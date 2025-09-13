<?php

namespace App\Http\Controllers\Admin\TransferLog;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TransferLogController extends Controller
{
    protected const OWNER_ROLE = 'Owner';

    protected const MASTER_ROLE = 'Master';

    protected const AGENT_ROLE = 'Agent';

    protected const SUB_AGENT_ROLE = 'SubAgent';

    protected const PLAYER_ROLE = 'Player';

    public function index(Request $request)
    {
        $agent = $this->getAgentOrCurrentUser();

        [$startDate, $endDate] = $this->parseDateRange($request);

        $transferLogs = $this->fetchTransferLogs($agent, $startDate, $endDate);
        $depositTotal = $this->fetchTotalAmount($agent, 'deposit', $startDate, $endDate);
        $withdrawTotal = $this->fetchTotalAmount($agent, 'withdraw', $startDate, $endDate);

        return view('admin.trans_log.index', compact('transferLogs', 'depositTotal', 'withdrawTotal'));
    }

    private function parseDateRange(Request $request): array
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        return [$startDate->format('Y-m-d H:i'), $endDate->format('Y-m-d H:i')];
    }

    private function fetchTransferLogs(User $user, string $startDate, string $endDate)
    {
        $relatedUserIds = $this->getRelevantUserIdsForTransfer($user);

        return Transaction::with('targetUser')
            ->whereIn('type', ['withdraw', 'deposit'])
            ->whereIn('name', ['credit_transfer', 'debit_transfer'])
            ->where(function ($query) use ($user, $relatedUserIds) {
                $query->where(function ($q) use ($user, $relatedUserIds) {
                    $q->where('payable_id', $user->id)
                        ->whereIn('target_user_id', $relatedUserIds);
                })->orWhere(function ($q) use ($user, $relatedUserIds) {
                    $q->whereIn('payable_id', $relatedUserIds)
                        ->where('target_user_id', $user->id);
                });
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('id')
            ->get();
    }

    private function fetchTotalAmount(User $user, string $type, string $startDate, string $endDate): float
    {
        $relatedUserIds = $this->getRelevantUserIdsForTransfer($user);

        return Transaction::where('type', $type)
            ->whereIn('name', ['credit_transfer', 'debit_transfer'])
            ->where(function ($query) use ($user, $relatedUserIds) {
                $query->where(function ($q) use ($user, $relatedUserIds) {
                    $q->where('payable_id', $user->id)
                        ->whereIn('target_user_id', $relatedUserIds);
                })->orWhere(function ($q) use ($user, $relatedUserIds) {
                    $q->whereIn('payable_id', $relatedUserIds)
                        ->where('target_user_id', $user->id);
                });
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
    }

    private function getRelevantUserIdsForTransfer(User $user): array
    {
        $userType = UserType::from($user->type);

        switch ($userType) {
            case UserType::Owner:
                return User::where('type', UserType::Master->value)
                    ->orWhere('type', UserType::Owner->value)
                    ->pluck('id')->toArray();

            case UserType::Master:
                return User::where(function ($query) use ($user) {
                    $query->where('type', UserType::Owner->value)
                        ->orWhere('type', UserType::Agent->value)
                        ->orWhere('id', $user->id);
                })
                    ->pluck('id')->toArray();

            case UserType::Agent:
                return User::where(function ($query) use ($user) {
                    $query->where('type', UserType::Master->value)
                        ->orWhere('type', UserType::Player->value)
                        ->orWhere('type', UserType::SubAgent->value)
                        ->orWhere('id', $user->id);
                })
                    ->pluck('id')->toArray();

            case UserType::SubAgent:
                return User::where(function ($query) use ($user) {
                    $query->where('type', UserType::Agent->value)
                        ->orWhere('user_type', UserType::Player->value)
                        ->orWhere('id', $user->id);
                })
                    ->pluck('id')->toArray();

            default:
                return [$user->id];
        }
    }

    public function transferLog($id)
    {
        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden | You cannot access this page because you do not have permission'
        );

        $agent = $this->getAgent() ?? Auth::user();

        $transferLogs = $agent->transactions()->with('targetUser')
            ->whereIn('transactions.type', ['withdraw', 'deposit'])
            ->whereIn('transactions.name', ['credit_transfer', 'debit_transfer'])
            ->where('target_user_id', $id)
            ->orderBy('transactions.id', 'desc')
            ->paginate();

        return view('admin.trans_log.detail', compact('transferLogs'));
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

    private function getAgentOrCurrentUser(): User
    {
        $user = Auth::user();

        return $this->findAgent($user->id) ?? $user;
    }

    private function findAgent(int $userId): ?User
    {
        $user = User::find($userId);

        return $user && $user->hasRole(self::SUB_AGENT_ROLE) ? $user->parent : null;
    }

    // private function parseDateRange(Request $request): array
    // {
    //     $startDate = $request->start_date
    //         ? Carbon::parse($request->start_date)->startOfDay()
    //         : Carbon::today()->startOfDay();

    //     $endDate = $request->end_date
    //         ? Carbon::parse($request->end_date)->endOfDay()
    //         : Carbon::today()->endOfDay();

    //     return [$startDate->format('Y-m-d H:i'), $endDate->format('Y-m-d H:i')];
    // }

    // private function fetchTransferLogs(User $user, string $startDate, string $endDate)
    // {
    //     $relatedUserIds = $this->getRelevantUserIdsForTransfer($user);

    //     return \App\Models\Transaction::with('targetUser')
    //         ->whereIn('type', ['withdraw', 'deposit'])
    //         ->whereIn('name', ['credit_transfer', 'debit_transfer'])
    //         ->where(function ($query) use ($user, $relatedUserIds) {
    //             $query->where(function ($q) use ($user, $relatedUserIds) {
    //                 $q->where('user_id', $user->id)
    //                   ->whereIn('target_user_id', $relatedUserIds);
    //             })->orWhere(function ($q) use ($user, $relatedUserIds) {
    //                 $q->whereIn('user_id', $relatedUserIds)
    //                   ->where('target_user_id', $user->id);
    //             });
    //         })
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->orderByDesc('id')
    //         ->get();
    // }

    // private function fetchTotalAmount(User $user, string $type, string $startDate, string $endDate): float
    // {
    //     $relatedUserIds = $this->getRelevantUserIdsForTransfer($user);

    //     return \App\Models\Transaction::where('type', $type)
    //         ->whereIn('name', ['credit_transfer', 'debit_transfer'])
    //         ->where(function ($query) use ($user, $relatedUserIds) {
    //             $query->where(function ($q) use ($user, $relatedUserIds) {
    //                 $q->where('user_id', $user->id)
    //                   ->whereIn('target_user_id', $relatedUserIds);
    //             })->orWhere(function ($q) use ($user, $relatedUserIds) {
    //                 $q->whereIn('user_id', $relatedUserIds)
    //                   ->where('target_user_id', $user->id);
    //             });
    //         })
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->sum('amount');
    // }

    // private function getRelevantUserIdsForTransfer(User $user): array
    // {
    //     $userType = UserType::from($user->user_type);

    //     switch ($userType) {
    //         case UserType::Owner:
    //             // Owner ↔ Master
    //             return User::where('user_type', UserType::Master->value)
    //                 ->orWhere('user_type', UserType::Owner->value)
    //                 ->pluck('id')->toArray();

    //         case UserType::Master:
    //             // Master ↔ Owner and Master ↔ Agent
    //             return User::where(function ($query) use ($user) {
    //                     $query->where('user_type', UserType::Owner->value)
    //                           ->orWhere('user_type', UserType::Agent->value)
    //                           ->orWhere('id', $user->id);
    //                 })
    //                 ->pluck('id')->toArray();

    //         case UserType::Agent:
    //             // Agent ↔ Master, Agent ↔ Player, Agent ↔ SubAgent
    //             return User::where(function ($query) use ($user) {
    //                     $query->where('user_type', UserType::Master->value)
    //                           ->orWhere('user_type', UserType::Player->value)
    //                           ->orWhere('user_type', UserType::SubAgent->value)
    //                           ->orWhere('id', $user->id);
    //                 })
    //                 ->pluck('id')->toArray();

    //         case UserType::SubAgent:
    //             // SubAgent ↔ Agent, SubAgent ↔ Player
    //             return User::where(function ($query) use ($user) {
    //                     $query->where('user_type', UserType::Agent->value)
    //                           ->orWhere('user_type', UserType::Player->value)
    //                           ->orWhere('id', $user->id);
    //                 })
    //                 ->pluck('id')->toArray();

    //         default:
    //             // Player or any other role: only their own logs
    //             return [$user->id];
    //     }
    // }
}
