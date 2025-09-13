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
        Schema::create('top_ten_withdraws', function (Blueprint $table) {
            $table->id();
            $table->string('player_id')->default('P001122');
            $table->decimal('amount', 64, 2)->default('0.00');
            $table->unsignedBigInteger('admin_id')->nullable(); // Reference to admin
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_ten_withdraws');
    }
};
