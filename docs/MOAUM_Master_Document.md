# MOAUM Consultancy Services Limited --- Master Product & Development Document

This document consolidates the PRD/SRD, design system and
prototype-first implementation prompt.

------------------------------------------------------------------------

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

------------------------------------------------------------------------

# MOAUM Consultancy Services Limited

## Visual Design System

### Logo-Derived Modern Corporate UI System

**Version:** 1.0\
**Technology:** Tailwind CSS + HTML5 + Laravel Blade

------------------------------------------------------------------------

# 1. Design Direction

The supplied MOAUM Consultancy Services Limited logo contains four
dominant visual families:

-   Red
-   Blue
-   Green
-   Black/charcoal
-   White

The logo also combines:

-   Engineering/industry symbolism through the gear
-   Knowledge/education through the open book
-   Agriculture through the green field
-   Institutional identity through the university reference
-   A key motif through the central blue vertical element

The website should translate these ideas into a **modern enterprise
design language** rather than copying the logo literally.

## Design keywords

``` text
Modern
Institutional
Professional
Confident
Innovative
Enterprise
African
Technology-driven
Trustworthy
Clean
```

------------------------------------------------------------------------

# 2. Colour Tokens

The following working colours are derived from visual inspection of the
supplied logo. They should be treated as design tokens and refined
against the official brand guide if one exists.

## Primary Red

``` text
MOAUM Red
HEX: #C20409
RGB: 194, 4, 9
```

Use for:

-   Primary brand accents
-   Important CTAs
-   Active navigation states
-   Highlights
-   Alerts where semantically appropriate

Do not use large solid red areas everywhere.

## Primary Blue

``` text
MOAUM Blue
HEX: #0887D6
RGB: 8, 135, 214
```

Use for:

-   Technology-related content
-   Links
-   Secondary CTAs
-   Interactive controls
-   Information states
-   Digital/AI division branding

## Primary Green

``` text
MOAUM Green
HEX: #058230
RGB: 5, 130, 48
```

Use for:

-   Agriculture
-   Environmental services
-   Success states
-   Sustainability
-   Positive business indicators

## Charcoal

``` text
MOAUM Charcoal
HEX: #1D241D
RGB: 29, 36, 29
```

Use as the primary dark text/background colour.

## White

``` text
MOAUM White
HEX: #FFFFFF
```

Use for:

-   Main backgrounds
-   Card surfaces
-   Text on dark backgrounds

------------------------------------------------------------------------

# 3. Supporting Neutrals

Recommended Tailwind-style neutral system:

``` text
Slate 50   #F8FAFC
Slate 100  #F1F5F9
Slate 200  #E2E8F0
Slate 300  #CBD5E1
Slate 400  #94A3B8
Slate 500  #64748B
Slate 600  #475569
Slate 700  #334155
Slate 800  #1E293B
Slate 900  #0F172A
```

Use neutrals for most UI surfaces. Brand colours should provide emphasis
rather than dominate every page.

------------------------------------------------------------------------

# 4. Semantic Colours

``` text
Success: #15803D
Warning: #CA8A04
Danger:  #B91C1C
Info:    #0284C7
```

These should be visually distinguishable from brand colours.

------------------------------------------------------------------------

# 5. Colour Usage Ratio

A practical visual ratio:

``` text
60% Neutral / White
20% Charcoal / Dark surfaces
10% Blue
7% Red
3% Green
```

This is a design guideline rather than a mathematical constraint.

The purpose is to prevent the website from looking like a direct
recreation of the logo.

------------------------------------------------------------------------

# 6. Typography

## Primary Typeface

Recommended:

**Inter**

Why:

-   Excellent UI readability
-   Modern
-   Professional
-   Strong numerical glyphs
-   Works well with Tailwind
-   Good dashboard typography

## Secondary / Display Typeface

Recommended:

**Plus Jakarta Sans**

Use selectively for:

-   Hero headlines
-   Major section headings
-   Marketing statements

If simplicity is preferred, use Inter for the entire system.

------------------------------------------------------------------------

# 7. Typography Scale

``` text
Display 2XL: 72px / 1.05
Display XL:  60px / 1.08
Display LG:  48px / 1.10

H1: 40px / 1.15
H2: 32px / 1.20
H3: 26px / 1.25
H4: 22px / 1.30
H5: 18px / 1.40

Body LG: 18px / 1.65
Body:    16px / 1.60
Body SM: 14px / 1.55
Caption: 12px / 1.45
```

Responsive typography should scale down on smaller screens.

------------------------------------------------------------------------

# 8. Font Weights

``` text
Regular: 400
Medium: 500
Semibold: 600
Bold: 700
ExtraBold: 800
```

Use 700/800 selectively. Avoid making entire pages visually heavy.

------------------------------------------------------------------------

