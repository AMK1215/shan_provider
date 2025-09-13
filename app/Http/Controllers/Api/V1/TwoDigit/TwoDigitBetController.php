<?php

namespace App\Http\Controllers\Api\V1\TwoDigit;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoD\TwoDPlayRequest;
use App\Models\TwoDigit\Bettle;
use App\Models\TwoDigit\TwoBetSlip;
use App\Services\TwoDPlayService;
use App\Traits\HttpResponses; // Ensure Auth facade is used
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import the service
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TwoDigitBetController extends Controller
{
    use HttpResponses; // For success/error JSON responses

    protected TwoDPlayService $playService;

    public function __construct(TwoDPlayService $playService)
    {
        $this->playService = $playService;
    }

    /**
     * Store a new 2D bet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TwoDPlayRequest $request)
    {
        Log::info('TwoDigitBetController: Store method called.');

        // 1. Authentication check (Laravel's auth middleware should handle this, but an explicit check is fine)
        if (! Auth::check()) {
            Log::warning('TwoDigitBetController: Unauthenticated attempt to place bet.');

            return $this->error('Authentication Required', 'You are not authenticated! Please login.', 401);
        }

        // 2. Check current active betting battle (session)
        $currentBettle = Bettle::where('status', true)->first();
        if (! $currentBettle) {
            Log::info('TwoDigitBetController: Betting is closed at this time.');

            return $this->error(
                'Betting Closed',
                'This 2D lottery Bettle is closed at this time. Welcome back next time!',
                401
            );
        }

        // Retrieve the validated data from the request
        $totalAmount = $request->input('totalAmount');
        $amounts = $request->input('amounts');

        Log::info('TwoDigitBetController: Validated amounts received', [
            'totalAmount' => $totalAmount,
            'amounts' => $amounts,
        ]);

        try {
            // Delegate the core betting logic to the TwoDPlayService
            $result = $this->playService->play($totalAmount, $amounts);

            // Handle different types of results from the service
            if (is_string($result)) {
                // If the service returns a string, it's an error message
                // This covers 'Insufficient funds.', 'Resource not found.', or general exceptions
                if ($result === 'Insufficient funds in your main balance.') {
                    return $this->error('Insufficient Funds', 'လက်ကျန်ငွေ မလုံလောက်ပါ။', 400); // 400 Bad Request for client-side issue
                } elseif ($result === 'Required resource (e.g., 2D Limit) not found.') {
                    return $this->error('Configuration Error', '2D limit configuration is missing. Please contact support.', 500);
                } elseif ($result === 'Betting is currently closed. No active battle session found.') {
                    // Although checked above, defensive check in service is good.
                    return $this->error('Betting Closed', 'This 2D lottery Bettle is closed at this time. Welcome back next time!', 401);
                } elseif ($result === 'Bet placed successfully.') {
                    return $this->success(null, 'ထီအောင်မြင်စွာ ထိုးပြီးပါပြီ။');
                } else {
                    // General service-side error
                    return $this->error('Betting Failed', $result, 400); // 400 or 500 depending on cause
                }
            } elseif (is_array($result) && ! empty($result)) {
                // If the service returns an array, it contains over-limit digits
                $digitStrings = collect($result)->map(fn ($digit) => "'{$digit}'")->implode(', ');
                $message = "သင့်ရွှေးချယ်ထားသော {$digitStrings} ဂဏန်းမှာ သတ်မှတ် အမောင့်ထက်ကျော်လွန်ပါသောကြောင့် ကံစမ်း၍မရနိုင်ပါ။";

                return $this->error('Over Limit', $message, 400); // 400 Bad Request
            } else {
                // Defensive fallback: treat as error
                return $this->error('Betting Failed', 'Unknown error occurred.', 400);
            }

        } catch (\Exception $e) {
            // Catch any unexpected exceptions from the service layer
            Log::error('TwoDigitBetController: Uncaught exception in store method: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $this->error('Server Error', 'An unexpected error occurred. Please try again later.', 500);
        }

    }

    // slip no
    public function myBetSlips(Request $request)
    {
        $user = Auth::user();
        $currentTime = now();
        $currentHour = (int) $currentTime->format('H');
        $currentMinute = (int) $currentTime->format('i');

        // Determine which session to show based on current time
        // Morning session: 00:00 - 12:00 (actual session time)
        // Evening session: 12:04 - 16:30 (actual session time)

        // Display times (extended for user experience):
        // Morning data: Show until 1:00 PM
        // Evening data: Show until 6:00 PM

        // Convert current time to minutes for easier comparison
        $currentTimeInMinutes = ($currentHour * 60) + $currentMinute;
        $morningDisplayEndTime = 13 * 60; // 1:00 PM in minutes
        $eveningStartTime = (12 * 60) + 4; // 12:04 PM in minutes
        $eveningDisplayEndTime = 18 * 60; // 6:00 PM in minutes

        if ($currentTimeInMinutes <= $morningDisplayEndTime) {
            // Before or at 1:00 PM - show morning session
            $session = 'morning';
            $gameDate = $currentTime->format('Y-m-d');
        } elseif ($currentTimeInMinutes >= $eveningStartTime && $currentTimeInMinutes <= $eveningDisplayEndTime) {
            // Between 12:04 PM and 6:00 PM - show evening session
            $session = 'evening';
            $gameDate = $currentTime->format('Y-m-d');
        } else {
            // After 6:00 PM - show today's evening session (most recent)
            $session = 'evening';
            $gameDate = $currentTime->format('Y-m-d');
        }

        $betSlips = TwoBetSlip::with('twoBets')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('status', 'completed');
            })
            ->where('session', $session)
            ->where('game_date', $gameDate)
            ->orderByDesc('created_at')
            ->get();

        Log::info('Morning slips', [
            'user_id' => $user->id,
            'session' => $session,
            'game_date' => $gameDate,
            'count' => $betSlips->count(),
            'ids' => $betSlips->pluck('id'),
        ]);

        return $this->success($betSlips, "Your {$session} session two-digit bet slips retrieved successfully.");
    }

    // evening session slip
    public function eveningSessionSlip(Request $request)
    {
        $user = Auth::user();
        $currentTime = now();
        $currentHour = (int) $currentTime->format('H');
        $currentMinute = (int) $currentTime->format('i');

        // Evening session logic
        $currentTimeInMinutes = ($currentHour * 60) + $currentMinute;
        $eveningStartTime = (12 * 60) + 4; // 12:04 PM in minutes
        $eveningDisplayEndTime = 18 * 60; // 6:00 PM in minutes

        $session = 'evening';
        $gameDate = $currentTime->format('Y-m-d');

        // Only show evening data during appropriate times
        // if ($currentTimeInMinutes < $eveningStartTime) {
        //     // Before 12:04 PM - no evening data yet
        //     return $this->success([], "No evening session data available yet.");
        // }

        $betSlips = TwoBetSlip::with('twoBets')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('status', 'completed');
            })
            ->where('session', $session)
            ->where('game_date', $gameDate)
            ->orderByDesc('created_at')
            ->get();

        Log::info('Evening slips', [
            'user_id' => $user->id,
            'session' => $session,
            'game_date' => $gameDate,
            'count' => $betSlips->count(),
            'ids' => $betSlips->pluck('id'),
        ]);

        return $this->success($betSlips, "Your {$session} session two-digit bet slips retrieved successfully.");
    }

    // winner list

    public function dailyWinners(Request $request)
    {
        $user = Auth::user();

        // ✅ Allow only authenticated player
        // if ($user->type !== \App\Enums\UserType::Player) {
        //     return $this->error(null, 'Only players can access winner list.', 403);
        // }

        $date = $request->input('date') ?? now()->format('Y-m-d');
        $session = $request->input('session'); // optional

        if ($session && ! in_array($session, ['morning', 'evening'])) {
            return $this->error(null, 'Invalid session.', 422);
        }

        // ✅ Single session provided
        if ($session) {
            $result = DB::table('two_d_results')
                ->where('result_date', $date)
                ->where('session', $session)
                ->first();

            if (! $result || ! $result->win_number) {
                return $this->error(null, 'No result found for given session and date.', 404);
            }

            // ✅ Fetch all player winners (by win_digit)
            $winners = DB::table('two_bets')
                ->where('game_date', $date)
                ->where('session', $session)
                ->where('bet_number', $result->win_number)
                ->where('win_lose', true)
                ->select('member_name', 'bet_amount', DB::raw('bet_amount * 80 as win_amount'))
                ->get();

            return $this->success([
                'date' => $result->result_date,
                'session' => $result->session,
                'win_digit' => $result->win_number,
                'winners' => $winners,
            ], '2D winner list retrieved');
        }

        // ✅ Default case: show latest 3 sessions with winners
        $recentResults = DB::table('two_d_results')
            ->orderByDesc('result_date')
            ->orderByDesc('session')
            ->limit(10)
            ->get();

        $data = [];

        foreach ($recentResults as $res) {
            $winners = DB::table('two_bets')
                ->where('game_date', $res->result_date)
                ->where('session', $res->session)
                ->where('bet_number', $res->win_number)
                ->where('win_lose', true)
                ->select('member_name', 'bet_amount', DB::raw('bet_amount * 80 as win_amount'))
                ->get();

            $data[] = [
                'date' => $res->result_date,
                'session' => $res->session,
                'win_digit' => $res->win_number,
                'winners' => $winners,
            ];
        }

        return $this->success([
            'latest_results' => $data,
        ], 'Latest 2D winners (with winners list)');
    }
}

/*
 $betSlips = TwoBetSlip::with('twoBets')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('session', $session)
            ->where('game_date', $gameDate)
            ->orderByDesc('created_at')
            ->get();
*/
