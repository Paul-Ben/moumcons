# Architecture overview

Laravel 13 + Blade + Alpine.js + Tailwind v4, built against
`prototype-docs/MOAUM_PRD_SRD.md`. Audience: developers joining the project.

## Module map

| Area | Public routes | Admin | Main code |
|---|---|---|---|
| Home | `/` | Settings → Home page | `HomeController`, `home.blade.php` |
| About & legal pages | `/about…`, `/privacy-policy`, `/terms`, `/pages/{slug}` | Pages, Leadership | `Page` (`SYSTEM` keys), `LeadershipMember` |
| Business divisions | `/businesses`, `/businesses/{slug}` | Business Divisions | `BusinessDivision`, `DivisionCapability` |
| Services | `/services`, `/services/{slug}` | Services, categories | `Service`, `ServiceCategory` |
| Projects | `/projects`, `/projects/{slug}` | Projects | `Project`, `ProjectImage` |
| News | `/news`, `/news/{slug}`, `/news/feed` | News, categories | `NewsArticle`, `NewsCategory` |
| Training | `/training`, `/training/{slug}` (+ interest form) | Training | `TrainingProgramme` |
| Careers | `/careers`, `/careers/{slug}` (+ apply) | Careers, Applications | `JobOpening`, `JobApplication` |
| Downloads | `/downloads`, `/downloads/{slug}` | Downloads | `Document` ("documents" disk) |
| FAQs | `/faqs` (+ division pages) | FAQs | `Faq` |
| Gallery | `/gallery`, `/gallery/{slug}` | Gallery | `Gallery`, `GalleryImage` |
| Contact / enquiries | `/contact` | Enquiries | `Enquiry` |
| Service & quote requests | `/request-service`, `/request-quote`, `/track-request` | Service / Quote Requests | `ServiceRequest`, `QuoteRequest` |
| Search & SEO | `/search`, `/sitemap.xml`, `/robots.txt` | top-bar search | `SiteSearch`, `SeoController` |
| Administration | — | Dashboard, Users & Roles, Settings, Media, Audit Logs | `AdminNav`, `Rbac`, `SiteSettings` |

## Cross-cutting building blocks

- **Authorisation** — `App\Support\Rbac` defines permissions; route groups use
  `permission:view-*`; per-action checks live in policies. Content areas extend
  `App\Policies\ContentPolicy` (view/create/edit/publish/delete-{area}).
- **Publication** — `Publishable` (published_at: draft, live or scheduled) and
  `HandlesPublication` (the "Visible on site" checkbox + date). News has its own
  status workflow; pages, divisions and services use status enums.
- **Slugs** — `HasUniqueSlug` fills unique slugs from the title/name.
- **Rich text** — Trix in the admin (`x-admin.rich-text`), sanitised on write by
  `RichTextCast` and again on render by `<x-rich-content>` (`App\Support\RichText`).
  Legacy plain-text copy is rendered as paragraphs.
- **Media** — `MediaUploader` resizes to ≤2000px, re-encodes to WebP and makes a
  thumbnail. Content stores the image's site-relative URL. Pickers:
  `x-admin.media-picker` (one image) and `x-admin.gallery-picker` (ordered list).
- **Private files** — the `private` disk (form attachments) and the `documents`
  disk (library files, CVs) are never web-served; controllers check access,
  stream the file, and audit sensitive downloads.
- **Audit trail** — `AuditableTriageObserver` (enquiries, requests, applications)
  and `AuditableContentObserver` (all CMS models) write field-level diffs via
  `AuditLogger`; users, roles and settings log explicitly.
- **Notifications** — all queued (`ShouldQueue`) and sent via `RequestNotifier`.
- **Settings** — `App\Support\SiteSettings` defines the editable keys;
  `Setting::get()` reads them with a fallback to `config/moaum.php`;
  `CompanyDetails` hides `CLIENT_TO_PROVIDE` placeholders from visitors.
- **Caching** — settings and the header/footer division list are cached and
  invalidated by model events; the sitemap is cached for 30 minutes.

## Shared admin components (`resources/views/components/admin`)

`page-header`, `input`, `textarea`, `select`, `checkbox`, `rich-text`,
`media-picker`, `gallery-picker`, `media-library-modal`, `publish-fields`,
`seo-fields`, `category-manager`, `triage-filters`, `activity-trail`,
`attachment-card`, `detail`, `delete-button`, `form-actions`.

## Out of MVP scope (PRD §4.2)

Customer accounts and portal, online payments, training registration/payment,
e-commerce and the per-division operational systems. Extension points:
training interest and job applications already capture leads; the
Customer and Training Participant roles exist with no permissions.
