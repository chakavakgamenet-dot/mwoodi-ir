<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        $password = (string) env('MWOODI_ADMIN_PASSWORD', '44953322');
        if (!preg_match('/^\d{8}$/', $password)) {
            throw new RuntimeException('MWOODI_ADMIN_PASSWORD must be exactly 8 digits.');
        }

        $admin = DB::table('customers')->where('phone', 'admin')->first();
        $payload = [
            'name' => 'مدیر MWoodi',
            'password_hash' => Hash::make($password),
            'role' => 'super_admin',
            'is_active' => true,
            'customer_no' => 'ADMIN-0001',
            'updated_at' => now(),
        ];

        if ($admin) {
            DB::table('customers')->where('id', $admin->id)->update($payload);
            return;
        }

        DB::table('customers')->insert(array_merge($payload, [
            'id' => '30000000-0000-0000-0000-000000000001',
            'phone' => 'admin',
            'created_at' => now(),
        ]));
    }

    public function down(): void {}
};
