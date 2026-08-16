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
        Schema::table('fdr_transactions', function (Blueprint $table) {
            $table->foreign(['fdr_id'], 'f_d_r_transactions_fdr_id_foreign')->references(['id'])->on('fdr')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fdr_transactions', function (Blueprint $table) {
            $table->dropForeign('f_d_r_transactions_fdr_id_foreign');
        });
    }
};
