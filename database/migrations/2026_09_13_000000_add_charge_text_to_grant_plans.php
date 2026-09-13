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
        Schema::table('grant_plans', function (Blueprint $table) {
            $table->text('application_charge_text')->nullable()->after('grant_fee_type');
            $table->text('commission_charge_text')->nullable()->after('commission_charge_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grant_plans', function (Blueprint $table) {
            $table->dropColumn(['application_charge_text', 'commission_charge_text']);
        });
    }
};
