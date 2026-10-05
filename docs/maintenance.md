# Maintenance and content guide

Day-to-day running of the site. Audience: MOAUM administrators and whoever
supports the platform.

## Before go-live: content still needed from MOAUM

Search the admin for these and replace them with approved copy:

- **Pages → "Needs client copy"** badges: company history, strategic direction,
  mission statement, the legal wording on University ownership (PRD §9), the
  privacy notice and terms of use (PRD §33). Blocks marked `CLIENT_TO_PROVIDE`
  are hidden from visitors until replaced.
- **Settings → Contact details**: phone, address and map (currently empty).
- **Settings → Social media**: real profile links (none are shown until set).
- **Settings → Home page**: confirm the trust-banner statistics and hero badge
  (only publish confirmed figures).
- **Pages → Leadership team**: names, positions, photos (nothing is seeded).
- **Business Divisions**: confirm each division's status (Active / Planned /
  Coming Soon), descriptions, contacts and photography.
- **Services**: real services and pricing types for each division.

## Routine tasks

| Task | Where |
|---|---|
| Answer enquiries, service and quote requests | Admin → Customer engagement |
| Send a quote | Quote request → enter amount/message → status **Quote Sent** (emails the customer) |
| Review job applications | Admin → Applications (CV downloads are logged) |
| Publish news / schedule an article | News → status Published or Scheduled with a date |
| Add photos | Media Library (or drop images straight into an editor or picker) |
| Add a staff member | Users & Roles → Add user (they receive a set-password email) |
| Remove access | Edit the user → untick "Account active" (signs them out everywhere) |
| See who changed what | Audit Logs (also shown on each request's page) |

## Image guidance

Upload JPG, PNG or WebP up to 8 MB. Images are resized to at most 2000px wide
and converted to WebP automatically. Always fill in **alt text** — it is read
aloud to blind visitors and helps search engines.

## Health checks

- **Emails not arriving?** The queue worker must be running (see
  deployment guide). `php artisan queue:failed` lists failures;
  `php artisan queue:retry all` resends them.
- **Scheduled article not live?** It goes live at its date even if the
  scheduler is down; the scheduler only updates the admin status label.
- **Backups**: check `storage/app/backups/` (and the off-site disk) for a recent
  archive. Run one manually with `php artisan moaum:backup`.
- **Changed permissions in code?** Run
  `php artisan db:seed --class=RolePermissionSeeder --force`.

## Data protection

Enquiries, requests and applications contain personal data (PRD §33). Agree
retention periods with MOAUM's compliance adviser and delete records past
them. Restrict `view-applications` to HR staff only.
