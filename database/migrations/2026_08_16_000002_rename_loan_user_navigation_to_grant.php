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
        DB::table('user_navigations')->where('type', 'loan')->update([
            'type' => 'grant',
            'url' => 'user/grant',
            'name' => 'Grant',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_navigations')->where('type', 'grant')->update([
            'type' => 'loan',
            'url' => 'user/loan',
            'name' => 'Loan',
        ]);
    }
};
