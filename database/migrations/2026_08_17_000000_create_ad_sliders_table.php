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
        Schema::create('ad_sliders', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('template')->default(1);
            $table->text('title');
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->unsignedInteger('duration')->default(5);
            $table->integer('position')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_sliders');
    }
};
