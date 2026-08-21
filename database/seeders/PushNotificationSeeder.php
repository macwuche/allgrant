<?php

namespace Database\Seeders;

use App\Models\PushNotificationTemplate;
use Illuminate\Database\Seeder;

class PushNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            // [
            //     'icon' => 'send',
            //     'name' => 'Fund Transfer Request',
            //     'code' => 'fund_transfer_request',
            //     'for' => 'Admin',
            //     'title' => 'Fund transfer requested from [[full_name]]',
            //     'message_body' => 'Fund transfer requested from [[full_name]]',
            //     'short_codes' => '["[[full_name]]","[[charge]]","[[account_number]]","[[account_name]]","[[branch_name]]","[[amount]]","[[total_amount]]"]'
            // ],
            // [
            //     'icon' => 'send',
            //     'name' => 'Fund Transfer Request',
            //     'code' => 'fund_transfer_request',
            //     'for' => 'User',
            //     'title' => 'Your fund transfer request is [[status]]',
            //     'message_body' => 'Your fund transfer request is [[status]]',
            //     'short_codes' => '["[[full_name]]","[[status]]","[[charge]]","[[account_number]]","[[account_name]]","[[branch_name]]","[[amount]]","[[total_amount]]"]'
            // ],
            // [
            //     'icon' => 'wifi',
            //     'name' => 'Wire Transfer Request',
            //     'code' => 'wire_transfer_request',
            //     'for' => 'Admin',
            //     'title' => 'Wire transfer requested from [[full_name]]',
            //     'message_body' => 'Wire transfer requested from [[full_name]]',
            //     'short_codes' => '["[[full_name]]","[[charge]]","[[account_number]]","[[name_of_account]]","[[swift_code]]","[[phone_number]]","[[amount]]","[[total_amount]]"]'
            // ],
            // [
            //     'icon' => 'wifi',
            //     'name' => 'Wire Transfer Request',
            //     'code' => 'wire_transfer_request',
            //     'for' => 'User',
            //     'title' => 'Your wire transfer request is [[status]]',
            //     'message_body' => 'Your wire transfer request is [[status]]',
            //     'short_codes' => '["[[full_name]]","[[status]]","[[charge]]","[[account_number]]","[[name_of_account]]","[[swift_code]]","[[phone_number]]","[[amount]]","[[total_amount]]"]'
            // ]
            // [
            //     'icon' => 'alert-triangle',
            //     'name' => 'Grant Apply',
            //     'code' => 'grant_apply',
            //     'for' => 'Admin',
            //     'title' => '"[[plan_name]]" Grant Application From [[user_name]].',
            //     'message_body' => '"[[plan_name]]" Grant Application From [[user_name]].',
            //     'short_codes' => '["[[user_name]]","[[plan_name]]","[[grant_id]]","[[grant_amount]]","[[application_fee]]","[[approval_days]]"'
            // ],
            // [
            //     'icon' => 'alert-triangle',
            //     'name' => 'Grant Approved',
            //     'code' => 'grant_approved',
            //     'for' => 'User',
            //     'title' => '"[[plan_name]]" Grant Approved.',
            //     'message_body' => '"[[plan_name]]" Grant Approved',
            //     'short_codes' => '["[[plan_name]]","[[grant_id]]","[[grant_amount]]","[[commission_amount]]","[[net_amount]]"'
            // ],
            // [
            //     'icon' => 'alert-triangle',
            //     'name' => 'Grant Rejected',
            //     'code' => 'grant_rejected',
            //     'for' => 'User',
            //     'title' => '"[[plan_name]]" Grant Rejected.',
            //     'message_body' => '"[[plan_name]]" Grant Rejected',
            //     'short_codes' => '["[[plan_name]]","[[grant_id]]","[[grant_amount]]"'
            // ],
            // [
            //     'icon' => 'credit-card',
            //     'name' => 'Bill Pay',
            //     'code' => 'bill_pay',
            //     'for' => 'Admin',
            //     'title' => '[[user_name]] \'s "[[service_name]]" Pay bill completed.',
            //     'message_body' => '[[user_name]] \'s "[[service_name]]" Pay bill completed.',
            //     'short_codes' => '["[[user_name]]","[[service_name]]","[[amount]]","[[charge]]"]'
            // ],
            // [
            //     'icon' => 'pie-chart',
            //     'name' => 'Portfolio Achieve',
            //     'code' => 'portfolio_achieve',
            //     'for' => 'User',
            //     'title' => 'Congratulations, You achieved "[[portfolio_name]]" portfolio badge.',
            //     'message_body' => 'Congratulations, You achieved "[[portfolio_name]]" portfolio badge.',
            //     'short_codes' => '["[[portfolio_name]]"]'
            // ],
            // [
            //     'icon' => 'gift',
            //     'name' => 'Get rewards',
            //     'code' => 'get_rewards',
            //     'for' => 'User',
            //     'title' => 'Congratulations, You have received [[points]] reward points.',
            //     'message_body' => 'Congratulations, You have received [[points]] reward points.',
            //     'short_codes' => '["[[points]]"]'
            // ],
            // [
            //     'icon' => 'message-circle',
            //     'name' => 'Support Ticket Created',
            //     'code' => 'support_ticket_created',
            //     'for' => 'Admin',
            //     'title' => '[[full_name]] \'s open a support ticket.',
            //     'message_body' => '[[full_name]] \'s open a support ticket.',
            //     'short_codes' => '["[[full_name]]"]'
            // ],
            // [
            //     'icon' => 'message-circle',
            //     'name' => 'Support Ticket Reply',
            //     'code' => 'support_ticket_reply',
            //     'for' => 'Admin',
            //     'title' => '[[full_name]] \'s reply a ticket.',
            //     'message_body' => '[[full_name]] \'s reply a ticket.',
            //     'short_codes' => '["[[full_name]]"]'
            // ],
        ];

        PushNotificationTemplate::insert($notifications);
    }
}
