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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->text('member_name');
            $table->string('wager_id');
            $table->unsignedBigInteger('product_code');
            $table->unsignedBigInteger('game_type_id');
            $table->string('game_name');
            $table->string('game_round_id');
            $table->bigInteger('valid_bet_amount');
            $table->bigInteger('bet_amount');
            $table->bigInteger('payout_amount');
            $table->integer('commission_amount');
            $table->integer('jack_pot_amount');
            $table->integer('jp_bet');
            $table->integer('status');
            $table->datetime('created_on');
            $table->datetime('settlement_date');
            $table->datetime('modified_on');
            // $table->unsignedBigInteger("seamless_transaction_id")->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            // $table->integer('agent_commission')->nullable();
            $table->decimal('agent_commission', 15, 2)->default(0.00)->comment('The commission amount earned by the agent');
            $table->timestamps();
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
