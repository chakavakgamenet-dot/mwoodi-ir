#!/usr/bin/env bash
set -euo pipefail
API="${1:-https://mwoodi-api.onrender.com/api/v1}"
echo "1) health"
curl -fsS "$API/health"
echo
echo "2) products"
curl -fsS "$API/products?per_page=1" | python3 -m json.tool >/dev/null
echo "OK"
