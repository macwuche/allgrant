<?php

/**
 * Idempotently creates the 4 Email Inbox permissions (see
 * database/seeders/PermissionSeeder.php) directly against the live
 * database, without going through the full PermissionSeeder (which would
 * fail re-running against permissions that already exist). Safe to run
 * more than once.
 *
 * Run from the app's docroot: php email-inbox-seed-permissions.php
 * (paths below are relative to the current working directory, not this
 * file's location -- deliberately, since `curl -O` drops this file
 * directly into whatever directory you run it from, not a scripts/
 * subfolder to match)
 */
require getcwd().'/vendor/autoload.php';

$app = require getcwd().'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$permissions = [
    ['email-inbox-setting', 'Setting Management'],
    ['email-inbox-view', 'Email Inbox Management'],
    ['email-inbox-send', 'Email Inbox Management'],
    ['email-inbox-manage-addresses', 'Email Inbox Management'],
];

foreach ($permissions as [$name, $category]) {
    \Spatie\Permission\Models\Permission::firstOrCreate(
        ['name' => $name, 'guard_name' => 'admin'],
        ['category' => $category]
    );
}

$names = array_column($permissions, 0);
$found = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')
    ->whereIn('name', $names)
    ->pluck('name')
    ->all();

echo 'Present: '.count($found).' / '.count($names).PHP_EOL;
foreach ($names as $name) {
    echo (in_array($name, $found) ? '  [x] ' : '  [ ] ').$name.PHP_EOL;
}
