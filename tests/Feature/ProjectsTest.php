<?php

namespace Tests\Feature;

use App\Enums\DivisionStatus;
use App\Enums\ProjectStatus;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\Project;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** M4 — project portfolio: admin CRUD, publication, public pages (PRD §14). */
class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        return $user->fresh();
    }

    /* --------------------------------- Admin -------------------------------- */

    public function test_admin_screens_require_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole(Rbac::BUSINESS_MANAGER);

        $this->actingAs($manager)->get(route('admin.projects.index'))->assertForbidden();
    }

    public function test_editor_creates_a_published_project_with_gallery_and_tags(): void
    {
        $editor = $this->editor();
        $division = BusinessDivision::factory()->create();

        $this->actingAs($editor)->post(route('admin.projects.store'), [
            'title' => 'Campus Solar Mini-Grid',
            'business_division_id' => $division->id,
            'status' => 'completed',
            'start_date' => '2025-01-10',
            'completion_date' => '2025-06-30',
            'description' => '<div>Delivered a <b>250kW</b> grid<img src="x" onerror="alert(1)"></div>',
            'tags' => 'solar, energy, solar ,',
            'publish' => '1',
            'featured' => '1',
            'gallery' => [
                ['image' => '/storage/media/2026/10/a.webp', 'caption' => 'Panels'],
                ['image' => '/storage/media/2026/10/b.webp', 'caption' => ''],
            ],
            'gallery_submitted' => '1',
        ])->assertRedirect();

        $project = Project::sole();
        $this->assertSame('campus-solar-mini-grid', $project->slug);
        $this->assertSame(['solar', 'energy'], $project->tags);
        $this->assertTrue($project->isPublished());
        $this->assertTrue($project->featured);
        $this->assertStringNotContainsString('onerror', $project->description);
        $this->assertSame(['Panels', null], $project->images->pluck('caption')->all());
        $this->assertTrue(AuditLog::query()->where('action', 'project.created')->exists());
    }

    public function test_completion_before_start_is_rejected(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.projects.store'), [
            'title' => 'Bad dates', 'status' => 'ongoing',
            'start_date' => '2025-06-01', 'completion_date' => '2025-01-01',
        ])->assertSessionHasErrors('completion_date');
    }

    public function test_publishing_requires_publish_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Project writer', 'guard_name' => 'web']);
        $role->givePermissionTo(['view-projects', 'create-projects', 'edit-projects']);
        $user->assignRole($role);

        $this->actingAs($user)->post(route('admin.projects.store'), [
            'title' => 'Sneaky', 'status' => 'ongoing', 'publish' => '1',
        ])->assertForbidden();

        $this->actingAs($user)->post(route('admin.projects.store'), [
            'title' => 'Draft project', 'status' => 'ongoing',
        ])->assertRedirect();

        $this->assertFalse(Project::where('title', 'Draft project')->sole()->isPublished());
    }

    public function test_unticking_publish_unpublishes_and_empty_gallery_clears_images(): void
    {
        $editor = $this->editor();
        $project = Project::factory()->create();
        $project->images()->create(['image' => '/storage/x.webp', 'sort_order' => 1]);

        $this->actingAs($editor)->put(route('admin.projects.update', $project), [
            'title' => $project->title, 'status' => 'ongoing',
            'publish' => '0', 'gallery_submitted' => '1',
        ])->assertRedirect();

        $project->refresh();
        $this->assertNull($project->published_at);
        $this->assertSame(0, $project->images()->count());
    }

    public function test_admin_forms_render(): void
    {
        $editor = $this->editor();
        $project = Project::factory()->create();
        $project->images()->create(['image' => '/storage/x.webp', 'caption' => 'Shown caption', 'sort_order' => 1]);

        $this->actingAs($editor)->get(route('admin.projects.index'))->assertOk()->assertSee($project->title);
        $this->actingAs($editor)->get(route('admin.projects.create'))->assertOk();
        $this->actingAs($editor)->get(route('admin.projects.edit', $project))->assertOk()->assertSee('Shown caption');
    }

    /* -------------------------------- Public -------------------------------- */

    public function test_public_listing_shows_only_published_projects(): void
    {
        Project::factory()->create(['title' => 'Live project']);
        Project::factory()->unpublished()->create(['title' => 'Hidden draft']);
        Project::factory()->create(['title' => 'Future project', 'published_at' => now()->addWeek()]);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Live project')
            ->assertDontSee('Hidden draft')
            ->assertDontSee('Future project');
    }

    public function test_unpublished_project_page_is_404(): void
    {
        $project = Project::factory()->unpublished()->create();

        $this->get(route('projects.show', $project))->assertNotFound();
    }

    public function test_project_page_renders_details_and_gallery(): void
    {
        $division = BusinessDivision::factory()->create(['status' => DivisionStatus::Active]);
        $project = Project::factory()->create([
            'business_division_id' => $division->id,
            'client' => 'Benue State Government',
            'status' => ProjectStatus::Completed,
            'scope' => '<ul><li>Site survey</li></ul>',
        ]);
        $project->images()->create(['image' => '/storage/media/p1.webp', 'caption' => 'Opening day', 'sort_order' => 1]);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Benue State Government')
            ->assertSee('<li>Site survey</li>', escape: false)
            ->assertSee('/storage/media/p1.webp');
    }

    public function test_listing_filters_by_division_slug(): void
    {
        $a = BusinessDivision::factory()->create(['status' => DivisionStatus::Active]);
        $b = BusinessDivision::factory()->create(['status' => DivisionStatus::Active]);
        Project::factory()->create(['business_division_id' => $a->id, 'title' => 'Alpha build']);
        Project::factory()->create(['business_division_id' => $b->id, 'title' => 'Beta build']);

        $this->get(route('projects.index', ['division' => $a->slug]))
            ->assertSee('Alpha build')
            ->assertDontSee('Beta build');
    }

    public function test_featured_projects_appear_on_home_and_division_page(): void
    {
        $division = BusinessDivision::factory()->create(['status' => DivisionStatus::Active]);
        Project::factory()->featured()->create(['business_division_id' => $division->id, 'title' => 'Flagship works']);

        $this->get(route('home'))->assertOk()->assertSee('Featured Projects')->assertSee('Flagship works');
        $this->get(route('businesses.show', $division))->assertOk()->assertSee('Flagship works');
    }
}
