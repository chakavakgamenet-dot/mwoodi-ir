<?php
// Fails fast if Render accidentally boots Laravel against SQLite.
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$driver = DB::connection()->getDriverName();
if ($driver !== 'pgsql') {
    fwrite(STDERR, "[MWoodi] FATAL: database driver is {$driver}; PostgreSQL is required in production.\n");
    exit(1);
}

$hasCustomers = DB::getSchemaBuilder()->hasTable('customers');
if (!$hasCustomers) {
    fwrite(STDERR, "[MWoodi] FATAL: customers table is missing after migrations.\n");
    exit(1);
}

echo "[MWoodi] runtime verified: driver=pgsql, customers=present\n";
