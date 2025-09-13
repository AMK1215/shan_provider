<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('processed_wager_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('wager_code')->unique();
            $table->unsignedBigInteger('game_type_id');// always 15 here, but keep column
            $table->json('players');                   // stores $callbackPlayers array
            $table->decimal('banker_balance', 15, 2);  // banker->wallet->balanceFloat
            $table->timestampTz('timestamp');          // ISO8601 UTC timestamp
            $table->decimal('total_player_net', 15, 2);// $trueTotalPlayerNet
            $table->decimal('banker_amount_change', 15, 2); // $bankerAmountChange
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_wager_callbacks');
    }
};
