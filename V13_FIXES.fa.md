# MWoodi v13 — اصلاح عمیق Render/GitHub

این نسخه بر اساس فایل مرجع `v37-reference.html` بازبینی شده است.

اصلاحات:
- صفحه اصلی Next.js از API واقعی محصولات استفاده می‌کند و fallback فقط برای حالت قطع API است.
- ظاهر صفحه اصلی از ساختار مرجع v37 (هدر، Hero، دسته‌بندی، گالری، Collection و About) بازسازی شده، بدون وابستگی به localStorage برای داده فروشگاه.
- migration شماره 0004 به‌صورت idempotent مدیر تست `admin / 12345678`، دسته‌ها، شش محصول مرجع، موجودی و تنظیمات اولیه را روی PostgreSQL تضمین می‌کند.
- ورود مدیر با username `admin` به رکورد phone=`admin` و نقش `super_admin` متصل است.
- endpoint عمومی `/api/v1/settings` برای تنظیمات فروشگاه اضافه شد.
- مسیر API از `NEXT_PUBLIC_API_ORIGIN` دریافت می‌شود.
- ثبت سفارش مهمان و مشتری حفظ شده است.
- داده‌های مرجع v37 منبع UI/seed هستند؛ خود فایل v37 دیتابیس PostgreSQL ندارد و داده‌های آن در localStorage نگهداری می‌شوند.
