# MWoodi Render - اصلاح مسیرهای Blueprint

## علت خطای قبلی
Render دنبال `mwoodi-build/app/frontend` و `mwoodi-build/app/backend` می‌گشت، در حالی که ساختار واقعی Repository این است:

- `app/frontend`
- `app/backend`

## render.yaml اصلاح شد
- API: `dockerfilePath: ./app/backend/Dockerfile`
- API: `dockerContext: ./app/backend`
- Web: `rootDir: app/frontend`
- Web API origin از `RENDER_EXTERNAL_URL` سرویس API گرفته می‌شود.
- `DB_URL` از PostgreSQL همان Blueprint گرفته می‌شود.

## بعد از Push
1. در GitHub فایل‌های این نسخه را جایگزین کنید.
2. در Render وارد Blueprint شوید.
3. روی **Manual sync** بزنید.
4. تغییرات `rootDir` و `dockerfilePath` را تأیید کنید.
5. `mwoodi-db` موجود را حذف نکنید.
6. API و Web را Sync/Deploy کنید.
7. Health را تست کنید:
   `https://<API-URL>/api/v1/health`
8. انتظار:
   `{"ok":true,"database":"ok",...}`

## نکته
این نسخه برای تست Render آماده است. در صورت استفاده Production، CORS باید از `*` به دامنه واقعی MWoodi.ir محدود شود و درگاه پرداخت/OTP/پنل مدیر نیز قبل از انتشار عمومی تکمیل شود.


## اصلاح خطای جدید Docker
اگر Render خطای `.env.example: not found` نشان داد، نسخه v4 این وابستگی را حذف کرده است؛ Laravel در مرحله ساخت خود فایل `.env.example` را دارد.
