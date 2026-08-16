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
        Schema::create('withdraw_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('icon');
            $table->string('type')->nullable()->default('manual');
            $table->string('gateway_id')->nullable();
            $table->string('name');
            $table->string('currency');
            $table->double('rate', null, 0);
            $table->string('required_time');
            $table->string('required_time_format');
            $table->double('charge', null, 0);
            $table->string('charge_type');
            $table->string('min_withdraw');
            $table->string('max_withdraw');
            $table->text('fields');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_methods');
    }
};