# 9. Layout System

Use a centered container.

Recommended maximum width:

``` text
max-width: 1280px
```

For large editorial sections:

``` text
max-width: 1400px
```

Mobile horizontal padding:

``` text
16px
```

Tablet:

``` text
24px
```

Desktop:

``` text
32px
```

------------------------------------------------------------------------

# 10. Spacing

Use an 8px-oriented spacing rhythm.

Examples:

``` text
4px
8px
12px
16px
24px
32px
40px
48px
64px
80px
96px
128px
```

Large sections should have generous vertical spacing.

------------------------------------------------------------------------

# 11. Border Radius

Recommended:

``` text
sm: 6px
md: 10px
lg: 16px
xl: 24px
2xl: 32px
```

Use `lg` and `xl` for cards.

Avoid excessive pill-shaped elements except for tags/statuses.

------------------------------------------------------------------------

# 12. Shadows

Use subtle shadows.

``` text
Card:
0 4px 20px rgba(15, 23, 42, 0.06)

Elevated:
0 12px 40px rgba(15, 23, 42, 0.10)
```

Do not make every element shadowed.

------------------------------------------------------------------------

# 13. Buttons

## Primary

Background:

``` text
#C20409
```

Text:

``` text
#FFFFFF
```

Use for high-priority actions.

Examples:

-   Request a Service
-   Request a Quote
-   Register Now

## Secondary

Background:

``` text
#0887D6
```

## Outline

White/dark depending on background.

## Ghost

Transparent with subtle hover surface.

------------------------------------------------------------------------

# 14. Cards

Cards should have:

-   White or neutral surface
-   1px subtle border
-   16--24px radius
-   24px padding
-   Clear heading
-   Supporting description
-   Strong CTA

Use visual imagery for business divisions and projects.

------------------------------------------------------------------------

# 15. Hero Design

Recommended homepage hero:

``` text
┌─────────────────────────────────────────────┐
│ NAVIGATION                                  │
├─────────────────────────────────────────────┤
│                                             │
│  Building Enterprise Value                 │
│  Through Diverse Business Solutions        │
│                                             │
│  Short corporate statement...              │
│                                             │
│  [Request a Service] [Explore Businesses]  │
│                                             │
│                         Visual / Image      │
│                                             │
└─────────────────────────────────────────────┘
```

Use a large editorial image or carefully composed business imagery.

Avoid generic corporate handshake stock photos.

------------------------------------------------------------------------

# 16. Business Division Cards

Each division card should have:

-   Image/icon
-   Business name
-   Short description
-   Category/status
-   Arrow/action
-   Hover transition

Example visual hierarchy:

``` text
[IMAGE]

Printing & Publishing
Commercial printing, publishing
and institutional documentation.

Explore division →
```

------------------------------------------------------------------------

# 17. Business Colour Accents

The brand system can use controlled accent colours by business theme:

  Business Theme   Accent
  ---------------- ---------------
  Corporate        Red
  Technology/AI    Blue
  Agriculture      Green
  Construction     Charcoal
  Environment      Green
  Training         Blue
  Hospitality      Red/neutral
  Security         Charcoal
  Publishing       Red
  Property         Blue/charcoal

Do not assign arbitrary colours to every division.

------------------------------------------------------------------------

# 18. Navigation

Desktop:

-   White or very light header
-   Logo
-   Main navigation
-   Primary CTA
-   Optional utility link

Mobile:

-   Logo
-   Menu trigger
-   Full-screen/slide-over menu
-   Primary CTA

Navigation should remain visually clean despite the 16 business
divisions.

------------------------------------------------------------------------

# 19. Mega Menu

Because there are 16 business divisions, use a mega menu for:

**Our Businesses**

Recommended grouping:

``` text
Business & Professional
- Printing & Publishing
- Security
- Cleaning
- Consultancy

Technology & Education
- AI & Digital Technology
- Training
- Staff School
- ICT Secondary School

Commerce & Hospitality
- Super Credit Store
- Restaurant/Bakery/Catering
- Property

Industry & Infrastructure
- Construction
- Construction Materials
- Waste Management
- Mining

Agriculture & Logistics
- Agriculture
- Transportation
```

This prevents a long vertical dropdown.

------------------------------------------------------------------------

# 20. Section Design

A typical section should follow:

``` text
Eyebrow
Headline
Supporting paragraph

Content / Cards / Image

Optional CTA
```

Example:

``` text
OUR BUSINESS PORTFOLIO

Diversified capabilities.
One enterprise platform.

Explore the business units...
```

------------------------------------------------------------------------

# 21. Statistics

Use large numbers with concise labels.

Example:

``` text
16+
Business Areas

51%
University Shareholding

100%
Commitment to Service

∞
Opportunities for Growth
```

