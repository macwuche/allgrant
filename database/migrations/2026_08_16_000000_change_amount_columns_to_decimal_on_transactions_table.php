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
        DB::statement('ALTER TABLE transactions ALTER COLUMN charge DROP DEFAULT');
        DB::statement('ALTER TABLE transactions ALTER COLUMN final_amount DROP DEFAULT');

        DB::statement('ALTER TABLE transactions ALTER COLUMN amount TYPE numeric(20,2) USING amount::numeric');
        DB::statement('ALTER TABLE transactions ALTER COLUMN charge TYPE numeric(20,2) USING charge::numeric');
        DB::statement('ALTER TABLE transactions ALTER COLUMN final_amount TYPE numeric(20,2) USING final_amount::numeric');

        DB::statement("ALTER TABLE transactions ALTER COLUMN charge SET DEFAULT 0");
        DB::statement("ALTER TABLE transactions ALTER COLUMN final_amount SET DEFAULT 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE transactions ALTER COLUMN charge DROP DEFAULT');
        DB::statement('ALTER TABLE transactions ALTER COLUMN final_amount DROP DEFAULT');

        DB::statement('ALTER TABLE transactions ALTER COLUMN amount TYPE varchar(255) USING amount::varchar');
        DB::statement('ALTER TABLE transactions ALTER COLUMN charge TYPE varchar(255) USING charge::varchar');
        DB::statement('ALTER TABLE transactions ALTER COLUMN final_amount TYPE varchar(255) USING final_amount::varchar');

        DB::statement("ALTER TABLE transactions ALTER COLUMN charge SET DEFAULT '0'");
        DB::statement("ALTER TABLE transactions ALTER COLUMN final_amount SET DEFAULT '0'");
    }
};
