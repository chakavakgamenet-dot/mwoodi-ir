<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            $now = now();

            // 1) Render test administrator: update if present, create if absent.
            $admin = DB::table('customers')->where('phone', 'admin')->first();
            $adminPayload = [
                'name' => 'مدیر MWoodi',
                'email' => $admin?->email,
                'password_hash' => Hash::make(env('MWOODI_ADMIN_PASSWORD', '44953322')),
                'role' => 'super_admin',
                'is_active' => true,
                'updated_at' => $now,
            ];
            if ($admin) {
                // Do not touch the primary key of an existing customer.
                DB::table('customers')->where('id', $admin->id)->update($adminPayload);
            } else {
                DB::table('customers')->insert([
                    'id' => '30000000-0000-0000-0000-000000000001',
                    'name' => 'مدیر MWoodi',
                    'phone' => 'admin',
                        'password_hash' => Hash::make(env('MWOODI_ADMIN_PASSWORD', '44953322')),
                    'role' => 'super_admin',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 2) Category synchronization is deliberately based on ID first and slug second.
            // The original schema used the Persian word "پذیرایی" as a slug. A previous
            // migration then tried to insert the same fixed UUID with slug "reception".
            // This is the exact cause of the duplicate-key failure on Render.
            $syncCategory = static function (array $desired) use ($now): string {
                $byId = DB::table('categories')->where('id', $desired['id'])->first();
                $bySlug = DB::table('categories')->where('slug', $desired['slug'])->first();

                if ($byId) {
                    if ($bySlug && (string) $bySlug->id !== (string) $byId->id) {
                        // Another row already owns this slug. Keep both primary keys intact
                        // and use the existing slug row as the canonical category.
                        DB::table('categories')->where('id', $bySlug->id)->update([
                            'name' => $desired['name'],
                            'description' => $desired['description'],
                            'sort_order' => $desired['sort_order'],
                            'is_active' => true,
                            'updated_at' => $now,
                        ]);
                        return (string) $bySlug->id;
                    }

                    DB::table('categories')->where('id', $byId->id)->update([
                        'name' => $desired['name'],
                        'slug' => $desired['slug'],
                        'description' => $desired['description'],
                        'sort_order' => $desired['sort_order'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
                    return (string) $byId->id;
                }

                if ($bySlug) {
                    DB::table('categories')->where('id', $bySlug->id)->update([
                        'name' => $desired['name'],
                        'description' => $desired['description'],
                        'sort_order' => $desired['sort_order'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
                    return (string) $bySlug->id;
                }

                DB::table('categories')->insert([
                    'id' => $desired['id'],
                    'name' => $desired['name'],
                    'slug' => $desired['slug'],
                    'description' => $desired['description'],
                    'sort_order' => $desired['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                return (string) $desired['id'];
            };

            $categoryIds = [];
            foreach ([
                ['id' => '10000000-0000-0000-0000-000000000001', 'name' => 'آشپزخانه', 'slug' => 'kitchen', 'description' => 'محصولات چوبی آشپزخانه', 'sort_order' => 1],
                ['id' => '10000000-0000-0000-0000-000000000002', 'name' => 'دکوراسیون', 'slug' => 'decor', 'description' => 'محصولات دکوراتیو', 'sort_order' => 2],
                ['id' => '10000000-0000-0000-0000-000000000003', 'name' => 'پذیرایی', 'slug' => 'reception', 'description' => 'محصولات پذیرایی', 'sort_order' => 3],
                ['id' => '10000000-0000-0000-0000-000000000004', 'name' => 'اکسسوری', 'slug' => 'accessories', 'description' => 'اکسسوری‌های چوبی برای خانه', 'sort_order' => 4],
            ] as $category) {
                $categoryIds[$category['slug']] = $syncCategory($category);
            }

            // 3) The six products from the original v37 storefront are the canonical
            // storefront catalog. The older six Render-demo products are kept in the DB
            // for referential safety but hidden from the active storefront.
            DB::table('products')
                ->whereIn('id', [
                    '20000000-0000-0000-0000-000000000001',
                    '20000000-0000-0000-0000-000000000002',
                    '20000000-0000-0000-0000-000000000003',
                    '20000000-0000-0000-0000-000000000004',
                    '20000000-0000-0000-0000-000000000005',
                    '20000000-0000-0000-0000-000000000006',
                ])
                ->update(['is_active' => false, 'updated_at' => $now]);

            $products = [
                ['id' => '21000000-0000-0000-0000-000000000001', 'category' => 'reception', 'name' => 'سرویس پذیرایی چوبی ۶ نفره', 'slug' => 'wood-serving-set-6', 'description' => 'سرویس کامل پذیرایی با پرداخت طبیعی', 'price' => 4800000, 'stock' => 8, 'wood' => 'راش', 'sku' => 'MW-V37-01'],
                ['id' => '21000000-0000-0000-0000-000000000002', 'category' => 'kitchen', 'name' => 'سرویس آشپزخانه ۷ تکه', 'slug' => 'wood-kitchen-set-7', 'description' => 'مجموعه کاربردی برای آشپزخانه', 'price' => 3200000, 'stock' => 12, 'wood' => 'گردو', 'sku' => 'MW-V37-02'],
                ['id' => '21000000-0000-0000-0000-000000000003', 'category' => 'accessories', 'name' => 'تخته سرو گرد', 'slug' => 'round-serving-board', 'description' => 'مناسب سرو و دکور', 'price' => 850000, 'stock' => 20, 'wood' => 'راش', 'sku' => 'MW-V37-03'],
                ['id' => '21000000-0000-0000-0000-000000000004', 'category' => 'accessories', 'name' => 'کاسه چوبی دست‌ساز', 'slug' => 'handmade-wood-bowl', 'description' => 'پرداخت دست‌ساز و روغن خوراکی', 'price' => 1250000, 'stock' => 6, 'wood' => 'گردو', 'sku' => 'MW-V37-04'],
                ['id' => '21000000-0000-0000-0000-000000000005', 'category' => 'kitchen', 'name' => 'ست قاشق و کفگیر', 'slug' => 'wood-spoon-spatula-set', 'description' => 'مناسب استفاده روزمره', 'price' => 1450000, 'stock' => 15, 'wood' => 'زیتون', 'sku' => 'MW-V37-05'],
                ['id' => '21000000-0000-0000-0000-000000000006', 'category' => 'reception', 'name' => 'سینی پذیرایی مستطیل', 'slug' => 'rectangular-serving-tray', 'description' => 'سینی چوبی با طراحی مینیمال', 'price' => 2100000, 'stock' => 9, 'wood' => 'راش', 'sku' => 'MW-V37-06'],
            ];

            foreach ($products as $product) {
                $categoryId = $categoryIds[$product['category']];
                $existing = DB::table('products')->where('id', $product['id'])->first();
                if (!$existing) {
                    $existing = DB::table('products')->where('slug', $product['slug'])->first();
                }
                if (!$existing) {
                    $existing = DB::table('products')->where('sku', $product['sku'])->first();
                }

                $data = [
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'sku' => $product['sku'],
                    'is_active' => true,
                    'updated_at' => $now,
                ];

                if ($existing) {
                    // Never change the primary key of an existing product: orders and carts
                    // may already reference it.
                    DB::table('products')->where('id', $existing->id)->update($data);
                    $productId = (string) $existing->id;
                } else {
                    DB::table('products')->insert(array_merge($data, [
                        'id' => $product['id'],
                        'created_at' => $now,
                    ]));
                    $productId = $product['id'];
                }

                DB::table('inventory')->updateOrInsert(
                    ['product_id' => $productId],
                    ['quantity' => $product['stock'], 'reserved_quantity' => 0, 'updated_at' => $now]
                );
            }

            // 4) Storefront settings used by the web app.
            $settings = [
                'storefront' => [
                    'title' => 'Mwoodi | هنر چوب برای خانه',
                    'hero_title' => 'چوب را فقط نمی‌فروشیم؛ بخشی از حس خانه می‌کنیم.',
                    'hero_text' => 'محصولات چوبی با بافت طبیعی و طراحی کاربردی؛ برای آشپزخانه، پذیرایی و گوشه‌های خاص خانه.',
                    'about_title' => 'یک فروشگاه نیست؛ یک کارگاه است.',
                    'about_text' => 'سادگی، بافت طبیعی و طراحی کاربردی؛ محصولاتی برای خانه‌های گرم و امروزی.',
                ],
                'contact' => [
                    'address' => '',
                    'shipping' => 'ارسال سفارش‌ها به آدرس ثبت‌شده مشتری انجام می‌شود.',
                    'phone' => '',
                ],
            ];
            foreach ($settings as $key => $value) {
                DB::table('site_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'updated_at' => $now]
                );
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive. Render may already contain real customer/order data.
    }
};