Only use statistics that are factually approved by the client. Avoid
inventing performance metrics.

------------------------------------------------------------------------

# 22. Image Direction

Photography should emphasize:

-   Real facilities
-   Real staff
-   Real projects
-   Training sessions
-   Agriculture
-   Construction
-   Technology
-   Hospitality
-   Institutional environments

Prefer authentic photography over generic stock photography.

------------------------------------------------------------------------

# 23. Iconography

Use one consistent icon library.

Recommended:

**Lucide Icons**

Style:

-   Outline
-   1.5--2px stroke
-   Simple geometry

Do not mix multiple icon families.

------------------------------------------------------------------------

# 24. Motion

Motion should be subtle.

Recommended:

-   Fade-up on scroll
-   Card hover lift
-   Button micro-interactions
-   Image zoom on hover
-   Menu transitions
-   Number count-up only where useful

Respect reduced-motion preferences.

------------------------------------------------------------------------

# 25. Forms

Forms should be:

-   Single-column on mobile
-   Two-column where appropriate on desktop
-   Clearly labelled
-   Accessible
-   Validation-aware

Input style:

``` text
Height: 48–52px
Radius: 10–12px
Border: 1px
Focus ring: MOAUM Blue
```

Error states should be clear and not rely solely on colour.

------------------------------------------------------------------------

# 26. Dashboard Design

Admin dashboard should use:

-   Light neutral background
-   White cards
-   Charcoal typography
-   Blue interactive controls
-   Red high-priority actions
-   Green success indicators

Layout:

``` text
Sidebar
    │
    ├── Dashboard
    ├── Business
    ├── Services
    ├── Projects
    ├── Training
    ├── News
    ├── Enquiries
    ├── Media
    ├── Users
    └── Settings

Main Content
```

------------------------------------------------------------------------

# 27. Tailwind Design Tokens

Suggested conceptual configuration:

``` js
colors: {
  moaum: {
    red: '#C20409',
    blue: '#0887D6',
    green: '#058230',
    charcoal: '#1D241D',
    white: '#FFFFFF',
  }
}
```

Additional semantic tokens can be introduced for:

-   Surface
-   Border
-   Muted text
-   Success
-   Warning
-   Danger
-   Info

------------------------------------------------------------------------

# 28. Design Principles

## Principle 1 --- Corporate First

The interface should feel like one enterprise.

## Principle 2 --- Business Diversity Without Visual Chaos

The 16 business units should feel related.

## Principle 3 --- Strong Typography

Typography creates hierarchy more effectively than excessive decoration.

## Principle 4 --- Authentic Imagery

Use real MOAUM imagery whenever available.

## Principle 5 --- Conversion-Oriented

Every major page should provide a clear next action.

## Principle 6 --- Progressive Disclosure

Do not expose every business detail at once.

## Principle 7 --- Accessible by Default

Accessibility should be designed in from the beginning.

------------------------------------------------------------------------

# 29. Suggested Page Aesthetic

## Home

Editorial + corporate.

## Business Pages

Image-led + service-oriented.

## Projects

Portfolio/gallery driven.

## Training

Modern education/technology aesthetic.

## News

Clean publication layout.

## Contact

Simple, reassuring and action-focused.

## Admin

Functional enterprise dashboard.

------------------------------------------------------------------------

# 30. Design System Do/Don't

### Do

-   Use whitespace.
-   Use strong headlines.
-   Use real photography.
-   Use red strategically.
-   Use blue for technology/interaction.
-   Use green for agriculture/environment/success.
-   Use neutral surfaces.
-   Use consistent components.

### Don't

-   Make the whole website red.
-   Use the logo as a background everywhere.
-   Use too many gradients.
-   Use excessive glassmorphism.
-   Mix fonts unnecessarily.
-   Use low-quality stock images.
-   Use giant animated counters without meaning.
-   Create 16 unrelated visual styles.

------------------------------------------------------------------------

# 31. Logo Usage

The supplied logo should be used primarily in:

-   Header
-   Footer
-   Login
-   Admin interface
-   Documents/receipts
-   Favicon/brand assets where appropriate

Maintain clear space around the logo.

Do not:

-   Stretch the logo
-   Change its proportions
-   Recolour it arbitrarily
-   Add effects that reduce legibility

If an official SVG/vector version exists, use it instead of the supplied
raster image for production.

------------------------------------------------------------------------

# 32. Design Acceptance Criteria

The prototype should be considered visually acceptable when:

-   It feels modern without becoming visually trendy.
-   The logo remains recognizable and correctly proportioned.
-   Brand colours are consistent.
-   The 16 business areas remain easy to navigate.
-   Mobile layouts are deliberate.
-   CTA hierarchy is clear.
-   Typography is readable.
-   Admin and public interfaces feel like the same product.
-   The interface remains usable without animations.

