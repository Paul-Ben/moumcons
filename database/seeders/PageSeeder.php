<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * PRD §9 — creates the system pages if they are missing. Idempotent: an
 * existing page is never overwritten, so editors' changes survive re-seeding.
 *
 * Copy is limited to what the PRD states. Anything the client must confirm
 * (legal wording of ownership, mission, values, policies — §9, §33, §45) is
 * marked CLIENT_TO_PROVIDE so it is easy to find and replace in the CMS.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $company = config('moaum.company.name');
        $university = config('moaum.company.parent');

        $pages = [
            'about' => [
                'summary' => "{$company} is the official business and investment arm of {$university}.",
                'content' => <<<HTML
                    <h2>Who we are</h2>
                    <div>{$company} is the official business and investment arm of {$university}. We operate as a diversified commercial enterprise spanning professional services, technology, education, agriculture, construction, environmental services, hospitality, retail, property, security, printing and publishing, mining and logistics.</div>
                    <h2>Our purpose</h2>
                    <div>To present a credible, modern and scalable enterprise that makes our services easy to discover, creates clear channels for business, and supports the University's institutional development.</div>
                    <h2>History</h2>
                    <div>CLIENT_TO_PROVIDE — company history and founding details.</div>
                    <h2>Strategic direction</h2>
                    <div>CLIENT_TO_PROVIDE — strategic priorities for the coming years.</div>
                    HTML,
            ],
            'mission-vision' => [
                'summary' => 'What drives MOAUM Consultancy Services and the values behind our work.',
                'content' => <<<'HTML'
                    <h2>Vision</h2>
                    <div>To establish a credible, modern and scalable enterprise that presents MOAUM as a diversified commercial organisation, makes its services easy to access, and creates lasting social and economic impact.</div>
                    <h2>Mission</h2>
                    <div>CLIENT_TO_PROVIDE — approved mission statement.</div>
                    <h2>Core values</h2>
                    <ul><li>Institutional credibility</li><li>Professionalism</li><li>Reliability</li><li>Innovation</li><li>Social and economic impact</li></ul>
                    HTML,
            ],
            'leadership' => [
                'summary' => 'The people guiding MOAUM Consultancy Services.',
                'content' => '<div>Our leadership team brings together institutional oversight and commercial experience to guide the company and its business divisions.</div>',
            ],
            'university-relationship' => [
                'summary' => "Our relationship with {$university}.",
                'content' => <<<HTML
                    <div>{$company} is the business and investment arm of {$university}, which holds a majority shareholding in the company.</div>
                    <div>CLIENT_TO_PROVIDE — approved legal wording describing ownership and governance (PRD §9: the exact wording must be approved by the client).</div>
                    <h2>What the relationship means</h2>
                    <ul><li>University-backed governance and accountability</li><li>Commercial activity that supports institutional development</li><li>Opportunities for staff, students and the wider community</li></ul>
                    HTML,
            ],
            'privacy-policy' => [
                'summary' => 'How we handle personal information submitted through this website.',
                'content' => '<div>CLIENT_TO_PROVIDE — this privacy notice is being finalised with MOAUM\'s legal and compliance advisers (PRD §33). It will describe what personal information we collect through our forms, why, how long we keep it and how to contact us about it.</div>',
            ],
            'terms' => [
                'summary' => 'Terms that apply to using this website.',
                'content' => '<div>CLIENT_TO_PROVIDE — terms of use are being finalised with MOAUM\'s legal advisers.</div>',
            ],
        ];

        foreach (Page::SYSTEM as $key => [$title]) {
            Page::firstOrCreate(['key' => $key], [
                'title' => $title,
                'slug' => $key,
                'summary' => $pages[$key]['summary'] ?? null,
                'content' => $pages[$key]['content'] ?? null,
                'status' => PageStatus::Published,
            ]);
        }
    }
}
