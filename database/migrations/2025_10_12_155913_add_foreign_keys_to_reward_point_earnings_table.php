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
        Schema::table('reward_point_earnings', function (Blueprint $table) {
            $table->foreign(['portfolio_id'])->references(['id'])->on('portfolios')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_point_earnings', function (Blueprint $table) {
            $table->dropForeign('reward_point_earnings_portfolio_id_foreign');
        });
    }
};
