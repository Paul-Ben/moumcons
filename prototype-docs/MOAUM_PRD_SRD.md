# MOAUM Consultancy Services Limited

## Product Requirements Document (PRD) & Software Requirements Document (SRD)

**Document Version:** 1.1\
**Status:** Initial Development Baseline\
**Prepared For:** MOAUM Consultancy Services Limited\
**Institutional Context:** Rev. Fr. Moses Orshio Adasu University,
Makurdi\
**Technology Stack:** Laravel 13, MySQL, HTML5, Tailwind CSS\
**Document Purpose:** Product definition, functional specification,
technical baseline, and implementation guide

------------------------------------------------------------------------

# 1. Executive Summary

MOAUM Consultancy Services Limited is the official business and
investment arm of Rev. Fr. Moses Orshio Adasu University, Makurdi. The
University owns 51% of the company shares. The company operates as a
diversified commercial enterprise covering professional services,
technology, education, agriculture, construction, environmental
services, hospitality, retail, property, security, printing/publishing,
mining and logistics.

The proposed platform is a **modern corporate website and extensible web
application** that will serve as:

1.  The company's official digital presence.
2.  A corporate profile and business directory.
3.  A catalogue of business divisions and services.
4.  A project and portfolio showcase.
5.  A lead-generation and enquiry platform.
6.  A content management system.
7.  A foundation for future customer accounts, payments, bookings,
    ordering and business operations.

The system must be designed as a modular platform. The first release
should not attempt to build full operational management software for all
16 business areas. Instead, the architecture should support those
modules later while the MVP concentrates on the corporate website, CMS,
service discovery and customer engagement.

------------------------------------------------------------------------

# 2. Product Vision

## Vision Statement

To establish a credible, modern and scalable digital platform that
presents MOAUM Consultancy Services Limited as a diversified commercial
enterprise, makes its services easy to discover, creates a clear channel
for business enquiries, and provides the technical foundation for future
digital business operations.

## Product Positioning

The website should communicate:

-   Institutional credibility
-   Commercial capability
-   Modern technology
-   Professionalism
-   Reliability
-   Innovation
-   Social and economic impact
-   Connection to education and institutional development

The visual identity should be inspired by the supplied company logo
without reproducing it as a literal UI theme everywhere.

------------------------------------------------------------------------

# 3. Business Objectives

  -----------------------------------------------------------------------
  ID                                  Objective
  ----------------------------------- -----------------------------------
  BO-001                              Establish an authoritative
                                      corporate online presence.

  BO-002                              Clearly communicate the company's
                                      ownership and institutional
                                      relationship.

  BO-003                              Present all business divisions in a
                                      coherent information architecture.

  BO-004                              Make services and products easy to
                                      discover.

  BO-005                              Generate enquiries and quotation
                                      requests.

  BO-006                              Showcase projects, capabilities and
                                      achievements.

  BO-007                              Promote training programmes and
                                      capacity-building services.

  BO-008                              Provide a manageable CMS for
                                      authorized staff.

  BO-009                              Establish a foundation for future
                                      online payments and customer
                                      accounts.

  BO-010                              Establish a scalable architecture
                                      for future business-unit
                                      applications.
  -----------------------------------------------------------------------

------------------------------------------------------------------------

# 4. Scope

## 4.1 MVP In Scope

### Public Website

-   Home
-   About
-   Corporate profile
-   Mission, vision and values
-   University relationship
-   Leadership
-   Business divisions
-   Services
-   Projects/portfolio
-   Training
-   News and announcements
-   Careers
-   Downloads
-   FAQs
-   Gallery
-   Contact

### Customer Engagement

-   Contact enquiry
-   Service request
-   Quote request
-   File attachments
-   Email notifications
-   Request status management internally

### Administration

-   Authentication
-   Role-based access control
-   Dashboard
-   CMS
-   Business division management
-   Service management
-   Project management
-   News management
-   Training management
-   Media management
-   Enquiry management
-   User management
-   Audit logs
-   Site settings

