<?php

declare(strict_types=1);

// Final runtime guard for the live Render PostgreSQL database.
// The migration performs the same synchronization once, while this script
// also verifies the actual stored bcrypt hash on every API container boot.
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

$password = (string) (env('MWOODI_ADMIN_PASSWORD') ?: '44953322');
if (!preg_match('/^\d{8}$/', $password)) {
    fwrite(STDERR, "[MWoodi] FATAL: MWOODI_ADMIN_PASSWORD must be exactly 8 digits.\n");
    exit(1);
}

$admin = Customer::where('phone', 'admin')->first();
if (!$admin) {
    fwrite(STDERR, "[MWoodi] FATAL: admin customer was not created by migrations.\n");
    exit(1);
}

$needsUpdate = !Hash::check($password, (string) $admin->password_hash)
    || $admin->role !== 'super_admin'
    || !$admin->is_active
    || $admin->customer_no !== 'ADMIN-0001';

if ($needsUpdate) {
    $admin->password_hash = Hash::make($password);
    $admin->role = 'super_admin';
    $admin->is_active = true;
    $admin->customer_no = 'ADMIN-0001';
    $admin->save();
}

$admin->refresh();
if (!Hash::check($password, (string) $admin->password_hash)) {
    fwrite(STDERR, "[MWoodi] FATAL: admin password verification failed.\n");
    exit(1);
}

fwrite(STDOUT, "[MWoodi] admin synchronized and password verified (admin / 8-digit password).\n");
