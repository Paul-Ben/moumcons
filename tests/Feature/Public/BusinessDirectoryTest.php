<?php

namespace Tests\Feature\Public;

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
        $visible = $this->division(['status' => \App\Enums\DivisionStatus::Active, 'name' => 'Alpha Division']);
        $hidden = $this->division(['status' => \App\Enums\DivisionStatus::Archived, 'name' => 'Secret Division']);

        $response = $this->get(route('businesses.index'));
        $response->assertOk()
            ->assertSee($visible->name)
            ->assertDontSee($hidden->name);
    }

    public function test_directory_search_filters_by_name(): void
    {
        $this->division(['status' => \App\Enums\DivisionStatus::Active, 'name' => 'Printing Press']);
        $this->division(['status' => \App\Enums\DivisionStatus::Active, 'name' => 'Catering Hub']);

        $this->get(route('businesses.index', ['q' => 'printing']))
            ->assertOk()
            ->assertSee('Printing Press')
            ->assertDontSee('Catering Hub');
    }

    public function test_detail_page_renders_by_slug(): void
    {
        $division = $this->division([
            'status' => \App\Enums\DivisionStatus::Active,
            'slug' => 'test-division',
        ]);
        Service::factory()->create([
            'business_division_id' => $division->id,
            'status' => \App\Enums\ServiceStatus::Active,
            'name' => 'Visible Service',
        ]);

        $this->get(route('businesses.show', $division))
            ->assertOk()
            ->assertSee($division->name)
            ->assertSee('Visible Service');
    }

    public function test_draft_division_detail_returns_404(): void
    {
        $division = $this->division(['status' => \App\Enums\DivisionStatus::Archived]);

        $this->get(route('businesses.show', $division))->assertNotFound();
    }
}
