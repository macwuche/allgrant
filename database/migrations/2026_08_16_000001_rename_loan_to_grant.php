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
        DB::statement('ALTER TABLE loans RENAME TO grants');
        DB::statement('ALTER TABLE loan_plans RENAME TO grant_plans');
        DB::statement('ALTER TABLE loan_transactions RENAME TO grant_transactions');

        DB::statement('ALTER TABLE grants RENAME COLUMN loan_no TO grant_no');
        DB::statement('ALTER TABLE grants RENAME COLUMN loan_plan_id TO grant_plan_id');
        DB::statement('ALTER TABLE grant_plans RENAME COLUMN loan_fee TO grant_fee');
        DB::statement('ALTER TABLE grant_plans RENAME COLUMN loan_fee_type TO grant_fee_type');
        DB::statement('ALTER TABLE grant_transactions RENAME COLUMN loan_id TO grant_id');
        DB::statement('ALTER TABLE users RENAME COLUMN loan_status TO grant_status');

        DB::statement("ALTER SEQUENCE loans_id_seq RENAME TO grants_id_seq");
        DB::statement("ALTER SEQUENCE loan_plans_id_seq RENAME TO grant_plans_id_seq");
        DB::statement("ALTER SEQUENCE loan_transactions_id_seq RENAME TO grant_transactions_id_seq");

        DB::table('permissions')->where('category', 'Loan Management')->update(['category' => 'Grant Management']);

        $permissionRenames = [
            'total-loan' => 'total-grant',
            'user-loan' => 'user-grant',
            'loan-plan-list' => 'grant-plan-list',
            'loan-plan-create' => 'grant-plan-create',
            'loan-plan-edit' => 'grant-plan-edit',
            'loan-plan-delete' => 'grant-plan-delete',
            'pending-loan' => 'pending-grant',
            'running-loan' => 'running-grant',
            'due-loan' => 'due-grant',
            'paid-loan' => 'paid-grant',
            'rejected-loan' => 'rejected-grant',
            'all-loan' => 'all-grant',
            'view-loan-details' => 'view-grant-details',
            'loan-approval' => 'grant-approval',
            'subscribe-user-loan' => 'subscribe-user-grant',
        ];

        foreach ($permissionRenames as $from => $to) {
            DB::table('permissions')->where('name', $from)->update(['name' => $to]);
        }

        $settingRenames = [
            'user_loan' => 'user_grant',
            'loan_referral_bounty' => 'grant_referral_bounty',
            'loan_level' => 'grant_level',
            'loan_passcode_status' => 'grant_passcode_status',
            'kyc_loan' => 'kyc_grant',
        ];

        foreach ($settingRenames as $from => $to) {
            DB::table('settings')->where('name', $from)->update(['name' => $to]);
        }

        DB::table('navigations')->where('type', 'loan')->update(['type' => 'grant']);
        DB::table('navigations')->where('url', 'user/loan')->update(['url' => 'user/grant']);
        DB::table('navigations')->where('name', 'Loan')->update(['name' => 'Grant']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('navigations')->where('name', 'Grant')->update(['name' => 'Loan']);
        DB::table('navigations')->where('url', 'user/grant')->update(['url' => 'user/loan']);
        DB::table('navigations')->where('type', 'grant')->update(['type' => 'loan']);

        $settingRenames = [
            'user_grant' => 'user_loan',
            'grant_referral_bounty' => 'loan_referral_bounty',
            'grant_level' => 'loan_level',
            'grant_passcode_status' => 'loan_passcode_status',
            'kyc_grant' => 'kyc_loan',
        ];

        foreach ($settingRenames as $from => $to) {
            DB::table('settings')->where('name', $from)->update(['name' => $to]);
        }

        $permissionRenames = [
            'total-grant' => 'total-loan',
            'user-grant' => 'user-loan',
            'grant-plan-list' => 'loan-plan-list',
            'grant-plan-create' => 'loan-plan-create',
            'grant-plan-edit' => 'loan-plan-edit',
            'grant-plan-delete' => 'loan-plan-delete',
            'pending-grant' => 'pending-loan',
            'running-grant' => 'running-loan',
            'due-grant' => 'due-loan',
            'paid-grant' => 'paid-loan',
            'rejected-grant' => 'rejected-loan',
            'all-grant' => 'all-loan',
            'view-grant-details' => 'view-loan-details',
            'grant-approval' => 'loan-approval',
            'subscribe-user-grant' => 'subscribe-user-loan',
        ];

        foreach ($permissionRenames as $from => $to) {
            DB::table('permissions')->where('name', $from)->update(['name' => $to]);
        }

        DB::table('permissions')->where('category', 'Grant Management')->update(['category' => 'Loan Management']);

        DB::statement("ALTER SEQUENCE grants_id_seq RENAME TO loans_id_seq");
        DB::statement("ALTER SEQUENCE grant_plans_id_seq RENAME TO loan_plans_id_seq");
        DB::statement("ALTER SEQUENCE grant_transactions_id_seq RENAME TO loan_transactions_id_seq");

        DB::statement('ALTER TABLE users RENAME COLUMN grant_status TO loan_status');
        DB::statement('ALTER TABLE grant_transactions RENAME COLUMN grant_id TO loan_id');
        DB::statement('ALTER TABLE grant_plans RENAME COLUMN grant_fee_type TO loan_fee_type');
        DB::statement('ALTER TABLE grant_plans RENAME COLUMN grant_fee TO loan_fee');
        DB::statement('ALTER TABLE grants RENAME COLUMN grant_plan_id TO loan_plan_id');
        DB::statement('ALTER TABLE grants RENAME COLUMN grant_no TO loan_no');

        DB::statement('ALTER TABLE grant_transactions RENAME TO loan_transactions');
        DB::statement('ALTER TABLE grant_plans RENAME TO loan_plans');
        DB::statement('ALTER TABLE grants RENAME TO loans');
    }
};
