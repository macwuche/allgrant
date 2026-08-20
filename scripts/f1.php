DB::transaction(function () {
    $alters = [
        "ALTER TABLE admins ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE branch_staff ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE branches ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE categories ALTER COLUMN is_visible SET DEFAULT 1",
        "ALTER TABLE email_templates ALTER COLUMN bottom_status SET DEFAULT 1",
        "ALTER TABLE email_templates ALTER COLUMN footer_status SET DEFAULT 1",
        "ALTER TABLE email_templates ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE labels ALTER COLUMN is_visible SET DEFAULT 1",
        "ALTER TABLE languages ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE navigations ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE plugins ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE push_notification_templates ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE set_tunes ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE sms_templates ALTER COLUMN status SET DEFAULT 1",
        "ALTER TABLE withdraw_methods ALTER COLUMN status SET DEFAULT 1",
    ];
    foreach ($alters as $sql) {
        DB::statement($sql);
        echo "OK: $sql".PHP_EOL;
    }
});
echo '--- re-verify (all should now read default=1) ---'.PHP_EOL;
$rows = DB::select("SELECT table_name, column_name, data_type, column_default FROM information_schema.columns WHERE (table_name, column_name) IN (('admins','status'),('branch_staff','status'),('categories','is_visible'),('email_templates','footer_status'),('email_templates','bottom_status'),('email_templates','status'),('languages','status'),('push_notification_templates','status'),('set_tunes','status'),('branches','status'),('labels','is_visible'),('navigations','status'),('withdraw_methods','status'),('sms_templates','status'),('plugins','status')) ORDER BY table_name, column_name");
foreach ($rows as $r) { echo $r->table_name.'.'.$r->column_name.' | default='.$r->column_default.PHP_EOL; }
