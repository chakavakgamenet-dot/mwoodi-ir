#!/bin/sh
set -e
# Render production must use PostgreSQL. The generated Laravel .env defaults to SQLite,
# so explicitly default DB_CONNECTION to pgsql when the service env is missing.
# On Render, always use the managed PostgreSQL database even if an old service
# environment variable still says sqlite. This prevents Laravel from silently
# creating/using /var/www/database/database.sqlite with no customers table.
if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export DB_CONNECTION="pgsql"
else
  export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
fi
if [ "${DB_CONNECTION}" != "pgsql" ]; then
  echo '[MWoodi] FATAL: Production requires PostgreSQL; refusing SQLite.' >&2
  exit 1
fi
if [ -z "${DB_URL:-}" ] && [ -z "${DB_HOST:-}" ]; then
  echo '[MWoodi] FATAL: PostgreSQL connection is not configured.' >&2
  exit 1
fi
# Render مقدار APP_KEY را بدون پیشوند base64: می‌دهد؛ اینجا به فرمت معتبر Laravel تبدیل می‌شود.
export APP_KEY="$(php -r '$k=getenv("APP_KEY"); if($k!==false && strpos($k,"base64:")===0){echo $k;} else {echo "base64:".base64_encode(hash("sha256",$k?:"mwoodi-fallback-key",true));}')"
php artisan config:clear
php artisan route:clear
echo '[MWoodi] DB_CONNECTION='"$DB_CONNECTION"
echo '[MWoodi] DB_URL configured='"$([ -n "${DB_URL:-}" ] && echo yes || echo no)"
php artisan migrate --force
php artisan migrate:status
php artisan route:list --path=api/v1
php /var/www/scripts/verify_routes.php
php /var/www/scripts/sync_admin.php
echo '[MWoodi] migrations and admin synchronization complete'
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
