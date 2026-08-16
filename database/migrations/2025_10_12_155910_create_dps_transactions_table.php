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
        Schema::create('dps_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dps_id')->index('dps_transactions_dps_id_foreign');
            $table->date('installment_date');
            $table->date('given_date')->nullable();
            $table->integer('deferment')->default(0);
            $table->decimal('paid_amount')->default(0);
            $table->decimal('charge')->default(0);
            $table->decimal('final_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dps_transactions');
    }
};