------------------------------------------------------------------------

# MOAUM Consultancy Services Limited

# Master AI Development Prompt

## Prototype-First → Production Implementation Workflow

You are the lead product designer, UX architect, solution architect and
senior Laravel engineer responsible for designing and implementing the
MOAUM Consultancy Services Limited digital platform.

------------------------------------------------------------------------

# 1. PROJECT CONTEXT

MOAUM Consultancy Services Limited is the official business and
investment arm of Rev. Fr. Moses Orshio Adasu University, Makurdi. The
University owns 51% of the company shares.

The company operates across 16 business areas:

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

The first release is a modern corporate website/web application and CMS.
It must be architected so that future business-unit applications can be
added without rebuilding the core platform.

------------------------------------------------------------------------

# 2. AUTHORITATIVE DOCUMENTS

Before designing or coding, read and use these project documents as the
source of truth:

-   `MOAUM_PRD_SRD.md`
-   `MOAUM_Design_System.md`

If additional client requirements are provided later, treat them as
amendments and reconcile them with the existing requirements rather than
blindly duplicating functionality.

When requirements conflict, identify the conflict explicitly and propose
a resolution before implementation.

------------------------------------------------------------------------

# 3. TECHNOLOGY STACK

Use exactly this primary stack unless an explicit project requirement
changes it:

## Backend

-   Laravel 13
-   PHP version supported by Laravel 13
-   MySQL
-   Blade
-   Eloquent ORM
-   Laravel validation/Form Requests
-   Laravel Policies
-   Laravel Notifications
-   Laravel Queues where appropriate
-   Laravel Scheduler where appropriate
-   Laravel Storage
-   Spatie Laravel Permission or equivalent RBAC implementation

## Frontend

-   HTML5
-   Tailwind CSS
-   Blade
-   Alpine.js only where it provides useful lightweight interaction
-   Minimal vanilla JavaScript where practical

Do not introduce React, Vue, Next.js or a separate SPA unless a later
requirement explicitly justifies it.

------------------------------------------------------------------------

# 4. DESIGN SOURCE

Use the supplied MOAUM logo as the visual reference.

Logo-derived design tokens:

``` text
MOAUM Red:      #C20409
MOAUM Blue:     #0887D6
MOAUM Green:    #058230
MOAUM Charcoal: #1D241D
White:          #FFFFFF
```

Typography:

``` text
Primary: Inter
Optional Display: Plus Jakarta Sans
```

The design must be modern, premium, institutional and
enterprise-oriented.

Do not simply copy the logo colours onto every component.

Use neutral backgrounds extensively and use red, blue and green
strategically.

------------------------------------------------------------------------

# 5. CORE PRODUCT PRINCIPLE

Build this as:

``` text
Corporate Website
        +
Content Management System
        +
Customer Engagement Platform
        +
Future Enterprise Platform Foundation
```

Do not attempt to build all 16 business operations in the first release.

------------------------------------------------------------------------

# 6. PROTOTYPE-FIRST RULE

DO NOT immediately start implementing the full application.

First create a complete interactive prototype and obtain logical
approval.

The workflow must be:

``` text
Requirements
      ↓
Information Architecture
      ↓
Design Tokens
      ↓
Wireframes
      ↓
High-Fidelity Prototype
      ↓
Responsive Prototype
      ↓
User Flow Validation
      ↓
Prototype Approval
      ↓
Technical Architecture
      ↓
Database
      ↓
Laravel Implementation
      ↓
Testing
      ↓
Deployment
```

The prototype is a design contract for the implementation.

------------------------------------------------------------------------

# 7. PHASE 0 --- REQUIREMENT ANALYSIS

Before writing code:

1.  Read all requirements.
2.  Identify confirmed requirements.
3.  Identify assumptions.
4.  Identify missing information.
5.  Identify conflicts.
6.  Identify future functionality.
7.  Convert requirements into modules.
8.  Define user roles.
9.  Define major workflows.
10. Define acceptance criteria.

Create:

``` text
requirements-matrix.md
module-list.md
open-questions.md
```

Do not invent business rules.

Where a requirement is missing, use a sensible placeholder and clearly
mark it as `TO BE CONFIRMED`.

------------------------------------------------------------------------

# 8. PHASE 1 --- INFORMATION ARCHITECTURE

Create the complete site map.

Minimum structure:

``` text
Home
About
  Company Profile
  Mission & Vision
  Core Values
  Leadership
  University Relationship

Our Businesses
  All 16 business divisions

Services
Projects
Training
News & Updates
Careers
Downloads
FAQs
Contact
Request a Service
Request a Quote
```

Create a mega menu for the 16 business divisions.

Group them logically rather than presenting a huge unstructured list.

------------------------------------------------------------------------

