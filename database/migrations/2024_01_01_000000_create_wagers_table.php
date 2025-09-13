<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wagers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable()->unique();
            $table->string('member_account');
            $table->string('round_id');
            $table->string('currency');
            $table->integer('provider_id');
            $table->integer('provider_line_id');
            $table->integer('provider_product_id')->nullable();
            $table->integer('provider_product_oid')->nullable();
            $table->string('game_type');
            $table->string('game_code');
            $table->decimal('valid_bet_amount', 15, 2);
            $table->decimal('bet_amount', 15, 2);
            $table->decimal('prize_amount', 15, 2);
            $table->string('status');
            $table->json('payload')->nullable();
            $table->bigInteger('settled_at')->nullable();
            $table->bigInteger('created_at_api');
            $table->bigInteger('updated_at_api');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wagers');
    }
};
