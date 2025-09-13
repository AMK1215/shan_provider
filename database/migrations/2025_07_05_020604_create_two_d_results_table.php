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
        Schema::create('two_d_results', function (Blueprint $table) {
            $table->id();
            $table->string('win_number'); // e.g., '56'
            $table->enum('session', ['morning', 'evening']);
            $table->date('result_date'); // The day this result applies to
            $table->time('result_time')->nullable(); // Time when result declared
            $table->unsignedBigInteger('battle_id')->nullable(); // If you want to link to battle/session
            $table->timestamps();

            $table->unique(['session', 'result_date'], 'unique_session_per_day'); // Prevent duplicate result for same session/day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_d_results');
    }
};
