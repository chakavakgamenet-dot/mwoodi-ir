<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'customer_no')) {
            Schema::table('customers', function (Blueprint $table) { $table->string('customer_no', 40)->nullable()->unique(); });
        }
        if (Schema::hasColumn('customers', 'customer_no')) {
            DB::table('customers')->where('phone','admin')->update(['customer_no'=>'ADMIN-0001']);
        }
        if (!Schema::hasColumn('customers', 'national_id')) {
            Schema::table('customers', function (Blueprint $table) { $table->string('national_id', 20)->nullable()->unique(); });
        }
    }
    public function down(): void {}
};
