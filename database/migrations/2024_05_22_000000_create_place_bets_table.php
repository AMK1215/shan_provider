<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_bets', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Batch-level data
            $table->string('member_account');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('player_agent_id')->nullable();
            $table->unsignedBigInteger('product_code');
            $table->string('provider_name')->nullable();
            $table->string('game_type');
            $table->string('operator_code');
            $table->timestamp('request_time')->nullable();
            $table->string('sign');
            $table->string('currency');

            // Transaction-level data
            $table->string('transaction_id')->unique();
            $table->string('action');
            $table->decimal('amount', 20, 4);
            $table->decimal('valid_bet_amount', 20, 4)->nullable();
            $table->decimal('bet_amount', 20, 4)->nullable();
            $table->decimal('prize_amount', 20, 4)->nullable();
            $table->decimal('tip_amount', 20, 4)->nullable();
            $table->string('wager_code')->nullable();
            $table->string('wager_status')->nullable();
            $table->string('round_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('settle_at')->nullable();
            $table->string('game_code')->nullable();
            $table->string('game_name')->nullable();
            $table->string('channel_code')->nullable();
            $table->string('status')->default('pending'); // New: To store the status of the transaction from our side
            // Add before_balance and after_balance if you want to explicitly store them here
            $table->decimal('before_balance', 20, 4)->nullable();
            $table->decimal('balance', 20, 4)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_bets');
    }
};