# 9. PHASE 2 --- DESIGN SYSTEM

Before designing pages, establish:

-   Colours
-   Typography
-   Spacing
-   Container widths
-   Buttons
-   Cards
-   Inputs
-   Selects
-   Tables
-   Badges
-   Alerts
-   Modals
-   Navigation
-   Footer
-   Breadcrumbs
-   Pagination
-   Empty states
-   Loading states
-   Error states
-   Success states
-   Dashboard cards
-   Charts
-   Mobile navigation

Build reusable Tailwind/Blade components.

Suggested component architecture:

``` text
resources/views/components/
├── layout/
├── navigation/
├── buttons/
├── cards/
├── forms/
├── badges/
├── alerts/
├── modals/
├── tables/
├── pagination/
├── media/
└── sections/
```

Do not duplicate markup unnecessarily.

------------------------------------------------------------------------

# 10. PHASE 3 --- PUBLIC WEBSITE PROTOTYPE

Create a high-fidelity prototype for at least:

1.  Home
2.  About
3.  University Relationship
4.  Business Directory
5.  Business Detail
6.  Services
7.  Service Detail
8.  Projects
9.  Project Detail
10. Training
11. Training Detail
12. News
13. News Detail
14. Careers
15. Downloads
16. FAQs
17. Contact
18. Request a Service
19. Request a Quote

The prototype must include realistic sample content.

Do not use Lorem Ipsum where meaningful sample copy can be written.

------------------------------------------------------------------------

# 11. HOMEPAGE PROTOTYPE

Build the homepage in this sequence:

``` text
Header
↓
Hero
↓
Institutional trust/ownership section
↓
Business portfolio
↓
Featured services
↓
Why MOAUM
↓
Impact/statistics
↓
Featured projects
↓
Training spotlight
↓
News
↓
CTA
↓
Footer
```

The hero should include:

-   Strong headline
-   Short supporting statement
-   Primary CTA
-   Secondary CTA
-   Strong imagery
-   Subtle motion
-   Institutional credibility

Do not invent financial or performance statistics.

Use only confirmed facts such as the number of listed business areas and
approved ownership information.

------------------------------------------------------------------------

# 12. BUSINESS DIRECTORY

Build a reusable business directory.

Each card should contain:

-   Image/icon
-   Name
-   Short description
-   Status
-   CTA

Business detail pages must use a reusable template.

The content should come from the database after implementation.

Do not hard-code individual business pages.

------------------------------------------------------------------------

# 13. CUSTOMER CONVERSION FLOWS

Implement prototype flows for:

## Flow A --- Service Request

``` text
Visitor
 ↓
Business Division
 ↓
Service
 ↓
Requirements
 ↓
Contact Details
 ↓
Attachment
 ↓
Consent
 ↓
Confirmation
```

## Flow B --- Quote Request

``` text
Visitor
 ↓
Service
 ↓
Project Details
 ↓
Contact
 ↓
Attachment
 ↓
Submit
 ↓
Confirmation
```

## Flow C --- Contact

``` text
Visitor
 ↓
Contact Form
 ↓
Validation
 ↓
Submission
 ↓
Confirmation
```

------------------------------------------------------------------------

# 14. ADMIN PROTOTYPE

Create a separate authenticated admin interface.

Minimum pages:

``` text
Admin Login
Dashboard
Business Divisions
Services
Projects
Training
News
Pages
Media
Downloads
FAQs
Careers
Enquiries
Service Requests
Quote Requests
Users
Roles & Permissions
Audit Logs
Settings
```

Dashboard should contain:

-   KPI cards
-   Enquiry trend
-   Requests by business division
-   Recent enquiries
-   Recent activity
-   Quick actions

------------------------------------------------------------------------

# 15. PROTOTYPE INTERACTION REQUIREMENTS

The prototype must demonstrate:

-   Navigation
-   Mobile menu
-   Mega menu
-   Search interaction
-   Filtering
-   Form states
-   Modal states
-   Toast notifications
-   Dropdowns
-   Tabs where appropriate
-   Pagination
-   Empty states
-   Loading states
-   Error states
-   Success states

The prototype should demonstrate how the actual application will behave.

------------------------------------------------------------------------

# 16. RESPONSIVE PROTOTYPE

Test every major screen at:

``` text
Mobile
Tablet
Laptop
Desktop
Large Desktop
```

Do not merely shrink desktop layouts.

Reflow:

-   Navigation
-   Cards
-   Tables
-   Forms
-   Hero sections
-   Images
-   Dashboard widgets

Tables should become responsive cards or horizontally scrollable layouts
where appropriate.

------------------------------------------------------------------------

# 17. ACCESSIBILITY

Build accessibility into the prototype and code.

Requirements:

