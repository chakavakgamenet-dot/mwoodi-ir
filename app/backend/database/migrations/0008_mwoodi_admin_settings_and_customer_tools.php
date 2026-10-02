<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $defaults = [
            'storefront' => [
                'title' => 'Mwoodi | هنر چوب برای خانه',
                'hero_title' => 'چوب را فقط نمی‌فروشیم؛ بخشی از حس خانه می‌کنیم.',
                'hero_text' => 'محصولات چوبی با بافت طبیعی و طراحی کاربردی؛ برای آشپزخانه، پذیرایی و گوشه‌های خاص خانه.',
                'about_title' => 'یک فروشگاه نیست؛ یک کارگاه است.',
                'about_text' => 'سادگی، بافت طبیعی و طراحی کاربردی؛ محصولاتی برای خانه‌های گرم و امروزی.',
                'address' => '',
                'shipping' => 'ارسال سفارش‌ها به آدرس ثبت‌شده مشتری انجام می‌شود.',
                'contact' => '',
                'instagram' => '',
                'telegram' => '',
            ],
            'trust' => [
                'items' => ['چوب طبیعی', 'پرداخت دست‌ساز', 'ارسال مطمئن', 'پشتیبانی قبل و بعد از خرید'],
            ],
            'payment' => [
                'mode' => 'manual',
                'gateway_name' => '',
                'card_holder' => '',
                'card_number' => '',
            ],
        ];

        foreach ($defaults as $key => $value) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', ['storefront','trust','payment'])->delete();
    }
};
