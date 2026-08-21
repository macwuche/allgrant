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
        // A grant is no longer repaid in installments (see
        // 2026_08_21_000002_redesign_grants_for_non_repayable_grants.php), so the
        // installment-schedule table has nothing left to track.
        Schema::dropIfExists('grant_transactions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('grant_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('grant_id');
            $table->date('installment_date');
            $table->date('given_date')->nullable();
            $table->integer('deferment')->default(0);
            $table->decimal('paid_amount')->default(0);
            $table->decimal('charge')->default(0);
            $table->decimal('final_amount')->default(0);
            $table->timestamps();
        });
    }
};
