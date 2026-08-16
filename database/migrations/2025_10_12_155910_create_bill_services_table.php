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
        Schema::create('bill_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_id', 50)->nullable()->unique('api_id')->comment('service id from api');
            $table->string('code')->nullable();
            $table->string('method');
            $table->string('name');
            $table->string('currency');
            $table->string('country');
            $table->string('country_code');
            $table->string('provider_code')->comment('flutterwave, bloc etc');
            $table->string('type');
            $table->json('label');
            $table->json('data');
            $table->integer('amount')->default(0);
            $table->integer('min_amount')->default(0);
            $table->integer('max_amount')->default(0);
            $table->double('charge', 8, 2)->default(0);
            $table->enum('charge_type', ['fixed', 'percentage', 'flexible', 'range'])->default('fixed');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_services');
    }
};
