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
        Schema::create('pone_wine_player_bets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pone_wine_bet_id');
            $table->string('user_name');
            $table->decimal('win_lose_amt');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pone_wine_bet_id')->references('id')->on('pone_wine_bets')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pone_wine_player_bets');
    }
};
