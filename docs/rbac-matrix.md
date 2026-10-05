# Roles and permissions (RBAC matrix)

Source of truth: `app/Support/Rbac.php` (PRD §23). This page is the
human-readable summary; the seeded defaults can be changed per role under
**Admin → Users & Roles → Roles & permissions**, and custom roles added there.

Authorisation always checks **permissions**, never role names. Super
Administrator bypasses every check (`Gate::before`).

## Default role grants

| Area | Permissions | Admin | Content Editor | Business Manager |
|---|---|:-:|:-:|:-:|
| Dashboard | view-admin-dashboard | ✓ | ✓ | ✓ |
| Business divisions | view / create / edit / publish / delete | all | all but delete | view, edit, publish |
| Services & categories | view / create / edit / publish / delete | all | all but delete | all but delete |
| Projects | view / create / edit / publish / delete | all | all but delete | — |
| News & categories | view / create / edit / publish / delete | all | all but delete | — |
| Training | view / create / edit / publish / delete | all | all but delete | — |
| Pages & leadership | view / create / edit / publish / delete | all | all but delete | — |
| Careers (job adverts) | view / create / edit / publish / delete | all | all but delete | — |
| Job applications | view-applications, update-applications | all | — | — |
| Downloads | view / create / edit / publish / delete | all | all but delete | — |
| FAQs | view / create / edit / publish / delete | all | all but delete | — |
| Gallery | view / create / edit / publish / delete | all | all but delete | — |
| Media library | view-media, upload-media, delete-media | all | view, upload | view, upload |
| Enquiries | view, assign, update, close | all | — | all |
| Service requests | view, update | all | — | all |
| Quote requests | view-quotes, update-quotes, create-quotes (price & send), close-quotes (accept/decline) | all | — | all |
| Users | manage-users | ✓ | — | — |
| Roles | manage-roles | ✓ | — | — |
| Site settings | manage-settings | ✓ | — | — |
| Audit log | view-audit-logs | ✓ | — | — |

Customer and Training Participant roles have no admin permissions; they are
reserved for the future customer portal (PRD §4.2).

## What "publish" means

`publish-*` controls whether and how a record appears publicly: status,
the featured flag and the publish date. Without it, staff can still write
and edit; new records start hidden (draft / planned / unpublished), and news
writers can move articles between **Draft** and **Review** only.

## Guard rails beyond permissions

- Only a Super Administrator can grant or remove the Super Administrator role,
  and the last active Super Administrator cannot be demoted or deactivated.
- Nobody can deactivate their own account. Deactivation signs the user out
  everywhere, including "remember me" cookies.
- System pages (About, Mission, Leadership, University Relationship, Privacy,
  Terms) cannot be deleted or have their address changed.
- Divisions with services, projects or customer requests, and jobs with
  applications, are archived/closed rather than deleted.
- Downloading a customer attachment or applicant CV is written to the audit log.

## After adding permissions in code

New permissions in `Rbac.php` reach the database when the seeder runs:

```bash
php artisan db:seed --class=RolePermissionSeeder --force
```
