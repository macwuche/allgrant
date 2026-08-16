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
        Schema::create('others_banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('logo')->nullable();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('processing_time');
            $table->enum('processing_type', ['days', 'hours']);
            $table->string('charge');
            $table->enum('charge_type', ['percentage', 'fixed']);
            $table->decimal('minimum_transfer')->default(0);
            $table->decimal('maximum_transfer')->default(100);
            $table->decimal('daily_limit_maximum_amount', 12)->default(100);
            $table->integer('daily_limit_maximum_count')->default(0);
            $table->decimal('monthly_limit_maximum_amount', 12)->default(1000);
            $table->integer('monthly_limit_maximum_count')->default(0);
            $table->json('field_options')->nullable();
            $table->text('details')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('others_banks');
    }
};
