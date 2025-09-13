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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_name')->unique();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile', 2000)->nullable();
            $table->decimal('max_score')->default(0.00);
            $table->integer('status')->default(1);
            $table->integer('is_changed_password')->default(1);
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('payment_type_id')->nullable();
            $table->string('agent_logo')->default('default.png');
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('line_id')->nullable();
            $table->decimal('commission')->default(0.00);
            $table->string('referral_code')->nullable();
            $table->string('site_name')->nullable();
            $table->string('site_link')->nullable();
            $table->string('type');
            $table->string('shan_agent_code')->nullable();
            $table->string('shan_agent_name')->nullable();
            $table->string('shan_secret_key')->nullable();
            $table->string('shan_callback_url')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