## 4.2 Future Scope

-   Customer accounts
-   Customer dashboard
-   Online payments
-   Training registration/payment
-   Product ordering
-   E-commerce
-   Printing order management
-   Cleaning service scheduling
-   Security workforce management
-   Psychological/drug-testing appointment and controlled results
    management
-   Restaurant ordering
-   Property/hostel reservations
-   Logistics/haulage requests
-   Business-unit reporting
-   Mobile application/API consumers

------------------------------------------------------------------------

# 5. Business Divisions

The platform must initially support these 16 divisions as configurable
records rather than hard-coded pages:

1.  Printing and Publishing Services
2.  Industrial Cleaning and Fumigation Services
3.  Private Security Guards and Intelligence Services
4.  AI and Digital Technology Training Centre
5.  Psychological and Drug Testing Centre
6.  Consultancy Super Credit Store
7.  Restaurant, Bakery and Catering Services
8.  Training and Capacity-Building Services
9.  Agriculture and University Farms
10. Block and Construction Materials Industry
11. Construction Services
12. Waste Management and Environmental Services
13. Mining and Geo-Mining Services
14. Staff School and ICT Secondary School
15. Hostel and Property Development
16. Transportation and Logistics Services

Each division should have a lifecycle/status field so that operational
and proposed divisions can be distinguished.

Recommended statuses:

-   Active
-   Planned
-   Coming Soon
-   Suspended
-   Archived

------------------------------------------------------------------------

# 6. Target Users

## 6.1 Public Visitor

Can:

-   Browse the website
-   View company information
-   Browse divisions and services
-   View projects
-   Read news
-   Browse training
-   Download documents
-   Submit enquiries
-   Request services
-   Request quotations

## 6.2 Prospective Customer

Can:

-   Submit a service request
-   Request a quotation
-   Upload supporting documents
-   Provide project requirements
-   Receive confirmation

## 6.3 Training Participant

Potential future capabilities:

-   Browse programmes
-   Register
-   Pay
-   Receive confirmation
-   View training history
-   Receive certificates

## 6.4 Content Editor

Can manage approved content areas such as:

-   Pages
-   News
-   Services
-   Projects
-   Media
-   FAQs
-   Training content

## 6.5 Business Manager

Can manage business-unit-specific:

-   Services
-   Products
-   Projects
-   Requests
-   Enquiries
-   Business information

## 6.6 Administrator

Can manage:

-   Users
-   Content
-   Enquiries
-   Business divisions
-   Settings
-   Reports

## 6.7 Super Administrator

Has complete platform control including:

-   Roles
-   Permissions
-   System settings
-   Administrators
-   Audit logs

------------------------------------------------------------------------

# 7. Information Architecture

Recommended primary navigation:

``` text
HOME
ABOUT
OUR BUSINESSES
SERVICES
PROJECTS
TRAINING
NEWS & UPDATES
CAREERS
CONTACT
```

Primary CTA:

``` text
REQUEST A SERVICE
```

Secondary CTA:

``` text
REQUEST A QUOTE
```

## About

-   Company Profile
-   Mission & Vision
-   Core Values
-   Leadership
-   University Relationship

## Our Businesses

All 16 business divisions.

## Services

Cross-division service catalogue.

## Projects

Project portfolio.

## Training

Training programmes and capacity building.

## News & Updates

News, announcements, events and publications.

------------------------------------------------------------------------

# 8. Homepage Requirements

The homepage should establish the company proposition quickly and guide
visitors into the appropriate business division.

## Section Order

1.  Header/navigation
2.  Hero
3.  Trust/institutional relationship
4.  Business divisions
5.  Featured services
6.  Why MOAUM
7.  Statistics/impact
8.  Featured projects
9.  Training spotlight
10. Latest news
11. CTA
12. Footer

## Hero

The hero should include:

-   Strong corporate headline
-   Short value proposition
-   Primary CTA
-   Secondary CTA
-   Carefully selected imagery
-   Subtle motion/interaction
-   Institutional credibility cue

Avoid excessive animation.

------------------------------------------------------------------------

# 9. Corporate Profile

The About page should provide:

-   Company history
-   Corporate purpose
-   Mission
-   Vision
-   Core values
-   Ownership
-   Relationship with the University
-   Leadership
-   Strategic direction
-   Areas of operation

The exact legal wording regarding the University ownership must be
approved by the client.

------------------------------------------------------------------------

# 10. Business Division Module

Each division should have a reusable profile template.

## Fields

``` text
id
name
slug
short_description
full_description
category
status
featured
icon
hero_image
cover_image
contact_email
contact_phone
location
operating_hours
sort_order
seo_title
seo_description
published_at
created_at
updated_at
```

## Division Page

Recommended sections:

1.  Division hero
2.  Overview
3.  Services
4.  Products, where applicable
5.  Capabilities
6.  Projects
7.  Gallery
8.  FAQs
9.  Contact/request CTA

------------------------------------------------------------------------

# 11. Service Catalogue

Services must belong to a business division.

## Service fields

``` text
id
business_division_id
name
slug
short_description
description
service_type
pricing_type
starting_price
featured
status
image
sort_order
seo_title
seo_description
created_at
updated_at
```

## Pricing Types

-   Fixed
-   Starting From
-   Quote Required
-   Contact Us
-   Not Published

Do not expose internal pricing unless the business manager has approved
it.

------------------------------------------------------------------------

# 12. Service Request

Visitors should be able to request a service without necessarily
creating an account.

## Form

-   Full name
-   Organization
-   Email
-   Phone
-   Business division
-   Service
-   Location
-   Preferred date
-   Requirements
-   Budget range
-   Attachment
-   Consent

## Request Status

``` text
New
Assigned
In Progress
Awaiting Customer
Quoted
Completed
Closed
Cancelled
```

------------------------------------------------------------------------

# 13. Quote Request

A generic quotation workflow should support services whose price depends
on requirements.

## Fields

-   Name
-   Organization
-   Email
-   Phone
-   Division
-   Service
-   Project title
-   Location
-   Requirements
-   Estimated quantity/size
-   Desired start date
-   Desired completion date
-   Budget range
-   Attachment

Future quote workflow:

``` text
Request
  ↓
Review
  ↓
Clarification
  ↓
Quote Preparation
  ↓
Quote Sent
  ↓
Accepted / Declined
  ↓
Order / Contract
```

------------------------------------------------------------------------

# 14. Projects / Portfolio

Projects provide proof of capability.

## Fields

-   Project title
-   Division
-   Client
-   Location
-   Description
-   Scope
-   Start date
-   Completion date
-   Status
-   Featured image
-   Gallery
-   Documents
-   Tags
-   Published date

Statuses:

-   Planned
-   Ongoing
-   Completed
-   Suspended
-   Cancelled

------------------------------------------------------------------------

# 15. Training Module

Training should support:

-   Programme title
-   Description
-   Division
-   Course category
-   Trainer
-   Duration
-   Start date
-   End date
-   Delivery mode
-   Venue
-   Fee
-   Capacity
-   Registration deadline
-   Curriculum
-   Requirements
-   Certificate information
-   Status

Delivery modes:

-   Physical
-   Online
-   Hybrid

Future workflow:

``` text
Programme
  ↓
Registration
  ↓
Payment
  ↓
Confirmation
  ↓
Attendance
  ↓
Certificate
```

------------------------------------------------------------------------

# 16. News and Announcements

CMS-managed publishing module.

Fields:

-   Title
-   Slug
-   Excerpt
-   Content
-   Featured image
-   Author
-   Category
-   Tags
-   Status
-   Publication date
-   SEO metadata

Statuses:

-   Draft
-   Review
-   Scheduled
-   Published
-   Archived

