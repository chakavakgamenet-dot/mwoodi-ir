<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$password = (string) env('MWOODI_ADMIN_PASSWORD', '44953322');
if (!preg_match('/^\d{8}$/', $password)) {
    fwrite(STDERR, "[MWoodi] FATAL: MWOODI_ADMIN_PASSWORD must be exactly 8 digits.\n");
    exit(1);
}

$hash = Hash::make($password);
$admin = DB::table('customers')->where('phone', 'admin')->first();
$data = [
    'name' => 'مدیر MWoodi',
    'password_hash' => $hash,
    'role' => 'super_admin',
    'is_active' => true,
    'customer_no' => 'ADMIN-0001',
    'updated_at' => now(),
];

if ($admin) {
    DB::table('customers')->where('id', $admin->id)->update($data);
} else {
    DB::table('customers')->insert(array_merge($data, [
        'id' => '30000000-0000-0000-0000-000000000001',
        'phone' => 'admin',
        'created_at' => now(),
    ]));
}

$check = DB::table('customers')->where('phone', 'admin')->first();
if (!$check || !Hash::check($password, $check->password_hash) || $check->role !== 'super_admin' || !$check->is_active) {
    fwrite(STDERR, "[MWoodi] FATAL: admin synchronization verification failed.\n");
    exit(1);
}

echo "[MWoodi] admin synchronized and password verified (admin / 8-digit password).\n";
