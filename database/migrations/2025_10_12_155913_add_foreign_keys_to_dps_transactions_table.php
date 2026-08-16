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
        Schema::table('dps_transactions', function (Blueprint $table) {
            $table->foreign(['dps_id'])->references(['id'])->on('dps')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dps_transactions', function (Blueprint $table) {
            $table->dropForeign('dps_transactions_dps_id_foreign');
        });
    }
};
