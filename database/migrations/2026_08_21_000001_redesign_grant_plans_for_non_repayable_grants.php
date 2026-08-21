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
            $table->double('commission_charge', 8, 2)->default(0)->after('grant_fee_type');
            $table->enum('commission_charge_type', ['fixed', 'percentage'])->default('percentage')->after('commission_charge');
            $table->integer('approval_days')->nullable()->after('commission_charge_type');
        });

        Schema::table('grant_plans', function (Blueprint $table) {
            $table->dropColumn([
                'per_installment',
                'installment_intervel',
                'total_installment',
                'admin_profit',
                'delay_days',
                'charge',
                'charge_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grant_plans', function (Blueprint $table) {
            $table->double('per_installment', 8, 2)->default(0);
            $table->integer('installment_intervel')->default(0);
            $table->integer('total_installment')->default(0);
            $table->double('admin_profit', 8, 2)->nullable()->default(0);
            $table->integer('delay_days')->nullable();
            $table->double('charge', 8, 2)->nullable();
            $table->enum('charge_type', ['fixed', 'percentage'])->nullable();
        });

        Schema::table('grant_plans', function (Blueprint $table) {
            $table->dropColumn(['commission_charge', 'commission_charge_type', 'approval_days']);
        });
    }
};
