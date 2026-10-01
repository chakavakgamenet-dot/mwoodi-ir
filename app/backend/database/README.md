# MWoodi production database

This is the **canonical production schema** used by Laravel on Render.

- Schema source: `schema.sql`
- Migrations: `migrations/`
- Render database resource: `mwoodi-db` (managed PostgreSQL)
- The database itself is **not stored inside GitHub**. GitHub stores this schema and migration code; Render creates/runs the actual PostgreSQL database from `render.yaml`.
- Admin username: `admin`
- Admin password: `44953322`

On every API container boot, Laravel runs migrations and then `scripts/sync_admin.php`, which synchronizes and verifies the admin credential against the live PostgreSQL database.
