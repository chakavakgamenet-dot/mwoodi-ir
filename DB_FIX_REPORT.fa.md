# گزارش فیکس نهایی دیتابیس MWoodi

خطای اصلی:
`SQLSTATE[HY000]: General error: 1 no such table: customers (Connection: sqlite, Database: /var/www/database/database.sqlite)`

علت قطعی: سرویس Laravel در زمان درخواست ورود با driver `sqlite` بالا آمده بود، در حالی که ساختار `customers` برای PostgreSQL طراحی شده است.

اصلاحات این نسخه:
- در Render، وجود `DB_URL` یا تنظیمات PostgreSQL باعث اجبار `DB_CONNECTION=pgsql` می‌شود.
- اجرای production روی SQLite عمداً متوقف می‌شود.
- قبل از بالا آمدن وب‌سرور، migrationها اجرا می‌شوند.
- در صورت آماده نبودن موقت PostgreSQL، migration تا ۱۲ بار با فاصله ۳ ثانیه retry می‌شود.
- بعد از migration وجود `customers` و driver `pgsql` بررسی می‌شود.
- مدیر `admin` با رمز دقیق ۸ رقمی `44953322` روی PostgreSQL sync و با `Hash::check` verify می‌شود.
- migration جدید `0007_finalize_admin_and_runtime.php` برای دیتابیس‌های قبلی اضافه شده است.
- `render.yaml` متغیر `MWOODI_ADMIN_PASSWORD=44953322` را تنظیم می‌کند.
- Dockerfile اسکریپت‌های runtime را داخل image کپی می‌کند.
- routeهای `/api/v1/...` بعد از boot لیست می‌شوند تا مسیرهای API قابل بررسی باشند.

## نکته مهم Deploy
پس از جایگزینی فایل‌های Repository در GitHub، باید یک Deploy جدید برای `mwoodi-api` انجام شود. لاگ صحیح باید شامل این موارد باشد:
- `DB_CONNECTION=pgsql`
- `migrations ...`
- `runtime verified: driver=pgsql, customers=present`
- `admin synchronized and password verified`
- routeهای `/api/v1/...`

اگر در لاگ جدید هنوز `Connection: sqlite` دیده شد، آن لاگ مربوط به این نسخه نیست یا سرویس قدیمی Deploy شده است.