------------------------------------------------------------------------

# 17. Careers

Optional MVP module, recommended if the company intends to recruit
through the website.

Fields:

-   Job title
-   Division
-   Location
-   Employment type
-   Description
-   Responsibilities
-   Qualifications
-   Requirements
-   Application deadline
-   Status

Applications may include:

-   Name
-   Email
-   Phone
-   CV
-   Cover letter
-   Qualifications

------------------------------------------------------------------------

# 18. Downloads

Central document library.

Categories:

-   Company profile
-   Brochures
-   Service catalogues
-   Forms
-   Reports
-   Policies
-   Training documents
-   Tender documents

File controls:

-   File type
-   File size
-   Access level
-   Download count
-   Publication status

------------------------------------------------------------------------

# 19. Gallery

Gallery should support:

-   Corporate gallery
-   Business-unit gallery
-   Project gallery
-   Training/event gallery

Images should be optimized automatically where practical.

------------------------------------------------------------------------

# 20. Contact Module

Contact information should be configurable from the CMS.

Include:

-   Address
-   Phone
-   Email
-   Business hours
-   Map
-   Social media
-   Contact form

------------------------------------------------------------------------

# 21. Enquiry Management

All enquiries should be persisted.

## Enquiry fields

``` text
id
name
organization
email
phone
subject
business_division_id
service_id
message
attachment
status
assigned_to
priority
internal_notes
resolved_at
created_at
updated_at
```

## Priority

-   Low
-   Normal
-   High
-   Urgent

------------------------------------------------------------------------

# 22. CMS

The CMS should manage:

-   Pages
-   Business divisions
-   Services
-   Products
-   Projects
-   News
-   Training
-   Events
-   FAQs
-   Careers
-   Downloads
-   Gallery
-   Testimonials
-   Site settings
-   SEO metadata

Content should be editable without modifying source code.

------------------------------------------------------------------------

# 23. Authentication and RBAC

Recommended roles:

``` text
Super Administrator
Administrator
Content Editor
Business Manager
Customer
Training Participant
```

Use role/permission-based authorization rather than checking role names
directly throughout controllers.

Recommended permission examples:

``` text
view-pages
create-pages
edit-pages
publish-pages
delete-pages

view-services
create-services
edit-services
publish-services
delete-services

view-enquiries
assign-enquiries
update-enquiries
close-enquiries

manage-users
manage-roles
manage-settings
view-audit-logs
```

------------------------------------------------------------------------

# 24. Administration Dashboard

Dashboard widgets:

-   Total enquiries
-   New enquiries
-   Open service requests
-   Quote requests
-   Active business divisions
-   Active services
-   Projects
-   Training programmes
-   News articles
-   Users

Charts:

-   Enquiries by month
-   Requests by division
-   Requests by service
-   Training registrations

------------------------------------------------------------------------

# 25. Notifications

Email notifications should be implemented through Laravel
Notifications/Queues.

Administrative notifications:

-   New enquiry
-   New service request
-   New quote request
-   New application
-   New training registration
-   Payment received

Customer notifications:

-   Request received
-   Request status changed
-   Quote available
-   Registration confirmed
-   Payment confirmed

------------------------------------------------------------------------

# 26. Payment Architecture

Payments are a future module and should be implemented behind a gateway
abstraction.

Potential gateways:

-   Paystack
-   Flutterwave

Conceptual workflow:

``` text
Customer
  ↓
Order / Registration
  ↓
Payment Initiation
  ↓
Gateway
  ↓
Webhook
  ↓
Signature Verification
  ↓
Transaction
  ↓
Receipt
```

Never trust a browser redirect alone as proof of payment. Payment status
must be verified server-side and reconciled through verified
webhooks/API responses.

------------------------------------------------------------------------

# 27. Database Design

Recommended core tables:

