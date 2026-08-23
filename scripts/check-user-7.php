$user = DB::table('users')->where('id', 7)->first();

if (! $user) {
    echo 'No user with id=7 found.'.PHP_EOL;
} else {
    echo '=== user id 7 ==='.PHP_EOL;
    echo "name: {$user->first_name} {$user->last_name}".PHP_EOL;
    echo "email: {$user->email}".PHP_EOL;
    echo "username: {$user->username}".PHP_EOL;
    echo "phone: {$user->phone}".PHP_EOL;
    echo "country: {$user->country}".PHP_EOL;
    echo "created_at: {$user->created_at}".PHP_EOL;
    echo "status: {$user->status}".PHP_EOL;
    echo "balance: {$user->balance}".PHP_EOL;

    echo PHP_EOL.'=== login activity ==='.PHP_EOL;
    $logins = DB::table('login_activities')->where('user_id', 7)->orderByDesc('id')->get();
    if ($logins->isEmpty()) {
        echo 'No login_activities rows for this user.'.PHP_EOL;
    } else {
        foreach ($logins as $l) {
            echo "id={$l->id} ip={$l->ip} time={$l->created_at}".PHP_EOL;
        }
    }

    echo PHP_EOL.'=== transactions ==='.PHP_EOL;
    $txns = DB::table('transactions')->where('user_id', 7)->orderBy('id')->get();
    foreach ($txns as $t) {
        echo "id={$t->id} type={$t->type} status={$t->status} amount={$t->amount} created_at={$t->created_at}".PHP_EOL;
    }
}
