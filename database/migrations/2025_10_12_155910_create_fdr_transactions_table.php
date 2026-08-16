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
        Schema::create('fdr_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fdr_id')->index('f_d_r_transactions_fdr_id_foreign');
            $table->date('given_date')->nullable();
            $table->decimal('given_amount')->default(0);
            $table->decimal('paid_amount', 20, 6)->nullable();
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
        Schema::dropIfExists('fdr_transactions');
    }
};
