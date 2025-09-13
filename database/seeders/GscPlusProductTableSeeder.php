<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GscPlusProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read the JSON file
        $jsonPath = base_path('app/Console/Commands/data/production_product_list.json');
        $jsonData = json_decode(File::get($jsonPath), true);

        // Check if the JSON data is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Invalid JSON data');

            return;
        }

        // Counter for order
        $orderCounter = 1;

        // Insert each product from the JSON data
        foreach ($jsonData['ProviderGames'] as $product) {
            DB::table('products')->insert([
                'provider' => $product['provider'],
                'currency' => $product['currency'],
                'status' => $product['status'],
                'provider_id' => $product['provider_id'],
                'provider_product_id' => $product['product_id'],
                'product_code' => $product['product_code'],
                'product_name' => $product['product_name'],
                'game_type' => $product['game_type'],
                'product_title' => $product['product_title'],
                'short_name' => null,
                'order' => $orderCounter++,
                'game_list_status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
