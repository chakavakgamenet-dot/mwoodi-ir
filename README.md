# MWoodi — نسخه یکپارچه GitHub / Render

این بسته نسخه نهایی یکپارچه فروشگاه MWoodi است. رابط v37 به‌عنوان مرجع حفظ شده، اما مسیر اجرایی سایت دیگر به localStorage قدیمی v37 برای احراز هویت، مشتریان، سفارش‌ها یا مدیریت وابسته نیست.

## معماری نهایی
- Frontend: Next.js 15 + React 19 + TypeScript
- Backend: Laravel 12 + Sanctum
- Database production: PostgreSQL
- Render: دو Web Service رایگان + PostgreSQL
- احراز هویت: API Bearer Token
- سبد مشتری: PostgreSQL
- سبد میهمان: localStorage فقط تا قبل از ثبت سفارش
- سفارش مشتری و میهمان: یک API و یک منطق backend
- مدیر: roleهای `seller`, `manager`, `super_admin`
- تنظیمات سایت: جدول `site_settings` و پنل مدیر

## مشکل اصلی نسخه قبلی
نسخه‌های قبلی هم‌زمان کد قدیمی v37 با `localStorage` و API جدید Laravel را اجرا می‌کردند. بنابراین ممکن بود صفحه‌ای `customers` را از SQLite/localStorage بخواند و صفحه دیگر PostgreSQL API را. خطای `no such table: customers` نیز علامت همین ناهماهنگی/اتصال به دیتابیس اشتباه بود.

در نسخه نهایی:
1. Backend تولیدی فقط PostgreSQL را می‌پذیرد.
2. `start.sh` قبل از سرویس‌دهی migration را اجرا می‌کند.
3. وجود جدول `customers` بعد از migration بررسی می‌شود.
4. مدیر در همان جدول `customers` ساخته/همگام می‌شود.
5. login مشتری و login مدیر از API واحد استفاده می‌کنند.
6. checkout میهمان و مشتری از `OrderController` واحد استفاده می‌کنند.

## ورود مدیر تست
نام کاربری:
`admin`

رمز پیش‌فرض این بسته:
`44953322`

برای محیط واقعی حتماً مقدار `MWOODI_ADMIN_PASSWORD` را در Environment Variables سرویس `mwoodi-api` به یک رمز ۸ رقمی خصوصی تغییر دهید.

## مسیرهای اصلی
- `/` فروشگاه
- `/products` محصولات
- `/cart` سبد و خرید نهایی
- `/login` ورود/ثبت‌نام مشتری
- `/login?guest=1` مسیر میهمان
- `/login?admin=1` ورود مدیر
- `/account` حساب مشتری
- `/admin` پنل مدیریت
- `/api/v1/health` سلامت API

## امکاناتی که در نسخه نهایی یکپارچه شده‌اند
### مشتری
- ساخت حساب
- ورود با موبایل، کد ملی یا شماره مشتری
- رمز حداقل ۸ کاراکتر
- ویرایش نام، موبایل، کد ملی و رمز
- مشاهده سفارش‌ها
- سبد خرید سروری
- checkout و رزرو موجودی

### میهمان
- مشاهده محصولات
- افزودن به سبد محلی
- checkout بدون ساخت حساب
- ذخیره snapshot نام، موبایل و آدرس داخل سفارش
- پیگیری سفارش با شماره سفارش + موبایل

### مدیر
- ورود مستقل
- داشبورد فروش/سفارش/مشتری
- مشاهده مشتریان
- فعال/غیرفعال کردن محصول
- مشاهده سفارش‌ها
- تنظیمات محتوای صفحه اصلی
- آدرس و اطلاعات تماس
- شبکه‌های اجتماعی
- موارد اعتماد و خدمات
- تنظیمات پرداخت دستی/نام درگاه
- خروج امن و کنترل role سمت سرور

## Render
در GitHub، ریشه repository را همین ساختار نگه دارید و `render.yaml` را به Render بدهید.

Blueprint سه منبع می‌سازد:
- `mwoodi-api`
- `mwoodi-web`
- `mwoodi-db`

Frontend از Proxy داخلی Next.js به API وصل می‌شود؛ بنابراین وابستگی مستقیم browser به CORS API کمتر می‌شود.

### Environment مهم
در `mwoodi-api`:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` به‌صورت generated
- `DB_CONNECTION=pgsql`
- `DB_URL` از PostgreSQL Render
- `MWOODI_ADMIN_PASSWORD` رمز ۸ رقمی خصوصی

## تست قبل از تحویل
PHP syntax check روی تمام فایل‌های PHP این بسته اجرا شده و بدون خطا بوده است.

Build کامل npm در محیط اجرای فعلی به‌دلیل timeout هنگام دریافت dependencyها تکمیل نشد؛ بنابراین ادعای تست runtime کامل نمی‌شود. Render در deploy، `npm install && npm run build` را اجرا می‌کند.

## پرداخت
ثبت سفارش و رزرو موجودی در دیتابیس فعال است، اما اتصال به درگاه بانکی واقعی هنوز انجام نشده است. برای اتصال درگاه واقعی باید gateway، callback، verification و ثبت payment reference به پروژه اضافه شود.

## SQLite
SQLite برای production Render عمداً غیرفعال است. اگر Laravel روی `/var/www/database/database.sqlite` اجرا شود، این نسخه باید fail-fast کند تا خطای خاموش `no such table: customers` دوباره رخ ندهد.

## فایل مرجع v37
`app/frontend/src/legacy-v37-reference.html` نسخه مرجع v37 است. کد آن برای مقایسه و حفظ محتوا در بسته باقی مانده، اما مسیر اجرایی جدید از API واحد استفاده می‌کند.


### نکته مهم برای Render

اگر در سایت خطایی مثل `no such table: customers` با `Connection: sqlite` دیدید، آن نسخه‌ای که سرو می‌شود نسخه نهایی این پروژه نیست یا سرویس API به دیتابیس PostgreSQL متصل نشده است. نسخه فعلی قبل از سرویس‌دهی، در `start.sh` و `verify_runtime.php` درایور PostgreSQL و وجود جدول `customers` را کنترل می‌کند و در صورت خطا متوقف می‌شود. Blueprint نیز `DATABASE_URL` را از Render Postgres می‌گیرد.

بعد از اتصال Repository به Render، حتماً Deploy جدید انجام دهید و در سرویس API مسیر `/api/v1/health` را بررسی کنید. پاسخ سالم باید شامل `"ok":true`، `"driver":"pgsql"` و `"customers_table":true` باشد.

`MWOODI_ADMIN_PASSWORD` عمداً در Blueprint به صورت `sync: false` است؛ هنگام ساخت Blueprint مقدار رمز ۸ رقمی دلخواه خودتان را وارد کنید.
