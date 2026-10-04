<?php

namespace Tests\Feature\Public;

use App\Enums\DivisionStatus;
use App\Enums\ServiceStatus;
use App\Models\BusinessDivision;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Module 5 — Business Directory (PRD §9). */
class BusinessDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function division(array $attrs = []): BusinessDivision
    {
        return BusinessDivision::factory()->create($attrs);
    }

    public function test_directory_lists_public_divisions(): void
    {
        $visible = $this->division(['status' => DivisionStatus::Active, 'name' => 'Alpha Division']);
        $hidden = $this->division(['status' => DivisionStatus::Archived, 'name' => 'Secret Division']);

        $response = $this->get(route('businesses.index'));
        $response->assertOk()
            ->assertSee($visible->name)
            ->assertDontSee($hidden->name);
    }

    public function test_directory_search_filters_by_name(): void
    {
        $this->division(['status' => DivisionStatus::Active, 'name' => 'Printing Press']);
        $this->division(['status' => DivisionStatus::Active, 'name' => 'Catering Hub']);

        // Assert on the paginated grid rather than the whole page: the site
        // footer deliberately lists divisions independently of the search.
        $response = $this->get(route('businesses.index', ['q' => 'printing']));

        $response->assertOk()->assertSee('Printing Press');
        $this->assertSame(
            ['Printing Press'],
            $response->viewData('divisions')->pluck('name')->all()
        );
    }

    public function test_detail_page_renders_by_slug(): void
    {
        $division = $this->division([
            'status' => DivisionStatus::Active,
            'slug' => 'test-division',
        ]);
        Service::factory()->create([
            'business_division_id' => $division->id,
            'status' => ServiceStatus::Active,
            'name' => 'Visible Service',
        ]);

        $this->get(route('businesses.show', $division))
            ->assertOk()
            ->assertSee($division->name)
            ->assertSee('Visible Service');
    }

    public function test_draft_division_detail_returns_404(): void
    {
        $division = $this->division(['status' => DivisionStatus::Archived]);

        $this->get(route('businesses.show', $division))->assertNotFound();
    }
}
