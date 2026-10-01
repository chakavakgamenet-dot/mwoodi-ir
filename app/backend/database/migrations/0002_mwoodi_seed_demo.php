<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        DB::statement(
            "INSERT INTO customers (id,name,phone,password_hash,role,is_active)
             VALUES (?,?,?,?,?::user_role,true)
             ON CONFLICT (phone) DO NOTHING",
            ['30000000-0000-0000-0000-000000000001', 'مدیر MWoodi', 'admin', Hash::make('44953322'), 'super_admin']
        );

        DB::statement("INSERT INTO categories (id,name,slug,description,sort_order) VALUES
            ('10000000-0000-0000-0000-000000000001','آشپزخانه','kitchen','محصولات چوبی آشپزخانه',1),
            ('10000000-0000-0000-0000-000000000002','دکوراسیون','decor','محصولات دکوراتیو',2),
            ('10000000-0000-0000-0000-000000000003','پذیرایی','reception','محصولات پذیرایی',3)
            ON CONFLICT (id) DO NOTHING");

        DB::statement("INSERT INTO products (id,category_id,name,slug,description,price,sku,is_active) VALUES
            ('20000000-0000-0000-0000-000000000001','10000000-0000-0000-0000-000000000001','سینی چوبی دست‌ساز','wood-tray','سینی چوبی دست‌ساز MWoodi',890000,'MW-P1',true),
            ('20000000-0000-0000-0000-000000000002','10000000-0000-0000-0000-000000000002','استند چوبی مینیمال','minimal-stand','استند چوبی مینیمال',690000,'MW-P2',true),
            ('20000000-0000-0000-0000-000000000003','10000000-0000-0000-0000-000000000003','جعبه پذیرایی چوبی','serving-box','جعبه پذیرایی چوبی',1250000,'MW-P3',true),
            ('20000000-0000-0000-0000-000000000004','10000000-0000-0000-0000-000000000001','تخته سرو طبیعی','serving-board','تخته سرو طبیعی',980000,'MW-P4',true),
            ('20000000-0000-0000-0000-000000000005','10000000-0000-0000-0000-000000000002','جا شمعی چوبی','candle-holder','جا شمعی چوبی',490000,'MW-P5',true),
            ('20000000-0000-0000-0000-000000000006','10000000-0000-0000-0000-000000000002','باکس چوبی رومیزی','desk-box','باکس چوبی رومیزی',760000,'MW-P6',true)
            ON CONFLICT (id) DO NOTHING");

        DB::statement("INSERT INTO inventory(product_id,quantity,reserved_quantity)
            SELECT id,20,0 FROM products
            ON CONFLICT (product_id) DO NOTHING");
    }

    public function down(): void
    {
        DB::table('customers')->where('phone','admin')->delete();
    }
};