``` text
users
roles
permissions
business_divisions
services
service_categories
products
product_categories
projects
project_images
pages
news
news_categories
training_programmes
training_registrations
events
enquiries
service_requests
quote_requests
documents
media
galleries
gallery_images
faqs
careers
applications
notifications
payments
transactions
audit_logs
settings
```

Future operational modules should introduce their own tables rather than
overloading the corporate tables.

------------------------------------------------------------------------

# 28. Relationship Model

``` text
BUSINESS DIVISION
    ├── SERVICES
    ├── PRODUCTS
    ├── PROJECTS
    ├── TRAINING PROGRAMMES
    └── ENQUIRIES

USER
    ├── SERVICE REQUESTS
    ├── QUOTE REQUESTS
    ├── TRAINING REGISTRATIONS
    └── PAYMENTS

SERVICE REQUEST
    ├── BUSINESS DIVISION
    └── SERVICE
```

------------------------------------------------------------------------

# 29. Technical Architecture

## Backend

-   Laravel 13
-   PHP version supported by Laravel 13
-   MySQL
-   Laravel authentication
-   Spatie Laravel Permission or equivalent RBAC package
-   Laravel Notifications
-   Laravel Queues
-   Laravel Scheduler
-   Laravel Storage
-   Laravel Policies
-   Form Requests
-   Eloquent ORM

## Frontend

-   HTML5
-   Tailwind CSS
-   Blade templates
-   Vanilla JavaScript or Alpine.js where useful
-   Responsive/mobile-first design

Avoid introducing React/Next.js unless later requirements justify a
separate SPA/frontend.

------------------------------------------------------------------------

# 30. Laravel Application Structure

Recommended organization:

``` text
app/
├── Actions/
├── DTOs/
├── Enums/
├── Events/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Notifications/
├── Policies/
├── Services/
└── Support/
```

For larger business modules, feature-oriented organization may be used:

``` text
app/Modules/
├── BusinessDivisions/
├── Services/
├── Projects/
├── Training/
├── News/
├── Enquiries/
├── Customers/
├── Payments/
└── Administration/
```

Do not over-engineer the first release; establish clear boundaries that
can grow.

------------------------------------------------------------------------

# 31. Routing Strategy

Public routes:

``` text
/
 /about
 /businesses
 /businesses/{slug}
 /services
 /services/{slug}
 /projects
 /projects/{slug}
 /training
 /training/{slug}
 /news
 /news/{slug}
 /careers
 /downloads
 /faqs
 /contact
 /request-service
 /request-quote
```

Administrative routes:

``` text
/admin
/admin/dashboard
/admin/businesses
/admin/services
/admin/projects
/admin/training
/admin/news
/admin/enquiries
/admin/users
/admin/settings
/admin/audit-logs
```

Use route model binding where appropriate.

------------------------------------------------------------------------

# 32. Security Requirements

The system must implement:

-   HTTPS
-   CSRF protection
-   XSS protection
-   SQL injection prevention through framework/database abstractions
-   Secure password hashing
-   Authorization policies
-   RBAC
-   Rate limiting
-   Secure file validation
-   Session security
-   Audit logging
-   Backup strategy
-   Secure secrets/environment configuration
-   Payment webhook verification when payments are implemented

Sensitive psychological/drug-testing data must never be exposed through
public routes or broad administrative permissions.

------------------------------------------------------------------------

# 33. Privacy and Data Protection

The system may process:

-   Names
-   Email
-   Phone
-   Addresses
-   Uploaded documents
-   Applications
-   Training records
-   Payment records
-   Potentially sensitive assessment information

The final privacy notice, retention periods, consent wording and access
policies should be approved by the client's legal/compliance
representatives.

------------------------------------------------------------------------

# 34. SEO

The CMS should support:

-   SEO title
-   Meta description
-   Canonical URL
-   Open Graph image
-   Social title/description
-   XML sitemap
-   robots.txt
-   Breadcrumbs
-   Semantic HTML
-   Image alt text
-   Structured data where appropriate

