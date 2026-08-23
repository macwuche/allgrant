$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'grant_plans' ORDER BY column_name");
$colNames = collect($cols)->pluck('column_name')->all();
echo "=== grant_plans columns ===".PHP_EOL;
echo implode(', ', $colNames).PHP_EOL;

$oldCols = ['per_installment', 'installment_intervel', 'total_installment', 'admin_profit', 'delay_days', 'charge', 'charge_type'];
$newCols = ['commission_charge', 'commission_charge_type', 'approval_days'];
echo PHP_EOL.'-- old columns still present (should be NONE): '.implode(', ', array_intersect($oldCols, $colNames)).PHP_EOL;
echo '-- new columns present (should be all 3): '.implode(', ', array_intersect($newCols, $colNames)).PHP_EOL;

echo PHP_EOL.'=== grants table: commission_amount/net_amount/approved_at present? ==='.PHP_EOL;
$grantCols = collect(DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'grants'"))->pluck('column_name')->all();
echo implode(', ', array_intersect(['commission_amount', 'net_amount', 'approved_at'], $grantCols)).PHP_EOL;

echo PHP_EOL.'=== grants_status_check constraint ==='.PHP_EOL;
$check = DB::select("SELECT pg_get_constraintdef(oid) AS def FROM pg_constraint WHERE conname = 'grants_status_check'");
echo ($check[0]->def ?? 'NOT FOUND').PHP_EOL;

echo PHP_EOL.'=== grant_transactions table (should be gone / NULL) ==='.PHP_EOL;
$exists = DB::select("SELECT to_regclass('public.grant_transactions') AS t");
echo ($exists[0]->t ?? 'NULL').PHP_EOL;

echo PHP_EOL.'=== migrations ledger: today\'s 4 rows present? ==='.PHP_EOL;
$rows = DB::select("SELECT migration, batch FROM migrations WHERE migration LIKE '2026_08_21_%' ORDER BY migration");
foreach ($rows as $r) { echo $r->migration.' | batch '.$r->batch.PHP_EOL; }
if (empty($rows)) { echo 'NONE FOUND'.PHP_EOL; }

echo PHP_EOL.'=== grant_plans row count and sample statuses ==='.PHP_EOL;
echo 'count: '.DB::table('grant_plans')->count().PHP_EOL;
foreach (DB::table('grant_plans')->select('id', 'name', 'status', 'commission_charge', 'approval_days')->get() as $p) {
    echo "id={$p->id} name={$p->name} status={$p->status} commission_charge={$p->commission_charge} approval_days={$p->approval_days}".PHP_EOL;
}

echo PHP_EOL.'=== grants row count ==='.PHP_EOL;
echo 'count: '.DB::table('grants')->count().PHP_EOL;
