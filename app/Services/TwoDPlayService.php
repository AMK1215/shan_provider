<?php

namespace App\Services;

use App\Helpers\SessionHelper;
use App\Models\TwoDigit\Bettle;
use App\Models\TwoDigit\ChooseDigit;
use App\Models\TwoDigit\HeadClose;
use App\Models\TwoDigit\SlipNumberCounter;
use App\Models\TwoDigit\TwoBet;
use App\Models\TwoDigit\TwoBetSlip;
use App\Models\TwoDigit\TwoDLimit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Ensure this is correctly imported if you are using it

class TwoDPlayService
{
    /**
     * Handles the logic for placing a 2D bet using custom main_balance.
     *
     * @param  float  $totalBetAmount  The total sum of all individual bet amounts.
     * @param  array  $amounts  An array of individual bets, e.g., [['num' => '01', 'amount' => 100], ...].
     * @return array|string Returns an array of over-limit digits, or a success message.
     *
     * @throws \Exception If authentication fails, limits are not set, or other issues occur.
     */
    public function play(float $totalBetAmount, array $amounts)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            throw new \Exception('User not authenticated.');
        }

        $currentBettle = Bettle::where('status', true)->first();
        if (! $currentBettle) {
            throw new \Exception('Betting is currently closed. No active battle session found.');
        }

        $sessionType = SessionHelper::getCurrentSession();
        $gameDate = Carbon::now()->format('Y-m-d');
        $gameTime = $currentBettle->end_time;

        try {
            DB::beginTransaction();

            $userPersonalLimit = $user->two_d_limit ?? null;
            Log::info('User personal 2D limit: '.($userPersonalLimit ?? 'Not Set'));

            $overallTwoDLimit = TwoDLimit::orderBy('created_at', 'desc')->first();
            if (! $overallTwoDLimit) {
                throw new ModelNotFoundException('Overall 2D limit (break) not set.');
            }
            $overallBreakAmount = $overallTwoDLimit->two_d_limit;
            Log::info("Overall 2D break limit: {$overallBreakAmount}");

            if ($user->main_balance < $totalBetAmount) {
                throw new \Exception('Insufficient funds in your main balance.');
            }

            $overLimitDigits = $this->checkAllLimits($amounts, $sessionType, $gameDate, $overallBreakAmount, $userPersonalLimit);
            if (! empty($overLimitDigits)) {
                return $overLimitDigits;
            }

            // Generate a unique slip number ONCE for this entire batch of bets
            // This method now incorporates the counter increment and uniqueness check
            $slipNo = $this->generateUniqueSlipNumber();
            Log::info("Generated Slip No for batch: {$slipNo}");

            $beforeBalance = $user->main_balance;

            // Deduct total amount from user's main_balance
            $user->main_balance -= $totalBetAmount;
            $user->save();

            $afterBalance = $user->main_balance;
            $playerName = $user->user_name;
            $agentId = $user->agent_id;
            $gameDate = Carbon::now()->format('Y-m-d');
            $gameTime = Carbon::now()->format('H:i:s');

            // Create the TwoBetSlip record first
            $twoBetSlip = TwoBetSlip::create([
                'slip_no' => $slipNo,
                'user_id' => $user->id,
                'player_name' => $playerName,
                'agent_id' => $agentId,
                'total_bet_amount' => $totalBetAmount,
                'session' => $sessionType,
                'status' => 'pending',
                'game_date' => $gameDate,
                'game_time' => $gameTime,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
            ]);

            foreach ($amounts as $betDetail) {
                $twoDigit = str_pad($betDetail['num'], 2, '0', STR_PAD_LEFT);
                $subAmount = $betDetail['amount'];

                $chooseDigit = ChooseDigit::where('choose_close_digit', $twoDigit)->first();
                $headClose = HeadClose::where('head_close_digit', substr($twoDigit, 0, 1))->first();
                // Removed: $slip = TwoBetSlip::where('slip_no', $slipNo)->first(); // Not needed, use $twoBetSlip object

                TwoBet::create([
                    'user_id' => $user->id,
                    'member_name' => $user->user_name,
                    'bettle_id' => $currentBettle->id,
                    'choose_digit_id' => $chooseDigit ? $chooseDigit->id : null,
                    'head_close_id' => $headClose ? $headClose->id : null,
                    'agent_id' => $user->agent_id,
                    'bet_number' => $twoDigit,
                    'bet_amount' => $subAmount,
                    // 'total_bet_amount' is now on TwoBetSlip, remove from individual TwoBet
                    'session' => $sessionType,
                    'win_lose' => false,
                    'potential_payout' => 0,
                    'bet_status' => false,
                    'game_date' => $gameDate,
                    'game_time' => $gameTime,
                    'slip_id' => $twoBetSlip->id, // Link to the TwoBetSlip record
                    // 'slip_no', 'before_balance', 'after_balance' are now on TwoBetSlip, remove from individual TwoBet
                ]);
            }

            DB::commit();

            return 'Bet placed successfully.';

        } catch (ModelNotFoundException $e) {
            DB::rollback();
            Log::error('Resource not found in TwoDPlayService: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return 'Required resource (e.g., 2D Limit) not found.';
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in TwoDPlayService play method: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $e->getMessage();
        }
    }

    /**
     * Checks all limits (head close, choose digit, and total bet amount per digit).
     * (No changes needed here based on the slip_no issue)
     */
    protected function checkAllLimits(
        array $amounts,
        string $sessionType,
        string $gameDate,
        float $overallBreakAmount,
        ?float $userPersonalLimit
    ): array {
        $overLimitDigits = [];

        $closedTwoDigits = ChooseDigit::where('status', false)
            ->pluck('choose_close_digit')
            ->map(fn ($digit) => str_pad($digit, 2, '0', STR_PAD_LEFT))
            ->unique()
            ->all();

        $closedHeadDigits = HeadClose::where('status', false)
            ->pluck('head_close_digit')
            ->map(fn ($digit) => (string) $digit)
            ->unique()
            ->all();

        foreach ($amounts as $amount) {
            $twoDigit = str_pad($amount['num'], 2, '0', STR_PAD_LEFT);
            $subAmount = $amount['amount'];
            $headDigitOfSelected = substr($twoDigit, 0, 1);

            if (in_array($headDigitOfSelected, $closedHeadDigits)) {
                $overLimitDigits[] = $twoDigit;

                continue;
            }

            if (in_array($twoDigit, $closedTwoDigits)) {
                $overLimitDigits[] = $twoDigit;

                continue;
            }

            // Important: Now sum from two_bet_slips join two_bets
            // To get accurate "total bet on a digit" for *all* bets, you still query two_bets
            // However, if you are checking limits based on *individual* bets,
            // the previous logic for totalBetAmountForTwoDigit and userBetAmountOnThisDigit holds.
            // Assuming these limits are per digit across *all* bets, not just current slip.
            $totalBetAmountForTwoDigit = DB::table('two_bets')
                ->where('game_date', $gameDate)
                ->where('session', $sessionType)
                ->where('bet_number', $twoDigit)
                ->sum('bet_amount');

            $projectedTotalBetAmount = $totalBetAmountForTwoDigit + $subAmount;

            if ($projectedTotalBetAmount > $overallBreakAmount) {
                $overLimitDigits[] = $twoDigit;

                continue;
            }

            $userBetAmountOnThisDigit = DB::table('two_bets')
                ->where('user_id', Auth::id())
                ->where('game_date', $gameDate)
                ->where('session', $sessionType)
                ->where('bet_number', $twoDigit)
                ->sum('bet_amount');

            $projectedUserBetAmount = $userBetAmountOnThisDigit + $subAmount;

            if ($userPersonalLimit !== null && $projectedUserBetAmount > $userPersonalLimit) {
                $overLimitDigits[] = $twoDigit;

                continue;
            }
        }

        return $overLimitDigits;
    }

    /**
     * Generates a unique slip number by using an atomically incremented counter.
     * It retries if a collision is detected, ensuring ultimate uniqueness.
     */
    protected function generateUniqueSlipNumber(): string
    {
        $maxRetries = 20; // Maximum attempts to generate a unique slip number
        $attempt = 0;

        do {
            $attempt++;

            // Get the base slip number which includes the atomically incremented counter
            // and the desired date/time format.
            $slipNo = $this->generateBaseSlipNumberWithCounter();

            // Check if this generated slip number already exists in the two_bet_slips table.
            // This check is crucial for ensuring uniqueness, especially under high concurrency.
            $exists = DB::table('two_bet_slips')->where('slip_no', $slipNo)->exists(); // IMPORTANT: Check two_bet_slips table

            if (! $exists) {
                return $slipNo; // Found a unique slip number, return it
            }

            // If a collision was detected, log it for monitoring purposes.
            Log::warning("Slip number collision detected (attempt {$attempt}): {$slipNo}");

            // If collision, wait briefly and retry.
            usleep(rand(100, 500)); // Sleep for 100-500 microseconds

            // If max retries reached, it indicates a severe issue.
            if ($attempt >= $maxRetries) {
                Log::critical("Failed to generate unique slip number after {$maxRetries} attempts. Last attempt: {$slipNo}");
                throw new \Exception('Could not generate a unique slip number. Please try again.');
            }

        } while (true);
    }

    /**
     * Generates the base slip number by incrementing the counter within a database transaction.
     * This ensures the counter increment is atomic and reliable.
     * The format will be: NNNNNN-customString-YYYY-MM-DD-HH:MM:SS
     */
    private function generateBaseSlipNumberWithCounter(): string
    {
        $currentDate = Carbon::now()->format('Y-m-d'); // e.g., 2025-07-04
        $currentTime = Carbon::now()->format('H:i:s'); // e.g., 02:28:51
        $customString = 'mk-2d'; // Your custom string

        // IMPORTANT: Use a database transaction and `lockForUpdate()` to ensure atomicity.
        return DB::transaction(function () use ($currentDate, $currentTime, $customString) {
            // Get the current counter record or create it if it doesn't exist.
            // `lockForUpdate()` prevents other transactions from reading/updating this row
            // until this transaction commits, thus preventing race conditions on the counter.
            $counter = SlipNumberCounter::lockForUpdate()->firstOrCreate(
                ['id' => 1],
                ['current_number' => 0]
            );

            // Increment the counter and get the new value. This is now safe.
            $newNumber = $counter->increment('current_number');
            // Format the counter to be a 6-digit number with leading zeros.
            $paddedCounter = sprintf('%06d', $newNumber);

            // Assemble the slip number in the requested format.
            return "{$paddedCounter}-{$customString}-{$currentDate}-{$currentTime}";
        });
    }
}
