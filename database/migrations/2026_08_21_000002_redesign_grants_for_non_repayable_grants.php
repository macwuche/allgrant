<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grants', function (Blueprint $table) {
            $table->decimal('commission_amount', 10)->default(0)->after('amount');
            $table->decimal('net_amount', 10)->nullable()->after('commission_amount');
            $table->timestamp('approved_at')->nullable()->after('status');
        });

        // Defensive: this repo's Loan->Grant rename never actually changed the repayment
        // lifecycle until now, so map any pre-existing installment-era statuses onto the
        // new terminal "approved" state before the check constraint stops allowing them.
        // (Confirmed 0 rows in this state on novabridgegrant's live DB at the time this
        // migration was written; written defensively in case another deployment differs.)
        DB::table('grants')->whereIn('status', ['running', 'due', 'completed'])->update(['status' => 'approved']);

        $constraint = DB::selectOne("
            SELECT con.conname
            FROM pg_constraint con
            JOIN pg_class rel ON rel.oid = con.conrelid
            WHERE rel.relname = 'grants'
              AND con.contype = 'c'
              AND pg_get_constraintdef(con.oid) ILIKE '%status%'
        ");

        if ($constraint) {
            DB::statement('ALTER TABLE grants DROP CONSTRAINT '.$constraint->conname);
        }

        DB::statement("ALTER TABLE grants ADD CONSTRAINT grants_status_check CHECK (status IN ('reviewing', 'approved', 'rejected', 'cancelled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $constraint = DB::selectOne("
            SELECT con.conname
            FROM pg_constraint con
            JOIN pg_class rel ON rel.oid = con.conrelid
            WHERE rel.relname = 'grants'
              AND con.contype = 'c'
              AND pg_get_constraintdef(con.oid) ILIKE '%status%'
        ");

        if ($constraint) {
            DB::statement('ALTER TABLE grants DROP CONSTRAINT '.$constraint->conname);
        }

        DB::statement("ALTER TABLE grants ADD CONSTRAINT grants_status_check CHECK (status IN ('reviewing', 'running', 'due', 'completed', 'rejected', 'cancelled'))");

        Schema::table('grants', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'net_amount', 'approved_at']);
        });
    }
};
