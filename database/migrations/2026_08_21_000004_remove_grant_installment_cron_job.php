<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The "grant" cron job collected installment repayments (CronJobController::grant());
        // grants are no longer repaid, so the scheduled job itself is removed. Mirrors how
        // the DPS/FDR cron rows were cleaned up when those modules were removed.
        DB::table('cron_jobs')->where('reserved_method', 'grant')->delete();

        // The admin "Payable Installment"/"Completed Grant" nav items and their permission
        // checks are removed along with the repayment lifecycle; drop the now-dead
        // permission rows so they don't linger unused in role/permission management.
        DB::table('permissions')->whereIn('name', ['due-grant', 'paid-grant'])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->insert([
            ['category' => 'Grant Management', 'name' => 'due-grant', 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Grant Management', 'name' => 'paid-grant', 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('cron_jobs')->insert([
            'name' => 'Grant',
            'next_run_at' => now()->addMinutes(1),
            'schedule' => 86400,
            'type' => 'system',
            'reserved_method' => 'grant',
            'status' => 'running',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
