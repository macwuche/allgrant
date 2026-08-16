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
        Schema::create('push_notification_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('icon', 100)->nullable();
            $table->string('name');
            $table->string('code');
            $table->string('for', 256)->default('User');
            $table->string('title')->nullable();
            $table->longText('message_body');
            $table->text('short_codes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notification_templates');
    }
};
