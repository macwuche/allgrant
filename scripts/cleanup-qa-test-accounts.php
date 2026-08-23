$users = DB::table('users')->where('email', 'LIKE', 'qa-%@example.com')->orWhere('username', 'LIKE', 'qatest%')->orWhere('username', 'LIKE', 'qaverify%')->get();
echo '=== QA test accounts found ==='.PHP_EOL;
foreach ($users as $u) { echo "id={$u->id} email={$u->email} username={$u->username}".PHP_EOL; }

if ($users->isEmpty()) {
    echo 'Nothing to clean up.'.PHP_EOL;
} else {
    $ids = $users->pluck('id')->all();
    DB::transaction(function () use ($ids) {
        DB::table('transactions')->whereIn('user_id', $ids)->delete();
        DB::table('login_activities')->whereIn('user_id', $ids)->delete();
        DB::table('users')->whereIn('id', $ids)->delete();
    });
    echo PHP_EOL.'Deleted '.count($ids).' QA user(s) and their transactions/login_activities.'.PHP_EOL;

    $max = DB::table('users')->max('id') ?? 0;
    DB::statement("SELECT setval(pg_get_serial_sequence('users','id'), ".($max + 1).", false)");
    echo 'users id sequence reset to '.($max + 1).PHP_EOL;

    $maxT = DB::table('transactions')->max('id') ?? 0;
    DB::statement("SELECT setval(pg_get_serial_sequence('transactions','id'), ".($maxT + 1).", false)");
    echo 'transactions id sequence reset to '.($maxT + 1).PHP_EOL;
}

echo PHP_EOL.'=== remaining users count ==='.PHP_EOL;
echo DB::table('users')->count().PHP_EOL;
