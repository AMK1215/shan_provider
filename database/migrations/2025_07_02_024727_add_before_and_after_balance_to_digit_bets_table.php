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
        Schema::table('digit_bets', function (Blueprint $table) {
            $table->decimal('before_balance', 15, 2)->nullable()->after('profit');
            $table->decimal('after_balance', 15, 2)->nullable()->after('before_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digit_bets', function (Blueprint $table) {
            $table->dropColumn('before_balance');
            $table->dropColumn('after_balance');
        });
    }
};
