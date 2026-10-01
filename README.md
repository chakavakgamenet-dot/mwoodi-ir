# MWoodi.ir — Render Test Build

این نسخه برای **تست واقعی روی Render** آماده شده است و شامل Next.js + Laravel API + PostgreSQL است.

## وضعیت فعلی
- Frontend: Next.js 15 + React 19
- Backend: Laravel 12 + Sanctum
- Database: PostgreSQL
- Dockerfile مخصوص Render
- `render.yaml` برای ساخت API، Frontend و PostgreSQL
- health endpoint: `/api/v1/health`
- migration کامل schema + داده نمونه
- ثبت‌نام و ورود واقعی
- خروج مشتری
- دریافت محصولات از API
- سبد مشتری واردشده از API
- سبد میهمان در مرورگر به‌صورت موقت
- ظاهر گرم و چوبی MWoodi حفظ شده است.

## نکته امنیتی
برای ساده شدن smoke test، نسخه فعلی Frontend توکن Sanctum را در `localStorage` نگه می‌دارد و از Bearer Token استفاده می‌کند. برای نسخه نهایی تجاری بهتر است احراز هویت به HttpOnly Secure Cookie منتقل شود.

## Deploy روی Render
فایل `render.yaml` را در ریشه repository قرار دهید و از Render گزینه **New -> Blueprint** را انتخاب کنید.

Render در Blueprint فعلی این منابع را می‌سازد:
- `mwoodi-api` — Laravel Docker Web Service
- `mwoodi-web` — Next.js Web Service
- `mwoodi-db` — PostgreSQL

اگر نام عمومی سرویس API تغییر کرد، مقدار `NEXT_PUBLIC_API_URL` سرویس frontend را به URL واقعی API با `/api/v1` تغییر دهید.

## تست
1. `https://<frontend>/products`
2. `https://<frontend>/login`
3. ثبت‌نام مشتری آزمایشی
4. ورود و خروج
5. باز کردن محصول و افزودن به سبد
6. باز کردن `/cart`
7. API health: `https://<api>/api/v1/health`

## محدودیت نسخه تست
درگاه بانکی واقعی، guest checkout سمت Backend و APIهای کامل پنل مدیریت هنوز برای مرحله تولید نهایی تکمیل نشده‌اند.

Render Free برای تست مناسب است، نه داده عملیاتی. طبق مستندات فعلی Render، Web Service رایگان پس از 15 دقیقه عدم فعالیت sleep می‌شود و PostgreSQL رایگان فعلی پس از 30 روز منقضی می‌شود.
