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
        Schema::table('pone_wine_bets', function (Blueprint $table) {
            // Change win_number from boolean to integer
            $table->integer('win_number')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pone_wine_bets', function (Blueprint $table) {
            // Revert win_number back to boolean
            $table->boolean('win_number')->change();
        });
    }
};
