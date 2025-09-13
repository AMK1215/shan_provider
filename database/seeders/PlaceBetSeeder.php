<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\PlaceBet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlaceBetSeeder extends Seeder
{
    private const GAME_TYPES = ['slot', 'table', 'card', 'arcade', 'lottery'];

    private const PROVIDERS = ['pragmatic', 'evolution', 'netent', 'playtech', 'microgaming'];

    private const ACTIONS = ['bet', 'win', 'refund'];

    private const STATUSES = ['pending', 'completed', 'cancelled'];

    private const CURRENCIES = ['MMK', 'THB', 'USD'];

    public function run(): void
    {
        try {
            DB::beginTransaction();

            // Log total users in database
            $totalUsers = User::count();
            Log::info("Total users in database: {$totalUsers}");

            // Get all players with their relationships
            $players = User::where('type', UserType::Player->value)
                ->with(['agent' => function ($query) {
                    $query->with('agent');
                }])
                ->get();

            // Log detailed player information
            Log::info('Player query SQL: '.User::where('type', UserType::Player->value)->toSql());
            Log::info('Player query bindings: '.json_encode(User::where('type', UserType::Player->value)->getBindings()));

            if ($players->isEmpty()) {
                // Check if any users exist at all
                $allUsers = User::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->get();

                Log::error('No players found in the database. Current user distribution:', [
                    'user_types' => $allUsers->toArray(),
                ]);

                throw new \RuntimeException(
                    'No players found in the database. Please ensure UsersTableSeeder has been run successfully. '.
                    'Current user distribution: '.json_encode($allUsers->toArray())
                );
            }

            Log::info("Found {$players->count()} players in the database");

            $batchSize = 1000;
            $totalBets = 10000;
            $betsPerPlayer = ceil($totalBets / $players->count());

            Log::info("Will create {$betsPerPlayer} bets per player");

            $totalCreated = 0;
            foreach ($players as $player) {
                $created = $this->createBetsForPlayer($player, $betsPerPlayer, $batchSize);
                $totalCreated += $created;
                Log::info("Created {$created} bets for player {$player->user_name}");
            }

            DB::commit();
            Log::info("Successfully created {$totalCreated} bet records");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in PlaceBetSeeder: '.$e->getMessage());
            throw $e;
        }
    }

    private function createBetsForPlayer($player, int $betsPerPlayer, int $batchSize): int
    {
        $bets = [];
        $currentBalance = 1000000; // Starting balance
        $created = 0;

        for ($i = 0; $i < $betsPerPlayer; $i++) {
            $betAmount = $this->generateBetAmount();
            $prizeAmount = $this->generatePrizeAmount($betAmount);
            $beforeBalance = $currentBalance;
            $currentBalance = $beforeBalance - $betAmount + $prizeAmount;

            $bet = [
                'member_account' => $player->user_name,
                'player_id' => $player->id,
                'player_agent_id' => $player->agent_id,
                'product_code' => rand(1000, 9999),
                'provider_name' => $this->getRandomProvider(),
                'game_type' => $this->getRandomGameType(),
                'operator_code' => 'OP'.rand(1000, 9999),
                'request_time' => now()->subMinutes(rand(1, 1000000)),
                'sign' => Str::random(32),
                'currency' => $this->getRandomCurrency(),
                'transaction_id' => Str::uuid(),
                'action' => $this->getRandomAction(),
                'amount' => $betAmount,
                'valid_bet_amount' => $betAmount,
                'bet_amount' => $betAmount,
                'prize_amount' => $prizeAmount,
                'tip_amount' => 0,
                'wager_code' => 'WAGER'.rand(1000, 9999),
                'wager_status' => 'completed',
                'round_id' => Str::uuid(),
                'payload' => json_encode(['game_details' => 'Sample game details']),
                'settle_at' => now(),
                'game_code' => 'GAME'.rand(1000, 9999),
                'game_name' => 'Game '.rand(1, 100),
                'channel_code' => 'CH'.rand(1000, 9999),
                'status' => $this->getRandomStatus(),
                'before_balance' => $beforeBalance,
                'balance' => $currentBalance,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $bets[] = $bet;
            $created++;

            if (count($bets) >= $batchSize) {
                PlaceBet::insert($bets);
                $bets = [];
            }
        }

        if (! empty($bets)) {
            PlaceBet::insert($bets);
        }

        return $created;
    }

    private function generateBetAmount(): float
    {
        // Generate bet amounts between 100 and 100000
        return round(rand(100, 100000) / 100, 2);
    }

    private function generatePrizeAmount(float $betAmount): float
    {
        // 70% chance of winning, with prize between 0 and 5x bet amount
        if (rand(1, 100) <= 70) {
            return round($betAmount * (rand(0, 500) / 100), 2);
        }

        return 0;
    }

    private function getRandomGameType(): string
    {
        return self::GAME_TYPES[array_rand(self::GAME_TYPES)];
    }

    private function getRandomProvider(): string
    {
        return self::PROVIDERS[array_rand(self::PROVIDERS)];
    }

    private function getRandomAction(): string
    {
        return self::ACTIONS[array_rand(self::ACTIONS)];
    }

    private function getRandomStatus(): string
    {
        return self::STATUSES[array_rand(self::STATUSES)];
    }

    private function getRandomCurrency(): string
    {
        return self::CURRENCIES[array_rand(self::CURRENCIES)];
    }
}
