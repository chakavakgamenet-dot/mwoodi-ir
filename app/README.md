# MWoodi Production v1

Full implementation scaffold based on v37.

- `frontend/`: Next.js + TypeScript storefront
- `backend/`: Laravel 12 API + Sanctum
- `database-schema.sql`: PostgreSQL canonical schema
- `docker-compose.yml`: PostgreSQL, Redis, Laravel, Next.js
- `legacy-v37-reference.html`: preserved v37 UI reference inside frontend

## Start

```bash
cd app
cp backend/.env.example backend/.env
# For local Docker, keep the default DB host postgres.
docker compose up --build
```

Then open `http://localhost:3000`.

The payment gateway is intentionally abstracted and should be connected to the selected Iranian gateway before production. Admin RBAC, OTP, coupons, guest checkout, media storage and shipping provider are the next backend modules; the database/API contract already reserves the relevant entities and endpoints.
