<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShanProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update the ShanKomee product
        Product::updateOrCreate(
            ['product_code' => '100200'], // Condition to find the record
            [
                'provider' => 'ShanKomee',
                'currency' => 'MMK',
                'status' => 'ACTIVATED',
                'provider_id' => 102,
                'provider_product_id' => 100200,
                'product_name' => 'shan_komee',
                'game_type' => 'CARD_GAME',
                'product_title' => 'ShanKomee',
                'short_name' => 'SKM',
                'order' => 1,
                'game_list_status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create or update the ShanKomee product with code 100100
        Product::updateOrCreate(
            ['product_code' => '100100'], // Condition to find the record
            [
                'provider' => 'ShanKomee',
                'currency' => 'MMK',
                'status' => 'ACTIVATED',
                'provider_id' => 101,
                'provider_product_id' => 100100,
                'product_name' => 'shan_komee_basic',
                'game_type' => 'CARD_GAME',
                'product_title' => 'ShanKomee Basic',
                'short_name' => 'SKM_B',
                'order' => 2,
                'game_list_status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("ShanKomee products with codes '100100' and '100200' seeded successfully.");
    }
}
