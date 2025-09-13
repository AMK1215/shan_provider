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
        Schema::create('wager_lists', function (Blueprint $table) {
            $table->id();
            $table->string('member_account');
            $table->unsignedBigInteger('round_id');
            $table->string('currency', 10);
            $table->unsignedBigInteger('provider_id');
            $table->string('provider_line_id');
            $table->string('game_type');
            $table->string('game_code');
            $table->decimal('valid_bet_amount', 15, 2);
            $table->decimal('bet_amount', 15, 2);
            $table->decimal('prize_amount', 15, 2);
            $table->string('status')->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wager_lists');
    }
};
