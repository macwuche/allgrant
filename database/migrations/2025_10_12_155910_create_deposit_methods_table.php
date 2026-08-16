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
        Schema::create('deposit_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('gateway_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('name');
            $table->enum('type', ['auto', 'manual'])->default('manual');
            $table->string('gateway_code');
            $table->double('charge', null, 0)->default(0);
            $table->enum('charge_type', ['percentage', 'fixed']);
            $table->double('minimum_deposit', null, 0);
            $table->double('maximum_deposit', null, 0);
            $table->double('rate', null, 0);
            $table->string('currency');
            $table->string('currency_symbol');
            $table->longText('field_options')->nullable();
            $table->longText('payment_details')->nullable();
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_methods');
    }
};
