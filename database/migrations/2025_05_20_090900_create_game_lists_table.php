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
        Schema::create('game_lists', function (Blueprint $table) {
            $table->id();
            $table->string('game_code');
            $table->string('game_name');
            $table->string('game_type');
            $table->string('image_url');
            $table->unsignedBigInteger('provider_product_id');
            $table->unsignedBigInteger('game_type_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('product_code');
            $table->text('support_currency')->nullable();
            $table->string('status')->default('1');
            $table->boolean('is_active')->default(true);
            $table->string('provider')->nullable();
            $table->integer('order')->default(0);
            $table->string('hot_status')->default('0');
            $table->boolean('game_list_status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_lists');
    }
};
