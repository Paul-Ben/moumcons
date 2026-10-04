<?php

namespace Tests\Feature\Admin;

use App\Enums\DivisionStatus;
use App\Enums\PricingType;
use App\Enums\ServiceStatus;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\DivisionCapability;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** M3 — division and service management (PRD §5/§10/§11/§23). */
class CatalogueAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @param  list<string>  $permissions */
    private function staffWith(array $permissions): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $role = Role::create(['name' => 'Test '.implode('-', $permissions), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return $user->fresh();
    }

    private function editor(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        return $user->fresh();
    }

    /* ------------------------------- Divisions ------------------------------ */

    public function test_division_screens_require_view_permission(): void
    {
        $staff = $this->staffWith(['view-enquiries']);

        $this->actingAs($staff)->get(route('admin.divisions.index'))->assertForbidden();
    }

    public function test_editor_creates_a_division_with_capabilities(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.divisions.store'), [
            'name' => 'Solar Energy Services',
            'short_description' => 'Renewable energy installation.',
            'full_description' => '<div>We install <strong>solar</strong><script>alert(1)</script></div>',
            'category' => 'Industry & Infrastructure',
            'status' => 'active',
            'featured' => '1',
            'icon' => 'zap',
            'cover_image' => '/storage/media/2026/10/solar.webp',
            'capabilities' => [
                ['id' => '', 'title' => 'Rooftop arrays', 'description' => 'Homes and offices'],
                ['id' => '', 'title' => 'Mini-grids', 'description' => ''],
                ['id' => '', 'title' => '', 'description' => ''],
            ],
        ])->assertRedirect();

        $division = BusinessDivision::where('name', 'Solar Energy Services')->sole();
        $this->assertSame('solar-energy-services', $division->slug);
        $this->assertSame(DivisionStatus::Active, $division->status);
        $this->assertStringContainsString('<strong>solar</strong>', $division->full_description);
        $this->assertStringNotContainsString('script', $division->full_description);
        $this->assertSame(['Rooftop arrays', 'Mini-grids'], $division->capabilities->pluck('title')->all());
        $this->assertTrue(AuditLog::query()->where('action', 'business_division.created')->exists());
    }

    public function test_capabilities_are_synced_in_place(): void
    {
        $editor = $this->editor();
        $division = BusinessDivision::factory()->create();
        $keep = DivisionCapability::factory()->create(['business_division_id' => $division->id, 'title' => 'Old title']);
        $drop = DivisionCapability::factory()->create(['business_division_id' => $division->id]);

        $this->actingAs($editor)->put(route('admin.divisions.update', $division), [
            'name' => $division->name,
            'status' => $division->status->value,
            'capabilities' => [
                ['id' => '', 'title' => 'Brand new'],
                ['id' => $keep->id, 'title' => 'Renamed'],
            ],
        ])->assertRedirect(route('admin.divisions.edit', $division));

        $this->assertSame('Renamed', $keep->fresh()->title);
        $this->assertSame(2, $keep->fresh()->sort_order);
        $this->assertNull($drop->fresh());
        $this->assertSame(['Brand new', 'Renamed'], $division->capabilities()->pluck('title')->all());
    }

    public function test_status_change_requires_publish_permission(): void
    {
        $staff = $this->staffWith(['view-divisions', 'edit-divisions']);
        $division = BusinessDivision::factory()->create(['status' => DivisionStatus::Planned]);

        $this->actingAs($staff)->put(route('admin.divisions.update', $division), [
            'name' => $division->name, 'status' => 'active',
        ])->assertForbidden();

        $this->actingAs($staff)->put(route('admin.divisions.update', $division), [
            'name' => 'Renamed division',
        ])->assertRedirect();

        $this->assertSame('Renamed division', $division->fresh()->name);
        $this->assertSame(DivisionStatus::Planned, $division->fresh()->status);
    }

    public function test_business_manager_cannot_create_divisions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole(Rbac::BUSINESS_MANAGER);

        $this->actingAs($manager)->get(route('admin.divisions.create'))->assertForbidden();
    }

    public function test_invalid_image_urls_are_rejected(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.divisions.store'), [
            'name' => 'X', 'status' => 'active', 'hero_image' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('hero_image');
    }

    public function test_division_in_use_cannot_be_deleted(): void
    {
        $admin = $this->staffWith(['view-divisions', 'delete-divisions']);
        $division = BusinessDivision::factory()->create();
        ServiceRequest::factory()->create(['business_division_id' => $division->id]);

        $this->actingAs($admin)->delete(route('admin.divisions.destroy', $division))->assertSessionHas('error');
        $this->assertNotNull($division->fresh());

        $empty = BusinessDivision::factory()->create();
        $this->actingAs($admin)->delete(route('admin.divisions.destroy', $empty))->assertRedirect(route('admin.divisions.index'));
        $this->assertNull($empty->fresh());
    }

    public function test_public_page_renders_rich_overview_safely(): void
    {
        $division = BusinessDivision::factory()->create([
            'status' => DivisionStatus::Active,
            'full_description' => '<div>Trusted <em>partner</em></div>',
        ]);

        $this->get(route('businesses.show', $division))
            ->assertOk()
            ->assertSee('Trusted <em>partner</em>', escape: false);
    }

    /* -------------------------------- Services ------------------------------ */

    public function test_editor_creates_a_service_and_hidden_pricing_drops_the_price(): void
    {
        $editor = $this->editor();
        $division = BusinessDivision::factory()->create();

        $this->actingAs($editor)->post(route('admin.services.store'), [
            'business_division_id' => $division->id,
            'name' => 'Office Fumigation',
            'pricing_type' => 'contact_us',
            'starting_price' => '50000',
            'status' => 'active',
        ])->assertRedirect();

        $service = Service::where('name', 'Office Fumigation')->sole();
        $this->assertSame(PricingType::ContactUs, $service->pricing_type);
        $this->assertNull($service->starting_price);
        $this->assertSame('office-fumigation', $service->slug);
    }

    public function test_fixed_pricing_requires_a_price(): void
    {
        $editor = $this->editor();
        $division = BusinessDivision::factory()->create();

        $this->actingAs($editor)->post(route('admin.services.store'), [
            'business_division_id' => $division->id,
            'name' => 'Printing',
            'pricing_type' => 'fixed',
        ])->assertSessionHasErrors('starting_price');
    }

    public function test_duplicate_names_get_unique_slugs(): void
    {
        $division = BusinessDivision::factory()->create();
        $a = Service::factory()->create(['name' => 'Consulting', 'slug' => null, 'business_division_id' => $division->id]);
        $b = Service::factory()->create(['name' => 'Consulting', 'slug' => null, 'business_division_id' => $division->id]);

        $this->assertSame('consulting', $a->slug);
        $this->assertSame('consulting-2', $b->slug);
    }

    public function test_service_without_publish_permission_starts_as_draft(): void
    {
        $staff = $this->staffWith(['view-services', 'create-services']);
        $division = BusinessDivision::factory()->create();

        $this->actingAs($staff)->post(route('admin.services.store'), [
            'business_division_id' => $division->id,
            'name' => 'Catering',
            'pricing_type' => 'quote_required',
        ])->assertRedirect();

        $this->assertSame(ServiceStatus::Draft, Service::where('name', 'Catering')->sole()->status);
    }

    public function test_service_index_filters_by_division(): void
    {
        $editor = $this->editor();
        $a = BusinessDivision::factory()->create();
        $b = BusinessDivision::factory()->create();
        Service::factory()->create(['business_division_id' => $a->id, 'name' => 'Alpha service']);
        Service::factory()->create(['business_division_id' => $b->id, 'name' => 'Beta service']);

        $this->actingAs($editor)->get(route('admin.services.index', ['division' => $a->id]))
            ->assertOk()->assertSee('Alpha service')->assertDontSee('Beta service');
    }

    public function test_all_catalogue_screens_render(): void
    {
        $editor = $this->editor();
        $division = BusinessDivision::factory()->create();
        DivisionCapability::factory()->create(['business_division_id' => $division->id, 'title' => 'Shown capability']);
        $service = Service::factory()->create(['business_division_id' => $division->id]);
        ServiceCategory::factory()->create();

        $this->actingAs($editor);
        $this->get(route('admin.divisions.index'))->assertOk()->assertSee($division->name);
        $this->get(route('admin.divisions.create'))->assertOk();
        $this->get(route('admin.divisions.edit', $division))->assertOk()->assertSee('Shown capability');
        $this->get(route('admin.services.index'))->assertOk()->assertSee($service->name);
        $this->get(route('admin.services.create'))->assertOk();
        $this->get(route('admin.services.edit', $service))->assertOk();
        $this->get(route('admin.service-categories.index'))->assertOk();
        $this->get(route('admin.media.index'))->assertOk();
    }

    /* ------------------------------ Categories ----------------------------- */

    public function test_categories_can_be_managed(): void
    {
        $admin = $this->staffWith(['view-services', 'create-services', 'edit-services', 'delete-services']);

        $this->actingAs($admin)->post(route('admin.service-categories.store'), ['name' => 'Consulting'])->assertRedirect();
        $category = ServiceCategory::where('name', 'Consulting')->sole();

        $this->actingAs($admin)->put(route('admin.service-categories.update', $category), ['name' => 'Advisory', 'sort_order' => 3])->assertRedirect();
        $this->assertSame('Advisory', $category->fresh()->name);

        $this->actingAs($admin)->delete(route('admin.service-categories.destroy', $category))->assertRedirect();
        $this->assertNull($category->fresh());
    }
}
