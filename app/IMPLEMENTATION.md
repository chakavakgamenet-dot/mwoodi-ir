# MWoodi Production v1 — Implementation

این نسخه شامل یک storefront واقعی Next.js، قرارداد Laravel API، مدل‌های اصلی، cart/order transaction و Docker stack است.

## اجرا

```bash
cd app
cp backend/.env.example backend/.env
# APP_KEY را با Laravel تولید کنید
# سپس schema.sql ریشه پروژه را روی PostgreSQL اجرا کنید
# Laravel Sanctum را migrate/install کنید

docker compose up --build
```

Frontend: http://localhost:3000
Backend: http://localhost:8000

## نکته production
- قبل از انتشار، Laravel را با `public/index.php` واقعی و `php artisan` اجرا کنید؛ Dockerfile فعلی scaffold است.
- payment gateway باید پشت یک service abstraction پیاده شود.
- guest cart، OTP، coupon، admin RBAC، uploads و webhook پرداخت در iteration بعدی به endpointهای تعریف‌شده اضافه می‌شوند.
- هیچ price/stock دریافتی از client معتبر نیست؛ checkout باید همیشه از DB محاسبه شود.
