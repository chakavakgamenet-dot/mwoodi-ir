# MWoodi Laravel API
Laravel 12 + Sanctum + PostgreSQL + Redis.

Canonical DB schema is `../database-schema.sql`.
API routes are under `/api/v1`.

Important production rule: checkout recalculates prices and locks inventory rows in a DB transaction; client totals are never trusted.
