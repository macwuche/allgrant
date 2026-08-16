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
        Schema::create('fdr_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->double('interest_rate', null, 0);
            $table->integer('intervel');
            $table->integer('locked');
            $table->boolean('is_compounding')->default(false);
            $table->decimal('minimum_amount', 16);
            $table->decimal('maximum_amount', 16);
            $table->boolean('is_add_fund_fdr')->default(false);
            $table->boolean('is_deduct_fund_fdr')->default(false);
            $table->boolean('can_cancel')->default(false);
            $table->enum('cancel_type', ['anytime', 'fixed'])->nullable();
            $table->string('cancel_days')->nullable();
            $table->string('cancel_fee')->nullable();
            $table->enum('cancel_fee_type', ['fixed', 'percentage'])->nullable();
            $table->enum('increment_type', ['unlimited', 'fixed'])->default('unlimited');
            $table->boolean('increment_charge_type')->default(false);
            $table->enum('decrement_type', ['unlimited', 'fixed'])->default('unlimited');
            $table->boolean('decrement_charge_type')->default(false);
            $table->decimal('min_increment_amount', 16)->nullable();
            $table->decimal('max_increment_amount', 16)->nullable();
            $table->decimal('min_decrement_amount', 16)->nullable();
            $table->decimal('max_decrement_amount', 16)->nullable();
            $table->integer('increment_times')->nullable();
            $table->integer('decrement_times')->nullable();
            $table->integer('increment_fee')->nullable();
            $table->integer('decrement_fee')->nullable();
            $table->boolean('add_maturity_platform_fee')->default(false);
            $table->integer('maturity_platform_fee')->nullable();
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
        Schema::dropIfExists('fdr_plans');
    }
};
