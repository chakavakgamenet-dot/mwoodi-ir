#!/usr/bin/env sh
set -eu
: "${SITE_URL:?SITE_URL is required, e.g. https://mwoodi-web.onrender.com}"
: "${API_URL:?API_URL is required, e.g. https://mwoodi-api.onrender.com}"

echo "== health =="
curl -fsS "$API_URL/api/v1/health"
echo
echo "== products =="
curl -fsS "$API_URL/api/v1/products?all=1" >/dev/null
echo "products: ok"
echo "== storefront settings =="
curl -fsS "$API_URL/api/v1/settings" >/dev/null
echo "settings: ok"
echo "== site =="
curl -fsSI "$SITE_URL/login" | head -1
echo "site: ok"
