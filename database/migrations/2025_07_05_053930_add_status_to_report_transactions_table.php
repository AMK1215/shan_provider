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
        Schema::table('report_transactions', function (Blueprint $table) {
            $table->enum('settled_status', ['pending', 'settled_win', 'settled_loss'])->default('pending')->after('member_account');
            $table->string('wager_code')->nullable()->after('settled_status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_transactions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('wager_code');
        });
    }
};
