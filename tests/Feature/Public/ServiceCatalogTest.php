<?php

namespace Tests\Feature\Public;

use App\Enums\ServiceStatus;
use App\Models\BusinessDivision;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Module 6 — Services Catalogue (PRD §11). */
class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_lists_active_services(): void
    {
        $division = BusinessDivision::factory()->create(['status' => \App\Enums\DivisionStatus::Active]);
        $active = Service::factory()->create(['business_division_id' => $division->id, 'status' => ServiceStatus::Active, 'name' => 'Live Service']);
        Service::factory()->create(['business_division_id' => $division->id, 'status' => ServiceStatus::Draft, 'name' => 'Hidden Service']);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee('Hidden Service');
    }

    public function test_service_detail_page_renders(): void
    {
        $division = BusinessDivision::factory()->create(['status' => \App\Enums\DivisionStatus::Active]);
        $service = Service::factory()->create([
            'business_division_id' => $division->id,
            'status' => ServiceStatus::Active,
            'slug' => 'test-service',
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee($service->name);
    }

    public function test_unpublished_service_returns_404(): void
    {
        $division = BusinessDivision::factory()->create(['status' => \App\Enums\DivisionStatus::Active]);
        $service = Service::factory()->create([
            'business_division_id' => $division->id,
            'status' => ServiceStatus::Draft,
        ]);

        $this->get(route('services.show', $service))->assertNotFound();
    }
}
