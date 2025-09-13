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
        Schema::create('ads_vedios', function (Blueprint $table) {
            $table->id();
            $table->string('video_ads')->default('pp_video.mp4');
            $table->unsignedBigInteger('admin_id')->nullable(); // Reference to admin
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key constraint
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_vedios');
    }
};
