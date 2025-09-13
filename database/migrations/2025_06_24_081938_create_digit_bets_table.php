<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('digit_bets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Reference to users table
            $table->string('member_account'); // player name (id)
            $table->string('bet_type');            // 'digit', 'big', 'small'
            $table->unsignedTinyInteger('digit')->nullable(); // Only for digit bets
            $table->decimal('bet_amount', 10, 2);
            $table->decimal('multiplier', 10, 2)->nullable();
            $table->unsignedTinyInteger('rolled_number');
            $table->decimal('win_amount', 10, 2);
            $table->decimal('profit', 10, 2);
            $table->string('status'); // Bet, Settle, Cancel
            $table->dateTime('bet_time')->nullable();
            $table->string('wager_code');
            $table->string('outcome');             // 'win' or 'lose'
            $table->unsignedBigInteger('game_type_id')->nullable();
            // $table->foreignId('game_type_id')->nullable()->constrained('game_types')->onDelete('set null');
            $table->string('game_name')->nullable();
            $table->string('game_type')->nullable();
            $table->string('game_provider_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('game_type_id')->references('id')->on('game_types')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digit_bets');
    }
};
