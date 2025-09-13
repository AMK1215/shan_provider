<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder; // Make sure to import your Product model

class ShanProductCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the product code you want to seed
        $productCodeToSeed = '100200'; // Example product code

        // Create or update a single product record
        // updateOrCreate is good to prevent duplicates if the seeder is run multiple times
        Product::updateOrCreate(
            ['product_code' => $productCodeToSeed], // Condition to find the record
            [
                'provider' => 'ShanKoeMee',
                'currency' => 'MMK', // Or your default currency
                'status' => 'ACTIVATED',
                'provider_id' => 1, // Example ID, adjust as per your system
                'provider_product_id' => 101, // Example ID, adjust as per your provider's product ID
                'product_name' => 'Shan Koe Mee',
                'game_type' => 'CardGame', // Example game type
                'product_title' => 'Shan Koe Mee Online',
                'short_name' => 'SKM',
                'order' => 1,
                'game_list_status' => true,
                // 'image' => null, // If you uncommented 'image' in migration, set a default or null
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("Product with code '{$productCodeToSeed}' seeded successfully.");
    }
}
