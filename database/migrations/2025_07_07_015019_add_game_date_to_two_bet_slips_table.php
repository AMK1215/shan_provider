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
        Schema::table('two_bet_slips', function (Blueprint $table) {
            $table->date('game_date')->nullable()->after('status');
            $table->string('player_name')->nullable()->after('user_id');
            $table->time('game_time')->nullable()->after('game_date');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null')->after('user_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('two_bet_slips', function (Blueprint $table) {
            $table->dropColumn('game_date');
            $table->dropColumn('player_name');
            $table->dropColumn('game_time');
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });
    }
};
