<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::unprepared(file_get_contents(database_path('schema.sql')));
    }
    public function down(): void {
        DB::statement('DROP TABLE IF EXISTS audit_logs, site_settings, cart_items, carts, payments, order_items, orders, coupons, inventory, product_images, products, categories, addresses, personal_access_tokens, customers CASCADE');
        DB::statement('DROP TYPE IF EXISTS payment_status CASCADE');
        DB::statement('DROP TYPE IF EXISTS order_status CASCADE');
        DB::statement('DROP TYPE IF EXISTS user_role CASCADE');
    }
};
