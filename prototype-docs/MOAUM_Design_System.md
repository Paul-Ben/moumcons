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
