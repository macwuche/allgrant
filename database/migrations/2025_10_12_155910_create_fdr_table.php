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
        Schema::create('fdr', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fdr_id')->unique();
            $table->unsignedBigInteger('user_id')->index('fdr_user_id_foreign');
            $table->unsignedBigInteger('fdr_plan_id');
            $table->decimal('amount', 10)->nullable()->default(0);
            $table->integer('increment_count')->default(0);
            $table->integer('decrement_count')->default(0);
            $table->date('end_date')->nullable();
            $table->dateTime('cancel_date')->nullable();
            $table->decimal('cancel_fee')->nullable();
            $table->enum('status', ['running', 'closed', 'completed'])->default('running');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fdr');
    }
};
