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
        Schema::create('bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bill_service_id');
            $table->unsignedBigInteger('user_id');
            $table->json('data');
            $table->text('response_data')->nullable();
            $table->decimal('amount', 10);
            $table->decimal('charge', 10);
            $table->enum('status', ['pending', 'completed', 'return']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
