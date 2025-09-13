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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('currency');
            $table->string('status');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('provider_product_id');
            $table->string('product_code');
            $table->string('product_name');
            $table->string('game_type');
            $table->string('product_title');
            $table->string('short_name')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('game_list_status')->default(1);
            // $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
