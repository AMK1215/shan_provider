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
        Schema::create('battles', function (Blueprint $table) {
            $table->id(); // Primary key, auto-incrementing ID

            // Name of the betting battle/session (e.g., 'Morning Battle', 'Evening Battle')
            $table->string('battle_name')->unique();

            // Time of day when the betting session starts (e.g., '00:00:00')
            $table->time('start_time');

            // Time of day when the betting session ends (e.g., '12:00:00')
            $table->time('end_time');

            // Status of the battle (e.g., 1 for active/open, 0 for inactive/closed)
            // This can be used to manually enable/disable a specific battle period.
            $table->boolean('status')->default(1);

            // Date for which this battle period is active.
            // This allows defining specific battle times for different days if needed,
            // or simply for tracking when a battle was defined. For a daily fixed schedule,
            // you might primarily use start_time and end_time, but 'open_date' can be useful
            // for tracking or for future features like setting specific dates for special battles.
            $table->date('open_date')->nullable();

            $table->timestamps(); // Adds 'created_at' and 'updated_at' columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bettles');
    }
};
