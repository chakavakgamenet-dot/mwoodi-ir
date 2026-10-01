# MWoodi — نسخه اصلاح‌شده ورود، ثبت‌نام، محصولات و پنل مدیر

## چه چیزهایی اصلاح شده؟
- ثبت‌نام مشتری با API واقعی Laravel/Sanctum
- ورود مشتری و نگهداری Bearer Token
- ورود مدیر واقعی سمت سرور
- حساب مدیر تستی: `admin` / `1234`
- محافظت APIهای پنل مدیر بر اساس role
- داشبورد مدیر با سفارش‌ها، محصولات و آمار
- نمایش محصولات از PostgreSQL و seed خودکار محصولات
- افزودن مستقیم محصول به سبد از صفحه محصولات
- رفع لینک اشتباه صفحه جزئیات محصولات
- سبد خرید مشتری از API
- سبد مهمان با localStorage
- ثبت سفارش مشتری از فرم آدرس (بدون درگاه پرداخت آنلاین)
- Proxy داخلی Next.js برای `/api/*` تا مشکل URL/CORS فرانت‌اند کمتر شود
- migration دوم برای اینکه اگر دیتابیس قبلاً migrate شده باشد، مدیر و محصولات demo هم ساخته شوند.

## روش صحیح Deploy روی Render
**مهم:** فقط ZIP را به عنوان Web Service فرانت‌اند Deploy نکنید. این پروژه دو Web Service و یک PostgreSQL دارد.

1. کل محتوای این پروژه را در GitHub قرار دهید.
2. در Render گزینه **New → Blueprint** را بزنید.
3. Repository را انتخاب کنید و `render.yaml` را اجرا کنید.
4. باید این سه سرویس ساخته شوند:
   - `mwoodi-api`
   - `mwoodi-web`
   - `mwoodi-db`
5. صبر کنید API و Web هر دو Deploy شوند.
6. در API این آدرس باید `ok: true` بدهد:
   `/api/v1/health`
7. سپس Web را باز کنید.

### تست مدیر
در سایت:
`/login?admin=1`

نام کاربری:
`admin`

رمز:
`1234`

این رمز فقط برای تست است و قبل از استفاده واقعی باید تغییر کند.

## اگر Render قبلاً سرویس‌ها را ساخته است
Blueprint را **Manual Sync** کنید تا migration جدید `0002_mwoodi_seed_demo` هم اجرا شود. دیتابیس قبلی را بی‌دلیل حذف نکنید.

## نکته مهم درباره پرداخت
ثبت سفارش در این نسخه فعال است، اما درگاه بانکی ایرانی هنوز به پروژه متصل نشده است. بنابراین «ثبت سفارش» کار می‌کند ولی پرداخت آنلاین واقعی نیاز به اتصال درگاه دارد.


## v10 — auth/deployment fix
- Browser API requests now always use same-origin `/api/v1`.
- Next.js server-side rewrite forwards `/api/*` to the Render backend using `BACKEND_ORIGIN`, avoiding browser-side API-origin/CORS/build-time URL failures.
- Product cart/admin mutation routes explicitly bind by UUID `id`.
- Admin dashboard no longer fails as a whole when one panel API request fails.
- Render must have both `mwoodi-web` and `mwoodi-api` services deployed; `mwoodi-web` needs `BACKEND_ORIGIN` pointing to the API service's public `RENDER_EXTERNAL_URL`.


## v11 — Render database/auth fix
- Root cause found from the live error: Laravel was using SQLite (`/var/www/data/database.sqlite`) in production, while the project schema/migrations are PostgreSQL.
- `start.sh` now defaults `DB_CONNECTION` to `pgsql` if Render omitted it.
- Added an explicit Laravel `config/database.php` supporting `DB_URL` and PostgreSQL.
- Docker now copies that database config into the production image.
- Demo admin password is now exactly 8 digits: `admin / 12345678`.
- Added migration `0003_update_demo_admin_password.php` so an already-migrated PostgreSQL database receives the new password.
- Render service `mwoodi-api` must have `DB_URL` connected to the `mwoodi-db` PostgreSQL database.
