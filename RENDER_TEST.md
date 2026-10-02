# تست Render نسخه نهایی MWoodi

## 1. GitHub
کل محتوای این بسته را در root repository قرار دهید و push کنید.

## 2. Render
در Render:
`New -> Blueprint`

فایل `render.yaml` را انتخاب کنید.

## 3. منابع
- `mwoodi-api`: Laravel 12 + PHP 8.4
- `mwoodi-web`: Next.js 15
- `mwoodi-db`: PostgreSQL

## 4. مدیر
بعد از deploy:
- Username: `admin`
- Password: مقدار `MWOODI_ADMIN_PASSWORD`

مقدار فعلی تستی در Blueprint `44953322` است. برای استفاده واقعی آن را در Environment Variables به رمز خصوصی خودتان تغییر دهید.

## 5. Smoke test
1. `/api/v1/health` باید `ok=true` و `database=ok` برگرداند.
2. `/login?admin=1` باز شود.
3. ورود مدیر انجام شود و `/admin` نمایش داده شود.
4. در پنل، تب «مشتریان» و «تنظیمات سایت» قابل باز شدن باشد.
5. `/login?register=1` ثبت‌نام مشتری را انجام دهد.
6. مشتری وارد `/account` شود.
7. یک محصول به سبد اضافه و checkout شود.
8. خروج مشتری و ورود مجدد کار کند.
9. در حالت میهمان، محصول به سبد اضافه و سفارش ثبت شود.
10. سفارش میهمان به `customer_id` متصل نشود ولی snapshot نام/موبایل/آدرس را داشته باشد.

## محدودیت
Render Free برای تست مناسب است. این بسته برای محیط production واقعی، درگاه بانکی واقعی را فعال نکرده و داده عملیاتی حساس نباید روی سرویس رایگان نگهداری شود.


### تشخیص خطای SQLite

اگر پاسخ Login هنوز متن `Connection: sqlite, Database: /var/www/database/database.sqlite` را نشان می‌دهد، درخواست به یک Deployment قدیمی/سرویس API دیگر رسیده است. در نسخه نهایی این repository، `start.sh` اجازه اجرای production با SQLite را نمی‌دهد. ابتدا Deploy API را از همین commit انجام دهید، سپس `/api/v1/health` را بررسی کنید.