Every public content type should have a clean slug.

------------------------------------------------------------------------

# 35. Performance

Requirements:

-   Responsive images
-   Lazy loading
-   Efficient database queries
-   Pagination
-   Query indexing
-   Browser caching
-   Server-side caching where appropriate
-   Queued email
-   Optimized assets
-   Minimal JavaScript
-   Avoid unnecessary third-party scripts

------------------------------------------------------------------------

# 36. Accessibility

Design and implementation should target WCAG-aligned practices:

-   Semantic HTML
-   Keyboard navigation
-   Visible focus states
-   Form labels
-   Accessible error messages
-   Sufficient contrast
-   Alt text
-   Logical heading hierarchy
-   Reduced-motion consideration

------------------------------------------------------------------------

# 37. Search

Global search should eventually index:

-   Business divisions
-   Services
-   Products
-   Projects
-   News
-   Training
-   FAQs
-   Downloads

The initial MVP can use MySQL search/query capabilities; a dedicated
search engine can be introduced later if required.

------------------------------------------------------------------------

# 38. Design System Requirements

The supplied MOAUM logo should guide the visual system.

Primary extracted visual families:

-   Deep/bright red
-   Strong blue
-   Green
-   Black/charcoal
-   White

Recommended working tokens are documented separately in
`MOAUM_Design_System.md`.

The UI should use these colours selectively. Avoid making every section
red, blue and green simultaneously.

------------------------------------------------------------------------

# 39. Modern UI Direction

The website should use:

-   Large editorial typography
-   Generous whitespace
-   Strong visual hierarchy
-   Clean card layouts
-   Subtle shadows
-   Soft borders
-   Rounded but professional components
-   High-quality photography
-   Responsive grids
-   Subtle micro-interactions
-   Sticky navigation
-   Clear CTAs
-   Accessible forms
-   Modern dashboard patterns

Avoid:

-   Excessive gradients
-   Excessive glassmorphism
-   Excessive animations
-   Cluttered cards
-   Tiny text
-   Overuse of red
-   Template-like generic corporate layouts

------------------------------------------------------------------------

# 40. Responsive Breakpoints

Use Tailwind responsive conventions.

Primary targets:

``` text
Mobile
Tablet
Laptop
Desktop
Large Desktop
```

The mobile experience must be designed deliberately, not treated as a
compressed desktop page.

------------------------------------------------------------------------

# 41. Non-Functional Requirements

  Category          Requirement
  ----------------- ------------------------------------------
  Availability      Production-ready hosting with monitoring
  Security          HTTPS, RBAC, validation, secure sessions
  Performance       Optimized assets and database queries
  Maintainability   Modular Laravel architecture
  Scalability       Business modules can be added later
  Accessibility     WCAG-aligned
  SEO               Technical SEO support
  Responsiveness    Mobile/tablet/desktop
  Auditability      Administrative audit logs
  Backup            Automated database/file backup
  Reliability       Graceful error handling

------------------------------------------------------------------------

# 42. MVP Acceptance Criteria

## Public Website

-   [ ] Home page works on all target screen sizes.
-   [ ] Company profile is editable.
-   [ ] All 16 business divisions can be displayed.
-   [ ] Divisions can be marked active/planned/coming soon.
-   [ ] Services can be managed dynamically.
-   [ ] Projects can be managed dynamically.
-   [ ] Training can be managed dynamically.
-   [ ] News can be managed dynamically.
-   [ ] Contact forms work.
-   [ ] Service requests work.
-   [ ] Quote requests work.
-   [ ] Documents can be downloaded.
-   [ ] SEO metadata is manageable.

## Administration

-   [ ] Admin authentication works.
-   [ ] Roles and permissions work.
-   [ ] CMS CRUD operations work.
-   [ ] Enquiries can be assigned and tracked.
-   [ ] Audit logging works.
-   [ ] Media management works.

## Technical

