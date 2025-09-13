<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SpecialBingo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:special-bingo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputFilePath = base_path('app/Console/Commands/json_data/bingo.json');
        $outputFilePath = base_path('app/Console/Commands/data/SpecialBingoModify.json');

        // Load the JSON file
        $jsonData = file_get_contents($inputFilePath);
        $data = json_decode($jsonData, true);

        // Modify the data
        foreach ($data['ProviderGames'] as &$game) {
            $game['game_type_id'] = 1;
            $game['product_id'] = 32;
            // Remove ProviderGameType and ProviderCode if they exist
            if (isset($game['ProviderGameType'])) {
                unset($game['ProviderGameType']);
            }
            if (isset($game['ProviderCode'])) {
                unset($game['ProviderCode']);
            }
        }

        // Save the modified data to a new JSON file
        file_put_contents($outputFilePath, json_encode($data, JSON_PRETTY_PRINT));

        $this->info('Modified data has been saved to '.$outputFilePath);
    }
}
