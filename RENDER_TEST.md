# تست استقرار MWoodi روی Render

این بسته برای یک محیط تست رایگان Render آماده شده است.

## ساختار
- `app/backend`: Laravel API + Docker
- `app/frontend`: Next.js
- `mwoodi-build/database/schema.sql`: مرجع دیتابیس
- `render.yaml`: Blueprint برای API، Frontend و PostgreSQL

## راه‌اندازی
1. کل مخزن را در GitHub قرار دهید.
2. در Render از New -> Blueprint فایل `render.yaml` را انتخاب کنید.
3. سه سرویس ساخته می‌شوند: `mwoodi-api`، `mwoodi-web` و `mwoodi-db`.
4. اگر نام سرویس `mwoodi-api` به‌دلیل اشغال بودن تغییر کرد، متغیرهای API در `render.yaml` از URL سرویس API گرفته می‌شوند و فرانت‌اند از Proxy داخلی Next.js نیز استفاده می‌کند.
5. سلامت API: `/api/v1/health`
6. صفحه فروشگاه: URL سرویس `mwoodi-web`

## تست سریع
- ثبت‌نام: `/login`
- ورود: `/login`
- حساب: `/account`
- محصولات: `/products`
- جزئیات محصول: `/products/<slug>`
- سبد مشتری واردشده: `/cart`

### توجه
Render Free برای تست مناسب است. Web Service رایگان پس از 15 دقیقه بی‌استفاده شدن متوقف می‌شود و با درخواست بعدی دوباره بالا می‌آید. PostgreSQL رایگان فعلی Render برای شروع رایگان است اما طبق مستندات Render پس از 30 روز منقضی می‌شود؛ برای داده واقعی/تولیدی مناسب نیست.
