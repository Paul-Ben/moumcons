<?php

namespace Database\Seeders;

use App\Enums\DivisionStatus;
use App\Enums\PricingType;
use App\Enums\ServiceStatus;
use App\Models\BusinessDivision;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the 16 business divisions from PRD §5, cross-division service
 * categories (PRD §8), representative services, and site settings so the
 * public pages (Module 4+) render real database-driven content.
 *
 * Idempotent: safe to re-run (upserts by slug/key).
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $divisions = $this->seedDivisions();
        $categories = $this->seedCategories();
        $this->seedServices($divisions, $categories);
    }

    private function seedSettings(): void
    {
        $settings = [
            ['contact.email', 'general', 'info@moaumconsultancy.com'],
            ['contact.hours', 'general', 'Mon - Fri: 8:00 AM - 5:00 PM'],
            ['company.tagline', 'general', 'Building Enterprise Value Through Diverse Business Solutions'],
            ['home.hero.badge', 'homepage', '51% Owned by Rev. Fr. Moses Orshio Adasu University'],
            ['home.hero.title_highlight', 'homepage', 'Diverse'],
            ['home.hero.description', 'homepage', 'MOAUM Consultancy Services Limited is a diversified commercial enterprise spanning 16 business divisions, delivering professional excellence across technology, agriculture, construction, education, and beyond.'],
            ['home.stats', 'homepage', json_encode([
                ['value' => '51%', 'label' => 'University Shareholding', 'color' => 'red'],
                ['value' => '16', 'label' => 'Business Divisions', 'color' => 'blue'],
                ['value' => '∞', 'label' => 'Opportunities for Growth', 'color' => 'green'],
            ])],
            ['home.portfolio.eyebrow', 'homepage', 'Our Business Portfolio'],
            ['home.portfolio.title', 'homepage', 'Diversified Capabilities. One Enterprise Platform.'],
            ['home.portfolio.description', 'homepage', 'Explore our comprehensive range of professional services across multiple sectors'],
            ['home.cta.title', 'homepage', 'Ready to Work With Us?'],
            ['home.cta.description', 'homepage', "Let's discuss how our diverse business capabilities can meet your needs"],
        ];

        // firstOrCreate, not updateOrCreate: re-running db:seed must never
        // overwrite values staff have since edited under Admin → Settings.
        foreach ($settings as [$key, $group, $value]) {
            Setting::firstOrCreate(['key' => $key], [
                'group' => $group,
                'type' => str_starts_with($value, '[') ? 'json' : 'text',
                'value' => $value,
            ]);
        }
    }

    /** @return array<string, BusinessDivision> slug => model */
    private function seedDivisions(): array
    {
        // PRD §5 — all 16 divisions, in order. Featured flags + statuses mirror
        // prototype-docs/index.html (Transportation shown as "Coming Soon").
        $divisions = [
            ['printing-and-publishing-services', 'Printing & Publishing Services', 'active', true, 'Business & Professional', 'Commercial printing, publishing and institutional documentation services.'],
            ['industrial-cleaning-and-fumigation-services', 'Industrial Cleaning & Fumigation', 'active', true, 'Business & Professional', 'Professional cleaning and fumigation services for commercial spaces.'],
            ['private-security-guards-and-intelligence-services', 'Private Security & Intelligence Services', 'active', false, 'Business & Professional', 'Licensed guarding, surveillance and intelligence support for institutions and enterprises.'],
            ['ai-and-digital-technology-training-centre', 'AI & Digital Technology Training', 'active', true, 'Technology & Education', 'Cutting-edge training in artificial intelligence and digital technologies.'],
            ['psychological-and-drug-testing-centre', 'Psychological & Drug Testing Centre', 'planned', false, 'Business & Professional', 'Certified psychological assessment and drug-testing services.'],
            ['consultancy-super-credit-store', 'Consultancy Super Credit Store', 'active', false, 'Commerce & Hospitality', 'Retail and credit-store services for staff, students and the community.'],
            ['restaurant-bakery-and-catering-services', 'Restaurant, Bakery & Catering', 'active', true, 'Commerce & Hospitality', 'Quality food services, catering and bakery products.'],
            ['training-and-capacity-building-services', 'Training & Capacity Building', 'active', false, 'Technology & Education', 'Professional development, workshops and organisational capacity programmes.'],
            ['agriculture-and-university-farms', 'Agriculture & University Farms', 'active', false, 'Agriculture & Logistics', 'Crop production, livestock and farm-produce supply from university farms.'],
            ['block-and-construction-materials-industry', 'Block & Construction Materials', 'active', false, 'Industry & Infrastructure', 'Manufacturing and supply of blocks and quality construction materials.'],
            ['construction-services', 'Construction Services', 'active', false, 'Industry & Infrastructure', 'Building, civil works and project construction services.'],
            ['waste-management-and-environmental-services', 'Waste Management & Environmental Services', 'active', false, 'Industry & Infrastructure', 'Waste collection, recycling and environmental sanitation solutions.'],
            ['mining-and-geo-mining-services', 'Mining & Geo-Mining Services', 'planned', false, 'Industry & Infrastructure', 'Mineral exploration and geoscience consultancy services.'],
            ['staff-school-and-ict-secondary-school', 'Staff School & ICT Secondary School', 'active', false, 'Technology & Education', 'High-quality secondary education with an ICT emphasis.'],
            ['hostel-and-property-development', 'Hostel & Property Development', 'planned', false, 'Commerce & Hospitality', 'Student housing and commercial property development.'],
            ['transportation-and-logistics-services', 'Transportation & Logistics', 'coming_soon', false, 'Agriculture & Logistics', 'Comprehensive logistics and haulage services.'],
        ];

        // Placeholder photography in public/images — used so cards and detail
        // heroes render an image instead of the blank icon block. Drop a file
        // with the same name in to override; remove the entry once the client
        // supplies final photography.
        $images = [
            'printing-and-publishing-services' => 'division-printing-publishing.jpg',
            'industrial-cleaning-and-fumigation-services' => 'division-cleaning-fumigation.jpg',
            'private-security-guards-and-intelligence-services' => 'division-security-intelligence.jpg',
            'ai-and-digital-technology-training-centre' => 'division-ai-digital-technology.jpg',
            'consultancy-super-credit-store' => 'division-super-credit-store.jpg',
            'restaurant-bakery-and-catering-services' => 'division-restaurant-bakery-catering.jpg',
            'training-and-capacity-building-services' => 'division-training-capacity-building.jpg',
        ];

        $out = [];
        foreach ($divisions as $i => [$slug, $name, $status, $featured, $category, $short]) {
            $image = isset($images[$slug]) && file_exists(public_path('images/'.$images[$slug]))
                ? '/images/'.$images[$slug]
                : null;

            $out[$slug] = BusinessDivision::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'short_description' => $short,
                    'full_description' => $short.' As a division of '.config('moaum.company.name')
                        .', we combine institutional credibility with commercial agility to deliver measurable value.',
                    'category' => $category,
                    'status' => DivisionStatus::from($status)->value,
                    'featured' => $featured,
                    'icon' => 'building',
                    'cover_image' => $image,
                    'hero_image' => $image,
                    'sort_order' => $i + 1,
                    'seo_title' => $name.' | '.config('moaum.company.short_name'),
                    'seo_description' => $short,
                    'published_at' => now(),
                ],
            );
        }

        return $out;
    }

    /** @return array<string, ServiceCategory> slug => model */
    private function seedCategories(): array
    {
        $categories = [
            ['professional-services', 'Professional Services', 'Consultancy, advisory and expert services across sectors.'],
            ['training-and-development', 'Training & Development', 'Courses, workshops and capacity-building programmes.'],
            ['supply-and-procurement', 'Supply & Procurement', 'Goods supply, sourcing and procurement support.'],
            ['operations-and-maintenance', 'Operations & Maintenance', 'Facility, equipment and ongoing operational support.'],
        ];

        $out = [];
        foreach ($categories as $i => [$slug, $name, $description]) {
            $out[$slug] = ServiceCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'sort_order' => $i + 1],
            );
        }

        return $out;
    }

    private function seedServices(array $divisions, array $categories): void
    {
        $services = [
            // [division slug, category slug, name, short, pricing type, featured]
            ['printing-and-publishing-services', 'professional-services', 'Institutional Document Printing', 'Letterheads, reports, certificates and bulk document printing.', 'quote_required', true],
            ['printing-and-publishing-services', 'professional-services', 'Publishing & Editorial Support', 'Book, journal and magazine production with editorial assistance.', 'starting_from', false],
            ['industrial-cleaning-and-fumigation-services', 'operations-and-maintenance', 'Contract Facility Cleaning', 'Scheduled commercial and institutional cleaning contracts.', 'quote_required', true],
            ['industrial-cleaning-and-fumigation-services', 'operations-and-maintenance', 'Fumigation & Pest Control', 'Safe, certified fumigation for offices, stores and hostels.', 'fixed', false],
            ['ai-and-digital-technology-training-centre', 'training-and-development', 'AI Fundamentals for Professionals', 'Practical artificial-intelligence workshop for teams and individuals.', 'fixed', true],
            ['ai-and-digital-technology-training-centre', 'training-and-development', 'Digital Skills Bootcamp', 'Intensive programme covering productivity tools, data and web basics.', 'starting_from', false],
            ['restaurant-bakery-and-catering-services', 'supply-and-procurement', 'Event & Conference Catering', 'Full-service catering for corporate and university events.', 'quote_required', true],
            ['restaurant-bakery-and-catering-services', 'supply-and-procurement', 'Fresh Bakery Products', 'Daily bread, pastries and custom celebration cakes.', 'fixed', false],
            ['training-and-capacity-building-services', 'training-and-development', 'Corporate Capacity Assessment & Training', 'Tailored staff development pathways for organisations.', 'quote_required', false],
            ['agriculture-and-university-farms', 'supply-and-procurement', 'Farm Produce Supply', 'Bulk supply of grains, produce and livestock from university farms.', 'quote_required', false],
            ['construction-services', 'professional-services', 'Building & Civil Works Contracting', 'End-to-end construction delivery for institutional projects.', 'quote_required', false],
            ['waste-management-and-environmental-services', 'operations-and-maintenance', 'Waste Collection & Recycling', 'Planned collection, segregation and recycling programmes.', 'starting_from', false],
        ];

        foreach ($services as $i => [$division, $category, $name, $short, $pricing, $featured]) {
            Service::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'business_division_id' => $divisions[$division]->id,
                    'service_category_id' => $categories[$category]->id,
                    'name' => $name,
                    'short_description' => $short,
                    'description' => $short,
                    'service_type' => $category,
                    'pricing_type' => PricingType::from($pricing)->value,
                    'featured' => $featured,
                    'status' => ServiceStatus::Active->value,
                    'sort_order' => $i + 1,
                ],
            );
        }
    }
}