-   [ ] Database migrations are reproducible.
-   [ ] Validation is implemented.
-   [ ] Authorization is implemented.
-   [ ] Error handling is implemented.
-   [ ] Production configuration is separated from development
    configuration.
-   [ ] Application is deployable.
-   [ ] Backup procedure exists.

------------------------------------------------------------------------

# 43. Development Phases

## Phase 0 --- Discovery

-   Validate requirements
-   Obtain brand assets
-   Confirm content
-   Confirm business statuses
-   Confirm legal wording
-   Confirm administrators
-   Confirm domain/hosting

## Phase 1 --- UX and Prototype

-   Information architecture
-   Design system
-   Wireframes
-   High-fidelity screens
-   Responsive states
-   Admin dashboard prototype
-   User flow validation

## Phase 2 --- Laravel Foundation

-   Project setup
-   Database
-   Authentication
-   RBAC
-   Layout system
-   Components
-   CMS foundation

## Phase 3 --- Corporate Modules

-   Business divisions
-   Services
-   Projects
-   News
-   Training
-   Gallery
-   Downloads
-   FAQs

## Phase 4 --- Customer Engagement

-   Contact
-   Service request
-   Quote request
-   Notifications
-   Enquiry management

## Phase 5 --- QA and Hardening

-   Functional testing
-   Responsive testing
-   Accessibility testing
-   Security review
-   Performance optimization
-   SEO validation

## Phase 6 --- Deployment

-   Production environment
-   Database migration
-   Storage
-   Mail
-   SSL
-   Backups
-   Monitoring
-   Final acceptance

------------------------------------------------------------------------

# 44. Future Enterprise Modules

The platform should eventually support:

``` text
Printing Management
Cleaning/Fumigation Management
Security Management
Training Management
Psychological/Drug Testing
Retail/Credit Store
Restaurant/Catering
Agriculture
Construction Materials
Construction Projects
Waste Management
Mining/Geo-Mining
School Systems
Property/Hostel Management
Transport/Logistics
```

Each should be independently scoped before development.

------------------------------------------------------------------------

# 45. Important Requirements Still to Be Confirmed

The following should not be assumed as final requirements:

1.  Exact corporate legal identity.
2.  Public presentation of shareholding.
3.  Current versus proposed status of each business.
4.  Exact leadership structure.
5.  Customer account requirement.
6.  Online payment requirement.
7.  E-commerce requirement.
8.  Training payment/registration.
9.  School system integration.
10. Property reservation.
11. Logistics booking.
12. SMS provider.
13. Social media channels.
14. Exact office locations.
15. Brand typography.
16. Final company slogan/value proposition.
17. Content ownership and approval workflow.
18. Hosting/deployment requirements.

------------------------------------------------------------------------

# 46. Definition of Done

A feature is complete only when:

1.  Requirements are implemented.
2.  UI is responsive.
3.  Validation exists.
4.  Authorization exists.
5.  Error handling exists.
6.  Database constraints exist.
7.  Tests exist where appropriate.
8.  SEO/accessibility requirements are met for public content.
9.  No debug credentials/secrets are committed.
10. The feature is documented.
11. The feature is tested against the approved prototype.
12. The feature is production-ready.

------------------------------------------------------------------------

# 47. Final Product Strategy

The first version should present MOAUM as a **modern diversified
enterprise**, not as 16 disconnected businesses.

The platform should have a strong corporate layer:

``` text
MOAUM
  │
  ├── Corporate Identity
  ├── Business Portfolio
  ├── Services
  ├── Projects
  ├── Training
  ├── News
  └── Customer Engagement
```

The deeper operational systems should then grow underneath:

``` text
MOAUM DIGITAL PLATFORM
       │
       ├── Corporate CMS
       ├── Customer Portal
       ├── Business Operations
       ├── Payments
       ├── Reporting
       └── Integrations
```

This architecture allows the website to launch as a manageable MVP while
protecting the long-term investment in the platform.
