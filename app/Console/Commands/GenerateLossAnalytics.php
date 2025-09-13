<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateLossAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:loss-summary {--group=member_account : Group by member_account or player_id}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a loss analytics summary from place_bets table';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $groupBy = $this->option('group');
        if (! in_array($groupBy, ['member_account', 'player_id'])) {
            $this->error('Invalid group option. Use --group=member_account or --group=player_id.');

            return;
        }

        $this->info("Generating Loss Analytics Summary grouped by {$groupBy}...");

        $results = DB::table('place_bets')
            ->select(
                $groupBy,
                DB::raw('COUNT(*) as total_rounds'),
                DB::raw('SUM(CASE WHEN amount > 0 THEN 1 ELSE 0 END) as wins'),
                DB::raw('SUM(CASE WHEN amount = 0 THEN 1 ELSE 0 END) as losses')
            )
            ->groupBy($groupBy)
            ->orderByDesc('total_rounds')
            ->get();

        if ($results->isEmpty()) {
            $this->warn('No data found in place_bets.');

            return;
        }

        $tableData = [];

        foreach ($results as $row) {
            $winRate = round(($row->wins / $row->total_rounds) * 100, 2);
            $lossRate = round(($row->losses / $row->total_rounds) * 100, 2);

            $tableData[] = [
                ucfirst($groupBy) => $row->$groupBy,
                'Rounds' => $row->total_rounds,
                'Wins' => $row->wins,
                'Losses' => $row->losses,
                'Win Rate %' => $winRate,
                'Loss Rate %' => $lossRate,
            ];
        }

        $this->table(
            [ucfirst($groupBy), 'Rounds', 'Wins', 'Losses', 'Win Rate %', 'Loss Rate %'],
            $tableData
        );
    }
}
