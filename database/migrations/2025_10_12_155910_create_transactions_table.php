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
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('from_user_id')->nullable();
            $table->string('from_model')->default('User');
            $table->bigInteger('target_id')->nullable();
            $table->string('target_type', 256)->nullable();
            $table->boolean('is_level')->nullable()->default(false);
            $table->string('tnx')->unique();
            $table->string('description')->nullable();
            $table->string('amount');
            $table->string('type');
            $table->string('charge')->default('0');
            $table->string('final_amount')->default('0');
            $table->integer('points')->default(0);
            $table->string('method')->nullable();
            $table->string('pay_currency', 256)->nullable();
            $table->double('pay_amount', null, 0)->nullable();
            $table->text('manual_field_data')->nullable();
            $table->string('wallet_type')->default('default');
            $table->unsignedInteger('card_id')->nullable();
            $table->text('approval_cause')->nullable();
            $table->string('status');
            $table->enum('transfer_type', ['wire_transfer', 'other_bank_transfer', 'own_bank_transfer'])->nullable();
            $table->unsignedBigInteger('beneficiery_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->mediumText('action_message')->nullable();
            $table->mediumText('purpose')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
