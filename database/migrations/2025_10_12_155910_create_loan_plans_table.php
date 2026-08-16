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
        Schema::create('loan_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->decimal('minimum_amount', 10)->default(0);
            $table->decimal('maximum_amount', 10)->default(0);
            $table->double('per_installment', 8, 2)->default(0);
            $table->integer('installment_intervel')->default(0);
            $table->integer('total_installment')->default(0);
            $table->integer('loan_fee')->nullable();
            $table->enum('loan_fee_type', ['percentage', 'fixed'])->default('fixed');
            $table->double('admin_profit', 8, 2)->nullable()->default(0);
            $table->text('instructions')->nullable();
            $table->integer('delay_days');
            $table->double('charge', 8, 2);
            $table->enum('charge_type', ['fixed', 'percentage']);
            $table->json('field_options');
            $table->string('badge')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_plans');
    }
};
