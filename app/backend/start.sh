#!/bin/sh
set -eu

# IMPORTANT: Render's generated Laravel environment can contain DB_CONNECTION=sqlite.
# The managed Render PostgreSQL URL is authoritative. Never allow production to boot on SQLite.
if [ -n "${DB_URL:-}" ] || [ -n "${DATABASE_URL:-}" ]; then
  export DB_CONNECTION=pgsql
elif [ -n "${DB_HOST:-}" ] || [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export DB_CONNECTION=pgsql
else
  echo '[MWoodi] FATAL: Render PostgreSQL connection is not configured (DB_URL/DATABASE_URL/DB_HOST missing).' >&2
  exit 1
fi

if [ "${DB_CONNECTION}" != "pgsql" ]; then
  echo '[MWoodi] FATAL: PostgreSQL is required; SQLite is disabled in production.' >&2
  exit 1
fi

# Render may generate APP_KEY without Laravel's base64: prefix.
export APP_KEY="$(php -r '$k=getenv("APP_KEY"); if($k!==false && strpos($k,"base64:")===0){echo $k;} else {echo "base64:".base64_encode(hash("sha256",$k?:"mwoodi-fallback-key",true));}')"

php artisan config:clear
php artisan route:clear

# Give the managed database a short startup window after deploy/restart.
i=1
while ! php artisan migrate --force; do
  if [ "$i" -ge 12 ]; then
    echo '[MWoodi] FATAL: PostgreSQL migrations failed after 12 attempts.' >&2
    exit 1
  fi
  echo "[MWoodi] PostgreSQL not ready yet; retry ${i}/12..." >&2
  i=$((i+1))
  sleep 3
done

php artisan migrate:status
php /var/www/scripts/verify_runtime.php
php /var/www/scripts/sync_admin.php
php artisan route:list --path=api/v1

echo '[MWoodi] production boot checks completed successfully.'
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
