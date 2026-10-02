# نقشه پروژه نهایی

## مسیر اجرایی Render
- `app/backend` → Laravel API
- `app/frontend` → Next.js storefront
- `render.yaml` → Blueprint

## مسیرهای اصلی API
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/admin-login`
- `PATCH /api/v1/auth/profile`
- `GET /api/v1/auth/me`
- `POST /api/v1/orders`
- `GET /api/v1/orders`
- `GET /api/v1/orders/guest-lookup`
- `GET /api/v1/settings`
- `GET /api/v1/admin/dashboard`
- `GET/PATCH /api/v1/admin/customers`
- `GET/PATCH /api/v1/admin/products`
- `GET/PUT /api/v1/admin/settings`

## دیتابیس
`app/backend/database/schema.sql` منبع schema PostgreSQL است و migrationها تغییرات مرحله‌ای را اعمال می‌کنند.

## مرجع v37
`app/frontend/src/legacy-v37-reference.html`

این فایل اجرا نمی‌شود و فقط برای حفظ دقیق محتوا/منطق نسخه مرجع نگهداری شده است.
