<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            $password = (string) env('MWOODI_ADMIN_PASSWORD', '44953322');
            if (!preg_match('/^\d{8}$/', $password)) {
                throw new RuntimeException('MWOODI_ADMIN_PASSWORD must be exactly 8 digits.');
            }

            $admin = DB::table('customers')->where('phone', 'admin')->first();
            $adminData = [
                'name' => 'مدیر MWoodi',
                'password_hash' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
                'customer_no' => 'ADMIN-0001',
                'updated_at' => now(),
            ];
            if ($admin) {
                DB::table('customers')->where('id', $admin->id)->update($adminData);
            } else {
                DB::table('customers')->insert(array_merge($adminData, [
                    'id' => '30000000-0000-0000-0000-000000000001',
                    'phone' => 'admin',
                    'created_at' => now(),
                ]));
            }

            $images = [
                'wood-serving-set-6' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1200&q=85',
                'wood-kitchen-set-7' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=1200&q=85',
                'round-serving-board' => 'https://images.unsplash.com/photo-1600566753051-f0b89df2dd90?auto=format&fit=crop&w=1200&q=85',
                'handmade-wood-bowl' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1200&q=85',
                'wood-spoon-spatula-set' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1200&q=85',
                'rectangular-serving-tray' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=85',
            ];
            foreach ($images as $slug => $url) {
                $product = DB::table('products')->where('slug', $slug)->first();
                if (!$product) continue;
                $exists = DB::table('product_images')->where('product_id', $product->id)->exists();
                if (!$exists) {
                    DB::table('product_images')->insert([
                        'id' => DB::raw('gen_random_uuid()'),
                        'product_id' => $product->id,
                        'url' => $url,
                        'alt_text' => $product->name,
                        'sort_order' => 0,
                        'created_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void {}
};
