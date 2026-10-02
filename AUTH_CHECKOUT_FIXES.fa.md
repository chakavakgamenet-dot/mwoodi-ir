# اصلاحات نهایی احراز هویت، میهمان، فروش و خرید MWoodi

این نسخه مسیر اجرایی را روی Laravel API واحد قرار می‌دهد و کد legacy v37 فقط reference است.

## احراز هویت
- مشتری: ثبت‌نام و ورود با موبایل/کدملی/شماره مشتری.
- مدیر: `admin` + رمز ۸ رقمی از `MWOODI_ADMIN_PASSWORD`.
- نقش مدیر فقط `seller`, `manager`, `super_admin`.
- token با Sanctum صادر می‌شود.
- logout token جاری را حذف می‌کند.
- پروفایل مشتری قابل ویرایش است.

## مشتری و میهمان
هر دو از `/api/v1/orders` استفاده می‌کنند.
- مشتری: اقلام سبد سرور از روی حساب خوانده می‌شوند.
- میهمان: اقلام از browser ارسال می‌شوند.
- هر دو یک validation و transaction دارند.
- سفارش میهمان `customer_id = NULL` دارد.
- نام، تلفن و آدرس گیرنده در snapshot سفارش ذخیره می‌شود.

## مدیریت
پنل `/admin`:
- داشبورد
- محصولات
- مشتریان
- تنظیمات سایت

تنظیمات در `site_settings` ذخیره می‌شوند و از `/api/v1/settings` برای storefront خوانده می‌شوند.

## دیتابیس
production فقط PostgreSQL است. startup:
1. اتصال PostgreSQL را اجباری می‌کند.
2. migration اجرا می‌کند.
3. وجود customers را بررسی می‌کند.
4. مدیر را sync و password را verify می‌کند.

در نتیجه خطای `no such table: customers` در deployment صحیح نباید رخ دهد؛ اگر چنین خطایی دیده شود، باید log startup و متغیر `DB_URL/DB_CONNECTION` بررسی شود.

## پرداخت
سفارش و رزرو موجودی فعال است. درگاه بانکی واقعی و callback هنوز پیاده‌سازی نشده است.
