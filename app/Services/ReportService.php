<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\PlaceBet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getPlayerSummary(User $user)
    {
        $playerUsernames = $this->getDescendantPlayers($user)->pluck('user_name')->toArray();
        dd($playerUsernames); // Add this temporarily to debug

        $query = PlaceBet::query();

        if ($user->type === UserType::Owner) {
            // all player reports
        } elseif (in_array($user->type, [UserType::Master, UserType::Agent, UserType::SubAgent])) {
            $playerUsernames = $this->getDescendantPlayers($user)->pluck('user_name')->toArray();
            $query->whereIn('member_account', $playerUsernames);
        } elseif ($user->type === UserType::Player) {
            $query->where('member_account', $user->user_name);
        } else {
            return collect();
        }

        return $query->select(
            'member_account',
            DB::raw('SUM(valid_bet_amount) as total_stake'),
            DB::raw('SUM(bet_amount) as total_bet'),
            DB::raw('SUM(prize_amount) as total_win'),
            DB::raw('SUM(CASE WHEN bet_amount > prize_amount THEN bet_amount - prize_amount ELSE 0 END) as total_lost')
        )
            ->groupBy('member_account')
            ->orderBy('member_account')
            ->get();
    }

    private function getDescendantPlayers(User $user)
    {
        $descendants = collect();

        foreach ($user->children as $child) {
            if ($child->type === UserType::Player) {
                $descendants->push($child);
            } else {
                $descendants = $descendants->merge($this->getDescendantPlayers($child));
            }
        }

        return $descendants;
    }
}