-   Semantic HTML
-   Proper labels
-   Keyboard navigation
-   Focus states
-   Accessible buttons
-   Accessible modals
-   Alt text
-   Logical headings
-   Sufficient contrast
-   Reduced-motion support

Do not rely on colour alone to communicate state.

------------------------------------------------------------------------

# 18. PROTOTYPE APPROVAL GATE

Before production implementation, verify:

``` text
[ ] Information architecture approved
[ ] Homepage approved
[ ] Business directory approved
[ ] Business detail approved
[ ] Service pages approved
[ ] Project pages approved
[ ] Training pages approved
[ ] News pages approved
[ ] Contact flow approved
[ ] Service request flow approved
[ ] Quote request flow approved
[ ] Admin dashboard approved
[ ] Admin CRUD screens approved
[ ] Mobile layouts approved
[ ] Design tokens approved
```

Do not proceed to full implementation if major navigation or layout
issues remain unresolved.

------------------------------------------------------------------------

# 19. PHASE 4 --- LARAVEL FOUNDATION

After prototype approval:

Create Laravel 13 application.

Configure:

``` text
Environment
Database
Mail
Storage
Queue
Cache
Logging
Authentication
Authorization
```

Establish:

``` text
.env
.env.example
```

Never commit secrets.

------------------------------------------------------------------------

# 20. DATABASE-FIRST IMPLEMENTATION

Create migrations before business logic.

Start with:

``` text
users
roles
permissions
business_divisions
services
service_categories
projects
project_images
pages
news
news_categories
training_programmes
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
audit_logs
settings
```

Add indexes to:

-   slugs
-   statuses
-   foreign keys
-   publication dates
-   timestamps used for sorting/filtering

Use foreign key constraints intentionally.

Do not use cascading deletes on records where historical records must be
retained.

------------------------------------------------------------------------

# 21. ELOQUENT MODELS

Create relationships carefully.

Example:

``` text
BusinessDivision
  hasMany Services
  hasMany Projects
  hasMany TrainingProgrammes
  hasMany Enquiries

Service
  belongsTo BusinessDivision
  hasMany ServiceRequests
  hasMany QuoteRequests

Project
  belongsTo BusinessDivision
  hasMany ProjectImages
```

Use model casts/enums where useful.

Do not place complex business logic inside controllers.

------------------------------------------------------------------------

# 22. ARCHITECTURE RULE

Controllers should remain thin.

Preferred flow:

``` text
Route
 ↓
Controller
 ↓
Form Request
 ↓
Action / Service
 ↓
Model / Repository where justified
 ↓
Database
 ↓
Resource / View
```

Do not introduce repositories merely for ceremony. Use them only when
they provide genuine abstraction value.

------------------------------------------------------------------------

# 23. FORM REQUESTS

Every non-trivial form must have server-side validation.

Examples:

``` text
StoreEnquiryRequest
StoreServiceRequest
StoreQuoteRequest
StoreBusinessDivisionRequest
StoreServiceRequest
StoreProjectRequest
StoreTrainingRequest
```

Never rely only on browser-side validation.

------------------------------------------------------------------------

# 24. AUTHORIZATION

Use Policies/Gates and permission checks.

Do not implement authorization like:

``` php
if ($user->role === 'admin')
```

Prefer explicit permissions such as:

``` text
can('edit-services')
can('publish-news')
can('manage-users')
```

------------------------------------------------------------------------

# 25. CMS IMPLEMENTATION

All major corporate content must be database-driven.

Do not hard-code:

-   Business divisions
-   Services
-   Projects
-   News
-   Training
-   FAQs
-   Downloads

The administrator must be able to manage these records.

------------------------------------------------------------------------

# 26. PUBLIC ROUTING

Implement clean URLs:

``` text
/
about
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

Use route model binding and slug uniqueness.

------------------------------------------------------------------------

# 27. ADMIN ROUTING

Use an authenticated/policy-protected namespace:

``` text
/admin
/admin/dashboard
/admin/businesses
/admin/services
/admin/projects
/admin/training
/admin/news
/admin/pages
/admin/media
/admin/enquiries
/admin/requests
/admin/users
/admin/roles
/admin/audit-logs
/admin/settings
```

------------------------------------------------------------------------

# 28. MEDIA HANDLING

Implement secure file uploads.

Requirements:

-   MIME validation
-   Extension validation
-   Size validation
-   Safe filenames
-   Storage abstraction
-   Image optimization where practical
-   Access control for private files

Do not expose sensitive uploaded documents through predictable URLs.

------------------------------------------------------------------------

# 29. NOTIFICATIONS

Use Laravel Notifications.

Queue email notifications where appropriate.

Example events:

``` text
EnquiryCreated
ServiceRequestCreated
QuoteRequestCreated
TrainingRegistrationCreated
```

Keep notification templates maintainable.

------------------------------------------------------------------------

# 30. AUDIT LOGGING

Log important administrative actions:

``` text
Created
Updated
Deleted
Published
Unpublished
Assigned
Status Changed
Permission Changed
Login/Logout where appropriate
```

Include:

-   User
-   Action
-   Model/entity
-   Entity ID
-   Timestamp
-   IP where appropriate
-   Relevant before/after data where appropriate

------------------------------------------------------------------------

# 31. FUTURE PAYMENT ARCHITECTURE

Do not implement payment prematurely.

When required, use a gateway abstraction so the business logic does not
depend directly on one provider.

Example:

``` text
PaymentGatewayInterface
       ├── PaystackGateway
       └── FlutterwaveGateway
