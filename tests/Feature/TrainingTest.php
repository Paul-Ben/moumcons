<?php

namespace Tests\Feature;

use App\Enums\TrainingStatus;
use App\Models\Enquiry;
use App\Models\TrainingProgramme;
use App\Models\User;
use App\Notifications\RequestSubmitted;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** M6 — training programmes and interest registration (PRD §15). */
class TrainingTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        return $user->fresh();
    }

    public function test_editor_creates_and_publishes_a_programme(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.training.store'), [
            'title' => 'AI for Public Servants',
            'delivery_mode' => 'hybrid',
            'status' => 'open_for_registration',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'fee' => '75000',
            'curriculum' => '<ul><li>Prompting</li></ul>',
            'publish' => '1',
        ])->assertRedirect();

        $programme = TrainingProgramme::sole();
        $this->assertSame('ai-for-public-servants', $programme->slug);
        $this->assertTrue($programme->isPublished());
        $this->assertSame('10 – 12 Nov 2026', $programme->dateRange());
        $this->assertSame('₦75,000', $programme->feeLabel());
    }

    public function test_end_before_start_is_rejected(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.training.store'), [
            'title' => 'X', 'delivery_mode' => 'online', 'status' => 'upcoming',
            'start_date' => '2026-11-10', 'end_date' => '2026-11-01',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_admin_screens_render(): void
    {
        $editor = $this->editor();
        $programme = TrainingProgramme::factory()->create();

        $this->actingAs($editor)->get(route('admin.training.index'))->assertOk()->assertSee($programme->title);
        $this->actingAs($editor)->get(route('admin.training.create'))->assertOk();
        $this->actingAs($editor)->get(route('admin.training.edit', $programme))->assertOk();
    }

    public function test_public_listing_shows_current_published_programmes(): void
    {
        TrainingProgramme::factory()->create(['title' => 'Upcoming course']);
        TrainingProgramme::factory()->unpublished()->create(['title' => 'Draft course']);
        TrainingProgramme::factory()->create(['title' => 'Finished course', 'status' => TrainingStatus::Completed]);

        $this->get(route('training.index'))
            ->assertOk()
            ->assertSee('Upcoming course')
            ->assertDontSee('Draft course')
            ->assertDontSee('Finished course');

        $this->get(route('training.index', ['past' => 1]))->assertSee('Finished course')->assertDontSee('Upcoming course');
    }

    public function test_programme_page_renders_and_unpublished_is_404(): void
    {
        $programme = TrainingProgramme::factory()->create(['curriculum' => '<ul><li>Module one</li></ul>']);
        $draft = TrainingProgramme::factory()->unpublished()->create();

        $this->get(route('training.show', $programme))->assertOk()->assertSee('<li>Module one</li>', escape: false)->assertSee('Register interest');
        $this->get(route('training.show', $draft))->assertNotFound();
    }

    public function test_registering_interest_creates_an_enquiry_and_receipt(): void
    {
        Notification::fake();
        $programme = TrainingProgramme::factory()->create(['title' => 'Data Analysis Bootcamp']);

        $this->post(route('training.interest', $programme), [
            'name' => 'Ngozi Eze',
            'email' => 'ngozi@example.com',
            'participants' => 4,
            'message' => 'For our finance team.',
            'consent' => '1',
        ])->assertRedirect(route('training.show', $programme))->assertSessionHas('success');

        $enquiry = Enquiry::sole();
        $this->assertSame('Training interest: Data Analysis Bootcamp', $enquiry->subject);
        $this->assertStringContainsString('Participants: 4', $enquiry->message);
        $this->assertStringContainsString('For our finance team.', $enquiry->message);
        Notification::assertSentOnDemand(RequestSubmitted::class);
    }

    public function test_interest_is_refused_after_the_deadline(): void
    {
        $programme = TrainingProgramme::factory()->create(['registration_deadline' => now()->subDay()]);

        $this->post(route('training.interest', $programme), [
            'name' => 'Late', 'email' => 'late@example.com', 'participants' => 1, 'consent' => '1',
        ])->assertSessionHas('error');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_honeypot_blocks_bots(): void
    {
        $programme = TrainingProgramme::factory()->create();

        $this->post(route('training.interest', $programme), [
            'name' => 'Bot', 'email' => 'bot@example.com', 'participants' => 1, 'consent' => '1', 'website' => 'spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_spotlight_on_home_page(): void
    {
        TrainingProgramme::factory()->create(['title' => 'Spotlight course', 'featured' => true]);

        $this->get(route('home'))->assertOk()->assertSee('Training Spotlight')->assertSee('Spotlight course');
    }
}
