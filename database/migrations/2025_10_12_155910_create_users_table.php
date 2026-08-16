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
            $table->bigIncrements('id');
            $table->bigInteger('portfolio_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->longText('portfolios')->nullable();
            $table->string('avatar', 256)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('country');
            $table->string('phone');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->enum('gender', ['male', 'female', 'other', ''])->default('');
            $table->date('date_of_birth')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('address')->nullable();
            $table->float('balance', 10)->default(0);
            $table->bigInteger('points')->default(0);
            $table->boolean('status')->default(true);
            $table->text('close_reason')->nullable();
            $table->integer('ref_id')->nullable();
            $table->string('account_number', 510)->nullable();
            $table->boolean('kyc')->default(false);
            $table->longText('kyc_credential')->nullable();
            $table->text('google2fa_secret')->nullable();
            $table->boolean('two_fa')->default(false);
            $table->boolean('deposit_status')->default(true);
            $table->boolean('withdraw_status')->default(true);
            $table->boolean('transfer_status')->default(true);
            $table->boolean('otp_status')->default(true);
            $table->boolean('dps_status')->default(true);
            $table->boolean('fdr_status')->nullable()->default(true);
            $table->boolean('loan_status')->default(true);
            $table->boolean('pay_bill_status')->default(true);
            $table->boolean('portfolio_status')->default(true);
            $table->boolean('reward_status')->default(true);
            $table->boolean('referral_status')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->json('notifications_permission')->nullable();
            $table->string('password');
            $table->string('passcode')->nullable();
            $table->rememberToken();
            $table->boolean('phone_verified')->default(false);
            $table->string('otp')->nullable();
            $table->json('custom_fields_data');
            $table->timestamps();
            $table->softDeletes();
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