```

Always verify payments server-side.

Never mark an order as paid solely because the browser returned to a
success page.

------------------------------------------------------------------------

# 32. TESTING

Implement tests progressively.

## Unit Tests

Test:

-   Business rules
-   Services
-   Actions
-   Helpers

## Feature Tests

Test:

-   Authentication
-   Permissions
-   CRUD
-   Forms
-   Public pages
-   Service requests
-   Quote requests

## Browser/E2E Testing

Where tooling is available, test:

-   Navigation
-   Responsive layouts
-   Form submission
-   Admin workflows

------------------------------------------------------------------------

# 33. SECURITY TESTING

Verify:

-   Unauthorized access
-   IDOR prevention
-   CSRF
-   XSS
-   File upload abuse
-   Rate limiting
-   Authentication brute-force protection
-   Mass assignment
-   Authorization bypass
-   Sensitive document exposure

Never expose:

-   `.env`
-   credentials
-   private storage
-   database dumps
-   debug traces in production

------------------------------------------------------------------------

# 34. SEO IMPLEMENTATION

Every public content model should support:

``` text
slug
seo_title
seo_description
og_title
og_description
og_image
canonical_url
```

Generate:

-   sitemap
-   robots.txt
-   metadata
-   semantic headings
-   canonical links

------------------------------------------------------------------------

# 35. PERFORMANCE

Before production:

-   Optimize images
-   Minimize unnecessary JS
-   Optimize CSS
-   Add indexes
-   Avoid N+1 queries
-   Use eager loading
-   Paginate lists
-   Cache stable data where appropriate
-   Queue emails
-   Use production asset builds

------------------------------------------------------------------------

# 36. CONTENT STRATEGY

Do not invent official company claims.

Use placeholders explicitly marked:

``` text
[CLIENT TO PROVIDE]
```

Examples:

-   Official mission
-   Official vision
-   Leadership names
-   Address
-   Phone
-   Email
-   Project data
-   Pricing
-   Legal wording
-   Business status

Do not fabricate testimonials, statistics, clients or certifications.

------------------------------------------------------------------------

# 37. BUSINESS STATUS

Every business division should support status:

``` text
ACTIVE
PLANNED
COMING_SOON
SUSPENDED
ARCHIVED
```

If Transportation & Logistics is still proposed, display it as
planned/coming soon rather than implying current operational capacity.

------------------------------------------------------------------------

# 38. SENSITIVE MODULE RULE

Psychological and drug-testing services may involve sensitive
information.

If future online testing/assessment functionality is requested:

-   Separate sensitive records from public content.
-   Apply strict permissions.
-   Minimize collected data.
-   Encrypt/protect sensitive data where appropriate.
-   Keep results inaccessible to normal content administrators.
-   Log access.
-   Define retention policies.
-   Obtain client/legal approval before implementation.

------------------------------------------------------------------------

# 39. CODE QUALITY RULES

Follow:

-   PSR standards
-   Laravel conventions
-   SOLID principles where useful
-   DRY
-   Clear naming
-   Small methods
-   Reusable Blade components
-   Form Requests
-   Policies
-   Enums
-   Service/Action classes for complex workflows

Do not over-abstract simple CRUD.

------------------------------------------------------------------------

# 40. UI IMPLEMENTATION RULE

Translate the approved prototype into reusable components.

For example:

``` text
x-button
x-card
x-section-heading
x-badge
x-input
x-select
x-textarea
x-modal
x-alert
x-breadcrumbs
x-pagination
x-business-card
x-service-card
x-project-card
x-news-card
```

Keep component APIs predictable.

------------------------------------------------------------------------

# 41. TAILWIND RULES

Use the design tokens:

``` text
moaum-red
moaum-blue
moaum-green
moaum-charcoal
```

Use neutral colours for most surfaces.

Avoid random colour values throughout templates.

Prefer design tokens and reusable component classes.

------------------------------------------------------------------------

# 42. PUBLIC UI QUALITY BAR

The website should feel like a professionally designed enterprise
platform.

It must not look like:

-   A generic Tailwind starter
-   A copied template
-   A basic CRUD application
-   A university department microsite
-   A collection of unrelated business pages

It should feel like one coherent commercial brand.

------------------------------------------------------------------------

# 43. ADMIN UI QUALITY BAR

The admin interface should prioritize:

-   Speed
-   Clarity
-   Data density
-   Search
-   Filtering
-   Bulk actions where appropriate
-   Clear statuses
-   Consistent forms
-   Consistent tables
-   Confirmation dialogs
-   Empty states

------------------------------------------------------------------------

# 44. IMPLEMENTATION CHECKPOINTS

After every major module:

1.  Compare implementation against prototype.
2.  Check desktop.
3.  Check mobile.
4.  Check accessibility.
5.  Check validation.
6.  Check authorization.
7.  Check database relationships.
8.  Check error states.
9.  Add/update tests.
10. Refactor before proceeding.

------------------------------------------------------------------------

# 45. GIT WORKFLOW

Use feature branches.

Suggested:

``` text
main
develop
feature/prototype-home
feature/cms-businesses
feature/cms-services
feature/cms-projects
feature/training
feature/enquiries
feature/admin-dashboard
```

Commit messages should be meaningful.

Examples:

``` text
feat: add business division management
feat: add service request workflow
fix: prevent unauthorized project updates
refactor: extract enquiry notification service
test: add quote request validation coverage
```

------------------------------------------------------------------------

# 46. DEVELOPMENT ORDER

Implement in this order:

``` text
1. Project setup
2. Design system
3. Layout/components
4. Authentication
5. RBAC
6. Database foundation
7. Business divisions
8. Services
9. Projects
10. News
11. Training
12. Pages
13. Media
14. Downloads
15. FAQs
16. Careers
17. Enquiries
18. Service requests
19. Quote requests
20. Notifications
21. Audit logs
22. SEO
23. Testing
24. Performance
25. Security hardening
26. Deployment
```

------------------------------------------------------------------------

# 47. DO NOT DO THESE THINGS

Do not:

1.  Start coding before reviewing requirements.
2.  Build all 16 business operations in the MVP.
3.  Hard-code business divisions into templates.
4.  Invent client information.
5.  Invent prices.
6.  Invent statistics.
7.  Invent certifications.
8.  Invent customer testimonials.
9.  Trust client-side validation.
10. Trust payment redirects.
11. Put authorization only in the UI.
12. Expose sensitive documents.
13. Put secrets in Git.
14. Build unnecessarily complex architecture.
15. Use excessive animations.
16. Make every section red/blue/green.
17. Treat the prototype as disposable.

------------------------------------------------------------------------

# 48. REQUIRED DELIVERABLES

At prototype stage produce:

``` text
01-information-architecture.md
02-requirements-matrix.md
03-design-system.md
04-user-flows.md
05-wireframes.md
06-prototype-screen-list.md
07-open-questions.md
```

At implementation stage produce:

``` text
08-architecture.md
09-database-schema.md
10-api-contract.md
11-rbac-matrix.md
12-testing-strategy.md
13-deployment-guide.md
14-maintenance-guide.md
```

------------------------------------------------------------------------

# 49. MASTER EXECUTION INSTRUCTION

Work as a senior product team rather than a code generator.

Your priorities are:

``` text
Correctness
↓
Clarity
↓
User experience
↓
Maintainability
↓
Security
↓
Performance
↓
Visual polish
```

When a requirement is ambiguous:

1.  Identify the ambiguity.
2.  State the assumption.
3.  Ask for clarification if it materially affects architecture.
4.  Otherwise implement the least risky reversible interpretation.

When a future requirement is likely:

-   Create an extensible boundary.
-   Do not prematurely implement the entire future feature.

When a design decision is made:

-   Capture it in documentation.
-   Keep implementation consistent with it.

When implementing a module:

``` text
Requirements
→ Database
→ Model
→ Policy
→ Request validation
→ Action/Service
→ Controller
→ Route
→ View/component
→ Notification
→ Tests
```

Do not skip security, validation or authorization because the feature
appears simple.

------------------------------------------------------------------------

# 50. FINAL SUCCESS CRITERIA

The completed MVP must:

-   Present MOAUM as one coherent diversified enterprise.
-   Clearly communicate the University's relationship with the company.
-   Showcase the 16 business areas.
-   Provide modern, responsive UX.
-   Generate service and quote enquiries.
-   Provide a functional CMS.
-   Provide secure role-based administration.
-   Be database-driven.
-   Be SEO-ready.
-   Be accessible.
-   Be maintainable.
-   Be scalable toward future business modules.
-   Match the approved prototype.
-   Use the logo-derived design system consistently.

The final implementation must look and behave like a custom-built
corporate platform, not a generic Laravel starter project.
