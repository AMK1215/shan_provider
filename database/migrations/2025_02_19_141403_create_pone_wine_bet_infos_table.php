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
        Schema::create('pone_wine_bet_infos', function (Blueprint $table) {
            $table->id();
            $table->string('bet_no');
            $table->decimal('bet_amount');
            $table->unsignedBigInteger('pone_wine_player_bet_id');
            $table->foreign('pone_wine_player_bet_id')->references('id')->on('pone_wine_player_bets')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pone_wine_bet_infos');
    }
};
