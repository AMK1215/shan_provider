<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop dependent tables first, then recreate with correct schema
        Schema::dropIfExists('pone_wine_bet_infos');
        Schema::dropIfExists('pone_wine_player_bets');
        Schema::dropIfExists('pone_wine_bets');
        
        Schema::create('pone_wine_bets', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->string('match_id');
            $table->integer('win_number'); // Fixed: integer instead of boolean
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
        
        // Recreate dependent tables
        Schema::create('pone_wine_player_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pone_wine_bet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name');
            $table->decimal('win_lose_amt', 10, 2);
            $table->timestamps();
        });
        
        Schema::create('pone_wine_bet_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('bet_no');
            $table->decimal('bet_amount', 10, 2);
            $table->foreignId('pone_wine_player_bet_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original schema
        Schema::dropIfExists('pone_wine_bets');
        
        Schema::create('pone_wine_bets', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->string('match_id');
            $table->boolean('win_number');
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }
};