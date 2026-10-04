# MOAUM Consultancy Services

Corporate website + admin panel for MOAUM Consultancy Services Limited — the business and investment arm of Rev. Fr. Moses Orshio Adasu University, Makurdi.

Built with **Laravel 13** (PHP ^8.3), **Blade + Alpine.js + Tailwind CSS v4** (Vite), **SQLite** by default, and **spatie/laravel-permission** for RBAC. Full requirements live in `prototype-docs/MOAUM_PRD_SRD.md`; the visual spec and pixel prototypes are in `prototype-docs/`.

## Prerequisites

- PHP 8.3+ with the `sqlite` extension (no database server required — SQLite is used by default)
- Composer
- Node.js 20.19+ (Vite 8) and npm

## Setup

```bash
composer setup
php artisan db:seed
```

`composer setup` installs PHP dependencies, copies `.env` from `.env.example`, generates the app key, runs migrations, and builds the frontend assets.

**Note:** `composer setup` does *not* seed the database. Run `php artisan db:seed` afterwards — it creates roles/permissions, the local admin account, and sample content (16 business divisions, service categories, services, site settings). The seeder is idempotent and safe to re-run.

## Running the app

```bash
composer dev
```

Starts the app on <http://localhost:8000> with the Vite dev server and log tailing in one process.

Individual commands, if you prefer:

```bash
php artisan serve        # web server
npm run dev              # Vite (hot reload)
./vendor/bin/pail        # log tailing
```

## Local logins

| Role | Email | Password |
| --- | --- | --- |
| Super Administrator | `admin@moaum.test` | `ChangeMe!2026` |
| Customer (local only) | `test@example.com` | `ChangeMe!2026` |

Overridable via `MOAUM_ADMIN_EMAIL` / `MOAUM_ADMIN_PASSWORD` in `.env`.

## Testing

```bash
composer test                       # full suite
php artisan test --filter=BusinessDirectoryTest
php artisan test --filter=AuthenticationTest::test_users_can_login_with_valid_credentials
```

Tests run PHPUnit against in-memory SQLite (`RefreshDatabase`) — no external services needed.

## Code style

```bash
vendor/bin/pint
```

## Environment notes

- SQLite database file: `database/database.sqlite` (created automatically by migrations). Sessions, cache, and queue also use the database; tests override these to in-memory/array drivers in `phpunit.xml`.
- Mail uses the `log` driver in dev — emails are written to `storage/logs/laravel.log`, not sent.
- If you enable real mail or switch to MySQL, update `.env` accordingly; migrations are written to stay DB-agnostic.
- `.npmrc` sets `ignore-scripts=true` so npm postinstall scripts never run; keep this behaviour in any `npm install` you run manually.

## Repository map

- `app/Http/Controllers/` — `Public/` (site pages), `Requests/` (service/quote requests, tracking), `Auth/`, `Admin/`
- `app/Models/` + `app/Enums/` — domain models with status/privacy enums
- `app/Support/Rbac.php` — single source of truth for roles and permissions (PRD §23); authorize with permissions, never role names
- `app/Services/AuditLogger.php` + `app/Observers/AuditableTriageObserver.php` — audit trail for admin-affecting operations
- `config/moaum.php` — company details + navigation structure (source for header/footer until the Settings admin UI lands)
- `database/seeders/` — `RolePermissionSeeder`, `AdminUserSeeder`, `ContentSeeder` (sample divisions/services/settings)
- `resources/views/` — `public/`, `auth/`, `admin/`, shared `components/`; design tokens and shared component classes (`.btn-primary`, `.card`, `.input`, …) live in `resources/css/app.css`
- `prototype-docs/` — PRD/SRD, design system, and HTML prototypes; `docs/` mirrors the markdown docs
