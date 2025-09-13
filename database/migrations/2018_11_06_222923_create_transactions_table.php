<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->table(), static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('payable');
            $table->unsignedBigInteger('wallet_id');
            $table->enum('type', ['deposit', 'withdraw'])->index();
            $table->decimal('amount', 64, 0);
            $table->boolean('confirmed');
            $table->json('meta')
                ->nullable();

            $table->uuid('uuid')->unique();
            $table->string('event_id', 191)->nullable()->index();
            $table->string('seamless_transaction_id', 191)->nullable()->index();

            // DO NOT define wager_id and note here with $table->...->generatedAs()
            // We will add them via DB::statement below.

            $table->decimal('old_balance', 64, 0)->nullable();
            $table->decimal('new_balance', 64, 0)->nullable();
            $table->string('name', 100)->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable()->index();
            $table->boolean('is_report_generated')->default(false)->index();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id'], 'payable_type_payable_id_ind');
            $table->index(['payable_type', 'payable_id', 'type'], 'payable_type_ind');
            $table->index(['payable_type', 'payable_id', 'confirmed'], 'payable_confirmed_ind');
            $table->index(['payable_type', 'payable_id', 'type', 'confirmed'], 'payable_type_confirmed_ind');
        });
        // Add generated columns using raw SQL statement after table creation
        DB::statement(<<<SQL
    ALTER TABLE {$this->table()}
    ADD COLUMN wager_id BIGINT GENERATED ALWAYS AS ((meta->>'wager_id')::BIGINT) STORED,
    ADD COLUMN note TEXT GENERATED ALWAYS AS (meta->>'note') STORED;
SQL);
    }

    public function down(): void
    {
        Schema::drop($this->table());
    }

    private function table(): string
    {
        return (new Transaction)->getTable();
    }
};
