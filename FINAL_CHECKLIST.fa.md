# چک‌لیست نهایی MWoodi

- [x] یکسان‌سازی مسیر احراز هویت مشتری/مدیر روی Laravel API
- [x] حذف وابستگی اجرایی پنل به localStorage قدیمی v37
- [x] PostgreSQL اجباری در production
- [x] migration خودکار startup
- [x] verify وجود customers بعد از migration
- [x] sync مدیر در startup
- [x] ثبت‌نام مشتری
- [x] ورود مشتری با موبایل/کدملی/شماره مشتری
- [x] ویرایش حساب مشتری
- [x] سبد مشتری در API
- [x] سبد میهمان در مرورگر
- [x] checkout مشترک برای مشتری و میهمان
- [x] رزرو موجودی با transaction و lock
- [x] snapshot اطلاعات گیرنده در سفارش
- [x] پنل مدیر
- [x] مدیریت مشتریان
- [x] تنظیمات محتوای سایت
- [x] تنظیمات پرداخت دستی
- [x] health endpoint
- [x] Dockerfile API
- [x] render.yaml
- [x] نگهداری v37 به‌عنوان reference

## مواردی که عمداً ادعا نمی‌شوند
- درگاه بانکی واقعی هنوز متصل نیست.
- build npm در محیط فعلی به دلیل timeout dependency installation کامل اجرا نشد.
- SQLite برای production فعال نیست و نباید فعال شود.
