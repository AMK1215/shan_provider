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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('limit', 15, 2)->default(50000);
            $table->decimal('limit3', 15, 2)->default(50000);
            $table->decimal('cor', 15, 2)->default(0.1);
            $table->decimal('cor3', 15, 2)->default(0.1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('limit');
            $table->dropColumn('limit3');
            $table->dropColumn('cor');
            $table->dropColumn('cor3');
        });
    }
};
