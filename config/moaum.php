<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS URLs in production (PRD §32). Disable only if TLS is
    | terminated somewhere that cannot pass X-Forwarded-Proto.
    |--------------------------------------------------------------------------
    */
    'force_https' => env('FORCE_HTTPS', true),

    /*
    |--------------------------------------------------------------------------
    | Backups (PRD §41) — `php artisan moaum:backup`, scheduled daily.
    | MOAUM_BACKUP_DISK names a filesystems.php disk (e.g. "s3") to copy each
    | archive off the server; leave empty to keep local copies only.
    |--------------------------------------------------------------------------
    */
    'backups' => [
        'disk' => env('MOAUM_BACKUP_DISK'),
        'keep' => (int) env('MOAUM_BACKUP_KEEP', 14),
    ],

    /*
    |--------------------------------------------------------------------------
    | Company details (single source of truth for header/footer/contact)
    |--------------------------------------------------------------------------
    | Values marked CLIENT_TO_PROVIDE are placeholders awaiting client input
    | per the PRD; they will move to the DB Settings table in Module 10.
    */

    'company' => [
        'name' => 'MOAUM Consultancy Services Limited',
        'short_name' => 'MOAUM Consultancy',
        'parent' => 'Rev. Fr. Moses Orshio Adasu University, Makurdi',
        'tagline' => 'Building Enterprise Value Through Diverse Business Solutions',
        'email' => 'info@moaumconsultancy.com',
        'phone' => 'CLIENT_TO_PROVIDE',
        'address' => 'CLIENT_TO_PROVIDE',
        'hours' => 'Mon - Fri: 8:00 AM - 5:00 PM',
        'social' => [
            'facebook' => '#',
            'twitter' => '#',
            'linkedin' => '#',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation structure (mirrors prototype-docs/index.html mega menu)
    |--------------------------------------------------------------------------
    */

    'nav' => [
        'primary' => [
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'About', 'children' => [
                ['label' => 'Company Profile', 'route' => 'about.profile'],
                ['label' => 'Mission & Vision', 'route' => 'about.mission'],
                ['label' => 'Leadership', 'route' => 'about.leadership'],
                ['label' => 'University Relationship', 'route' => 'about.university'],
            ]],
            ['label' => 'Our Businesses', 'mega' => true],
            ['label' => 'Services', 'route' => 'services.index'],
            ['label' => 'Projects', 'route' => 'projects.index'],
            ['label' => 'Training', 'route' => 'training.index'],
            ['label' => 'News', 'route' => 'news.index'],
            ['label' => 'Careers', 'route' => 'careers.index'],
            ['label' => 'Contact', 'route' => 'contact.index'],
        ],

        // Mega-menu groups — Design System §19 grouping of the 16 divisions.
        // 'heading' must match business_divisions.category exactly: the header
        // renders each group by looking up divisions grouped on that column.
        // 'items' documents the expected membership of each group.
        'business_groups' => [
            ['heading' => 'Business & Professional', 'accent' => 'red', 'items' => [
                'Printing & Publishing', 'Cleaning & Fumigation', 'Security & Intelligence', 'Psychological & Drug Testing',
            ]],
            ['heading' => 'Technology & Education', 'accent' => 'blue', 'items' => [
                'AI & Digital Technology', 'Training & Capacity Building', 'Staff School & ICT Secondary School',
            ]],
            ['heading' => 'Commerce & Hospitality', 'accent' => 'green', 'items' => [
                'Super Credit Store', 'Restaurant/Bakery/Catering', 'Property Development',
            ]],
            ['heading' => 'Industry & Infrastructure', 'accent' => 'charcoal', 'items' => [
                'Construction Services', 'Construction Materials', 'Waste Management', 'Mining & Geo-Mining',
            ]],
            ['heading' => 'Agriculture & Logistics', 'accent' => 'green', 'items' => [
                'Agriculture & Farms', 'Transportation',
            ]],
        ],
    ],
];
