<?php

namespace Tests\Feature;

use App\Enums\DivisionStatus;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\JobOpening;
use App\Models\NewsArticle;
use App\Models\Project;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** M11 — SEO metadata, sitemap, robots, feed and search (PRD §34/§37). */
class SeoAndSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_have_canonical_and_open_graph_tags(): void
    {
        $article = NewsArticle::factory()->create(['title' => 'Harvest season update', 'featured_image' => '/storage/media/harvest.webp']);

        $this->get(route('news.show', $article).'?utm_source=x')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('news.show', $article).'">', escape: false)
            ->assertSee('<meta property="og:type" content="article">', escape: false)
            ->assertSee('content="'.url('/storage/media/harvest.webp').'"', escape: false)
            ->assertSee('"@type":"NewsArticle"', escape: false);
    }

    public function test_canonical_keeps_meaningful_filters_only(): void
    {
        $this->get(route('news.index', ['category' => 'events', 'utm_campaign' => 'x', 'page' => 1]))
            ->assertSee('<link rel="canonical" href="'.route('news.index').'?category=events">', escape: false);
    }

    public function test_home_has_organization_structured_data(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('"@type":"Organization"', escape: false);
    }

    public function test_job_pages_have_job_posting_structured_data(): void
    {
        $job = JobOpening::factory()->create();

        $this->get(route('careers.show', $job))->assertOk()->assertSee('"@type":"JobPosting"', escape: false);
    }

    public function test_sitemap_lists_public_content_only(): void
    {
        $division = BusinessDivision::factory()->create(['status' => DivisionStatus::Active]);
        $hidden = BusinessDivision::factory()->create(['status' => DivisionStatus::Archived]);
        $project = Project::factory()->create();
        $draft = Project::factory()->unpublished()->create();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('businesses.show', $division), escape: false)
            ->assertSee(route('projects.show', $project), escape: false)
            ->assertDontSee(route('businesses.show', $hidden), escape: false)
            ->assertDontSee(route('projects.show', $draft), escape: false);
    }

    public function test_robots_blocks_everything_outside_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');

        $this->app['env'] = 'production';
        $this->get('/robots.txt')->assertSee('Disallow: /admin')->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_news_feed_is_valid_rss(): void
    {
        NewsArticle::factory()->create(['title' => 'Feed story']);
        NewsArticle::factory()->draft()->create(['title' => 'Unpublished story']);

        $response = $this->get(route('news.feed'))->assertOk()->assertSee('Feed story')->assertDontSee('Unpublished story');

        $this->assertNotFalse(simplexml_load_string($response->getContent()));
    }

    public function test_site_search_finds_published_content_only(): void
    {
        BusinessDivision::factory()->create(['name' => 'Solar Fumigation Works', 'status' => DivisionStatus::Active]);
        Project::factory()->create(['title' => 'Fumigation of the library']);
        Project::factory()->unpublished()->create(['title' => 'Secret fumigation plan']);
        NewsArticle::factory()->draft()->create(['title' => 'Fumigation draft news']);

        $this->get(route('search', ['q' => 'fumigation']))
            ->assertOk()
            ->assertSee('Solar Fumigation Works')
            ->assertSee('Fumigation of the library')
            ->assertDontSee('Secret fumigation plan')
            ->assertDontSee('Fumigation draft news');
    }

    public function test_short_search_terms_return_no_results(): void
    {
        $this->get(route('search', ['q' => 'a']))->assertOk()->assertSee('at least two characters');
    }

    public function test_admin_search_respects_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $enquiry = Enquiry::factory()->create(['name' => 'Ifeoma Nwosu']);

        $manager = User::factory()->create();
        $manager->assignRole(Rbac::BUSINESS_MANAGER);
        $this->actingAs($manager->fresh())->get(route('admin.search', ['q' => 'Ifeoma']))
            ->assertOk()->assertSee($enquiry->reference);

        $editor = User::factory()->create();
        $editor->assignRole(Rbac::CONTENT_EDITOR);
        $this->actingAs($editor->fresh())->get(route('admin.search', ['q' => 'Ifeoma']))
            ->assertOk()->assertDontSee($enquiry->reference);
    }

    public function test_mobile_menu_links_about_and_businesses(): void
    {
        $this->get(route('home'))
            ->assertSee('href="'.route('about.profile').'"', escape: false)
            ->assertSee('href="'.route('businesses.index').'"', escape: false);
    }
}
