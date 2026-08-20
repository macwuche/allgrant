$rows = DB::select("SELECT table_name, column_name, data_type, column_default FROM information_schema.columns WHERE (table_name, column_name) IN (('admins','status'),('branch_staff','status'),('categories','is_visible'),('email_templates','footer_status'),('email_templates','bottom_status'),('email_templates','status'),('languages','status'),('push_notification_templates','status'),('set_tunes','status'),('branches','status'),('labels','is_visible'),('navigations','status'),('withdraw_methods','status'),('sms_templates','status'),('plugins','status'),('ad_sliders','status')) ORDER BY table_name, column_name");
foreach ($rows as $r) { echo $r->table_name.'.'.$r->column_name.' | type='.$r->data_type.' | default='.$r->column_default.PHP_EOL; }
echo '--- row counts currently sitting at 0/false (for review, not auto-changed) ---'.PHP_EOL;
$checks = [
    ['admins','status'], ['branch_staff','status'], ['categories','is_visible'],
    ['email_templates','footer_status'], ['email_templates','bottom_status'], ['email_templates','status'],
    ['languages','status'], ['push_notification_templates','status'], ['set_tunes','status'],
    ['branches','status'], ['labels','is_visible'], ['navigations','status'],
    ['withdraw_methods','status'], ['sms_templates','status'], ['plugins','status'], ['ad_sliders','status'],
];
foreach ($checks as [$table, $col]) {
    try {
        $count = DB::table($table)->where($col, 0)->count();
        echo "$table.$col: $count row(s) at 0".PHP_EOL;
    } catch (\Throwable $e) {
        echo "$table.$col: ERROR - ".$e->getMessage().PHP_EOL;
    }
}
