# MOAUM Consultancy Services

Laravel 13 (PHP ^8.3) corporate website + admin panel for MOAUM Consultancy Services. Public site built module-by-module against the PRD in `prototype-docs/`.

## Commands

- Setup (fresh clone): `composer setup` — installs deps, copies `.env`, generates key, migrates, runs `npm install --ignore-scripts` and `npm run build`. Then `php artisan db:seed` (setup does NOT seed; idempotent, safe to re-run — creates roles/permissions, admin user, sample content).
- Dev server: `composer dev` (runs `php artisan dev`: serve + log tail + Vite together).
- Tests: `composer test` (clears config cache, then `php artisan test`). Single test: `php artisan test --filter=BusinessDirectoryTest` or `--filter=Test::method`.
- Code style: `vendor/bin/pint` (Laravel Pint; no separate typecheck).
- PHPUnit, not Pest. Feature tests use `RefreshDatabase` on in-memory SQLite (see `phpunit.xml`); no external services needed.

## Spec & design (read before building features)

- `prototype-docs/MOAUM_PRD_SRD.md` — requirements, section numbers (§x) referenced throughout code comments; implement per module as stated in the code (`Module 3`, `Module 5`, etc. comments in `routes/web.php`).
- `prototype-docs/MOAUM_Design_System.md` + `prototype-docs/*.html` — visual spec and pixel prototypes (index, business-detail, admin). Follow the prototypes closely.
- Design tokens and shared component classes (`.btn-primary`, `.card`, `.input`, `.label`, `.form-error`, `.badge`, `.eyebrow`) live in `resources/css/app.css` (`@theme` + `@layer components`) — use these classes in Blade instead of ad-hoc Tailwind.

## Architecture notes

- Frontend: Blade + Alpine.js + Tailwind v4 (Vite). Reusable Blade components in `resources/views/components/` (layouts: `public.blade.php`, `admin.blade.php`; `button`, `badge`, `icon`). Icons come from the `lucide` npm package via `App\Support\Icons`.
- RBAC: `app/Support/Rbac.php` is the single source of truth for roles/permissions (spatie/laravel-permission). Seeders: `RolePermissionSeeder`, `AdminUserSeeder`.
  - Authorize with **permissions** (`permission:` middleware, policies, `can:`) — never check role names in controllers (PRD §23).
  - "Super Administrator" bypasses all gates via `Gate::before` in `app/Providers/AuthServiceProvider.php`; don't duplicate that logic.
  - spatie v8 resolves permission/role **names through a cached permission set**, not the DB, and that cache is only flushed by the models' `saved`/`deleted` **Eloquent events** (`RefreshesPermissionCache`). Consequences: never wrap seeders in `withoutEvents` (breaks `syncPermissions`/`hasRole`/`assignRole` with `PermissionDoesNotExist`), and after inserting permissions/roles out-of-band call `app(PermissionRegistrar::class)->forgetCachedPermissions()` (or `php artisan permission:cache-reset`). `User::query()->permission('x')` also throws if that permission row doesn't exist — tests that use it must seed RBAC first.
- Audit trail: `App\Services\AuditLogger` + `AuditableTriageObserver` (observing Enquiry/ServiceRequest/QuoteRequest) and auth event listeners registered in `AppServiceProvider`. Add audit logging for new admin-affecting operations; the admin viewer is at `/admin/audit-logs`.
- `config/moaum.php` holds company details and the site nav structure (incl. mega-menu groups) until the Settings table takes over in Module 10. Placeholder `Route::view` routes are wired to that config so nav links don't 404 — replace them with real modules as they land.
- Route groups: public site, auth, and `admin.` (auth + per-screen `permission:` middleware). Views mirror this: `resources/views/public/`, `auth/`, `admin/`.
- `config('moaum')` nav entries with `CLIENT_TO_PROVIDE` values in `config/moaum.php` are intentional placeholders awaiting client input — leave them.

## Local logins (seeded)

- Super admin: `admin@moaum.test` / `ChangeMe!2026` (overridable via `MOAUM_ADMIN_EMAIL` / `MOAUM_ADMIN_PASSWORD`).
- Customer: `test@example.com` / `ChangeMe!2026` (local env only).

## Environment quirks

- SQLite is the default DB (`database/database.sqlite`); sessions/cache/queue use the database — migrations for those exist in the initial three `0001_*` files.
- `.npmrc` sets `ignore-scripts=true`, so npm postinstall scripts never run (this is intentional for `laravel/pail`/native deps here); match it in any `npm install` invocations.
- `APP_URL` is `http://localhost:8000`; `MAIL_MAILER=log` (no real mail in dev).
