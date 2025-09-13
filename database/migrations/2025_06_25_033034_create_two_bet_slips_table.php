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
        Schema::create('two_bet_slips', function (Blueprint $table) {
            $table->id();
            $table->string('slip_no')->unique();
            $table->unsignedBigInteger('user_id');
            $table->decimal('total_bet_amount', 12, 2);
            $table->enum('session', ['morning', 'evening']);
            $table->enum('status', ['pending', 'completed', 'cancelled']);
            $table->decimal('before_balance', 12, 2);
            $table->decimal('after_balance', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_bet_slips');
    }
};
