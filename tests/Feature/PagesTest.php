<?php

namespace Tests\Feature;

use App\Enums\PageStatus;
use App\Models\LeadershipMember;
use App\Models\Page;
use App\Models\User;
use App\Support\Rbac;
use App\Support\RichText;
use Database\Seeders\PageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** M7 — About section, legal pages, custom pages and leadership (PRD §9/§22). */
class PagesTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        return $user->fresh();
    }

    public function test_seeder_creates_system_pages_idempotently(): void
    {
        $this->seed(PageSeeder::class);
        Page::forKey('about')->update(['title' => 'Edited title']);
        $this->seed(PageSeeder::class);

        $this->assertSame(count(Page::SYSTEM), Page::count());
        $this->assertSame('Edited title', Page::forKey('about')->title);
    }

    public function test_about_pages_render_with_sub_navigation(): void
    {
        $this->seed(PageSeeder::class);

        foreach (['about.profile', 'about.mission', 'about.leadership', 'about.university', 'legal.privacy', 'legal.terms'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('about.mission'))
            ->assertSee('Mission, Vision &amp; Values', escape: false)
            ->assertSee('University Relationship');
    }

    public function test_placeholders_never_reach_the_public_site(): void
    {
        $this->seed(PageSeeder::class);

        $this->get(route('about.profile'))
            ->assertOk()
            ->assertDontSee('CLIENT_TO_PROVIDE')
            ->assertDontSee('<h2>History</h2>', escape: false)
            ->assertSee('Who we are');
    }

    public function test_placeholder_stripping_keeps_surrounding_content(): void
    {
        $html = '<h2>Keep</h2><div>Real copy</div><h2>History</h2><div>CLIENT_TO_PROVIDE — later</div><div>Tail</div>';

        $this->assertSame('<h2>Keep</h2><div>Real copy</div><div>Tail</div>', RichText::withoutPlaceholders($html));
    }

    public function test_draft_system_page_is_404(): void
    {
        $this->seed(PageSeeder::class);
        Page::forKey('terms')->update(['status' => PageStatus::Draft]);

        $this->get(route('legal.terms'))->assertNotFound();
    }

    public function test_editor_creates_a_custom_page(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.pages.store'), [
            'title' => 'Tender Opportunities',
            'content' => '<div>Current tenders</div>',
            'status' => 'published',
        ])->assertRedirect();

        $page = Page::sole();
        $this->assertSame('tender-opportunities', $page->slug);
        $this->get(route('pages.show', $page))->assertOk()->assertSee('Current tenders');
    }

    public function test_system_pages_cannot_be_deleted_or_moved(): void
    {
        $editor = $this->editor();
        $this->seed(PageSeeder::class);
        $about = Page::forKey('about');

        $this->actingAs($editor)->put(route('admin.pages.update', $about), [
            'title' => 'Profile', 'slug' => 'moved', 'status' => 'published',
        ])->assertSessionHasErrors('slug');

        $admin = User::factory()->create();
        $admin->assignRole(Rbac::ADMINISTRATOR);
        $this->actingAs($admin->fresh())->delete(route('admin.pages.destroy', $about))->assertForbidden();

        // System pages are only reachable at their fixed route.
        $this->get(route('pages.show', $about))->assertNotFound();
    }

    public function test_admin_screens_render(): void
    {
        $editor = $this->editor();
        $this->seed(PageSeeder::class);
        $member = LeadershipMember::factory()->create();

        $this->actingAs($editor)->get(route('admin.pages.index'))->assertOk()->assertSee('Needs client copy');
        $this->actingAs($editor)->get(route('admin.pages.create'))->assertOk();
        $this->actingAs($editor)->get(route('admin.pages.edit', Page::forKey('about')))->assertOk();
        $this->actingAs($editor)->get(route('admin.leadership.index'))->assertOk()->assertSee($member->name);
        $this->actingAs($editor)->get(route('admin.leadership.edit', $member))->assertOk();
    }

    public function test_leadership_members_are_managed_and_shown(): void
    {
        $editor = $this->editor();
        $this->seed(PageSeeder::class);

        $this->actingAs($editor)->post(route('admin.leadership.store'), [
            'name' => 'Aondona Terver', 'position' => 'Managing Director', 'is_published' => '1',
        ])->assertRedirect(route('admin.leadership.index'));
        LeadershipMember::factory()->create(['name' => 'Hidden Person', 'is_published' => false]);

        $this->get(route('about.leadership'))
            ->assertOk()
            ->assertSee('Aondona Terver')
            ->assertSee('Managing Director')
            ->assertDontSee('Hidden Person');
    }

    public function test_footer_links_to_legal_pages(): void
    {
        $this->seed(PageSeeder::class);

        $this->get(route('home'))->assertSee(route('legal.privacy'))->assertSee(route('legal.terms'));
    }
}
