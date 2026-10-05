# Deployment guide

How to put the MOAUM platform on a production server and keep it running.
Audience: whoever hosts and operates the site.

## Requirements

- PHP 8.3+ with `pdo_mysql`, `gd` (with WebP), `zip`, `fileinfo`, `intl`, `mbstring`
- MySQL 8 / MariaDB 10.6+ (SQLite is for local development only)
- Composer 2, Node 20+ (build step only)
- A web server (Nginx or Apache) pointing at `public/`
- HTTPS certificate for the domain
- Cron, and a process supervisor (Supervisor/systemd) for the queue worker
- `mysqldump` on the server, for backups

## First deployment

```bash
git clone <repo> /var/www/moaum && cd /var/www/moaum
composer install --no-dev --optimize-autoloader
cp .env.example .env            # then edit — see "Environment" below
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force     # roles/permissions, super admin, divisions, site pages
php artisan storage:link
npm ci --ignore-scripts && npm run build
php artisan optimize            # config, route, view and event caches
```

Sign in at `/login` with `MOAUM_ADMIN_EMAIL` / `MOAUM_ADMIN_PASSWORD` and
**change the password immediately** (Admin → My profile).

Writable directories for the web user: `storage/` and `bootstrap/cache/`.

## Environment

Start from `.env.example`; its last block is the production checklist:

| Key | Production value |
|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` |
| `APP_URL` | `https://your-domain` (used in emails, sitemap, canonical URLs) |
| `DB_CONNECTION` + `DB_*` | `mysql` and the database credentials |
| `SESSION_ENCRYPT` / `SESSION_SECURE_COOKIE` | `true` / `true` |
| `MAIL_MAILER` + `MAIL_*` | SMTP credentials; `MAIL_FROM_ADDRESS` on the company domain |
| `QUEUE_CONNECTION` | `database` (default) or `redis` |
| `TRUSTED_PROXIES` | proxy IPs if behind a load balancer |
| `MOAUM_BACKUP_DISK` | e.g. `s3` to copy backups off the server |

Production automatically forces `https://` URLs and sends HSTS. `robots.txt`
blocks all crawling on any non-production environment, so staging copies are
never indexed.

## Background processes

**Queue worker** — every email (receipts, staff alerts, quote emails, status
updates, password resets) is queued. Without a worker, nothing is sent.

```ini
# /etc/supervisor/conf.d/moaum-queue.conf
[program:moaum-queue]
command=php /var/www/moaum/artisan queue:work --tries=3 --max-time=3600
user=www-data
autostart=true
autorestart=true
numprocs=1
stdout_logfile=/var/www/moaum/storage/logs/queue.log
```

**Scheduler** — one cron entry runs everything in `routes/console.php`:

```cron
* * * * * cd /var/www/moaum && php artisan schedule:run >> /dev/null 2>&1
```

| Task | When |
|---|---|
| `news:publish-scheduled` — mark due scheduled articles Published | every 5 min |
| `moaum:backup` — database + uploads archive | 02:00 daily |
| `auth:clear-resets` — expired password reset tokens | daily |

## Updating

```bash
php artisan down --refresh=15
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force   # picks up new permissions
php artisan db:seed --class=PageSeeder --force             # creates any new system pages; never overwrites
npm ci --ignore-scripts && npm run build
php artisan optimize
php artisan queue:restart
php artisan up
```

## Backups

`php artisan moaum:backup` writes `storage/app/backups/moaum-YYYYMMDD-HHMMSS.zip`
containing:

- `database/database.sql` (mysqldump; or `database.sqlite` for SQLite)
- `files/media/…` — the media library
- `files/private/…` — enquiry/request attachments, documents, CVs

The newest `MOAUM_BACKUP_KEEP` archives (default 14) are kept. Set
`MOAUM_BACKUP_DISK` to a configured off-site disk (S3 or similar) so a copy
leaves the server — **a backup on the same disk does not protect against
losing the server.**

### Restoring

```bash
php artisan down
unzip moaum-YYYYMMDD-HHMMSS.zip -d /tmp/restore
mysql -u <user> -p <database> < /tmp/restore/database/database.sql
rsync -a /tmp/restore/files/media/   storage/app/public/media/
rsync -a /tmp/restore/files/private/ storage/app/private/
php artisan optimize:clear && php artisan up
```

Test a restore on a staging server at least once before go-live, and after
any major change.

## Monitoring

- `/up` is Laravel's health endpoint; point uptime monitoring at it.
- Errors are logged to `storage/logs/laravel.log` — rotate with logrotate or set
  `LOG_STACK=daily`. Consider an error tracker (e.g. Sentry) for production.
- Watch `failed_jobs` (`php artisan queue:failed`) for emails that could not be sent.
