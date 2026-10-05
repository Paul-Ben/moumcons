<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Models\AuditLog;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\RequestSubmitted;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** M9 — careers and job applications (PRD §17/§33). */
class CareersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('documents');
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->fresh();
    }

    private function applyPayload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Terkura Iorhemen',
            'email' => 'terkura@example.com',
            'phone' => '08031234567',
            'cover_letter' => 'I would love to join.',
            'cv' => UploadedFile::fake()->create('My CV.pdf', 200, 'application/pdf'),
            'consent' => '1',
        ];
    }

    public function test_editor_creates_and_publishes_a_job(): void
    {
        $editor = $this->userWithRole(Rbac::CONTENT_EDITOR);

        $this->actingAs($editor)->post(route('admin.jobs.store'), [
            'title' => 'ICT Support Officer',
            'employment_type' => 'full_time',
            'status' => 'open',
            'responsibilities' => '<ul><li>Support users</li></ul>',
            'publish' => '1',
        ])->assertRedirect();

        $job = JobOpening::sole();
        $this->assertTrue($job->isPublished());
        $this->get(route('careers.index'))->assertOk()->assertSee('ICT Support Officer');
        $this->get(route('careers.show', $job))->assertOk()->assertSee('<li>Support users</li>', escape: false);
    }

    public function test_closed_and_unpublished_jobs_are_not_listed(): void
    {
        JobOpening::factory()->create(['title' => 'Closed role', 'status' => JobStatus::Closed]);
        $draft = JobOpening::factory()->create(['title' => 'Draft role', 'published_at' => null]);

        $this->get(route('careers.index'))->assertDontSee('Closed role')->assertDontSee('Draft role');
        $this->get(route('careers.show', $draft))->assertNotFound();
    }

    public function test_applying_stores_cv_privately_and_notifies(): void
    {
        Notification::fake();
        $hr = $this->userWithRole(Rbac::ADMINISTRATOR);
        $job = JobOpening::factory()->create();

        $this->post(route('careers.apply', $job), $this->applyPayload())
            ->assertRedirect(route('careers.show', $job))
            ->assertSessionHas('success');

        $application = JobApplication::sole();
        $this->assertStringStartsWith('APP-', $application->reference);
        $this->assertSame('terkura-iorhemen-cv.pdf', $application->cv_original_name);
        Storage::disk('documents')->assertExists($application->cv_path);
        $this->assertStringNotContainsString('My CV', $application->cv_path);

        Notification::assertSentOnDemand(RequestSubmitted::class, fn ($n) => $n->type === 'application');
        Notification::assertSentTo($hr, AdminNewRequestAlert::class);
    }

    public function test_cv_must_be_a_document(): void
    {
        $job = JobOpening::factory()->create();

        $this->post(route('careers.apply', $job), $this->applyPayload([
            'cv' => UploadedFile::fake()->create('cv.exe', 10, 'application/x-msdownload'),
        ]))->assertSessionHasErrors('cv');

        $this->assertSame(0, JobApplication::count());
    }

    public function test_applications_are_refused_after_the_deadline(): void
    {
        $job = JobOpening::factory()->create(['application_deadline' => now()->subDay()]);

        $this->post(route('careers.apply', $job), $this->applyPayload())->assertSessionHas('error');
        $this->assertSame(0, JobApplication::count());
    }

    public function test_content_editors_cannot_see_applications(): void
    {
        $editor = $this->userWithRole(Rbac::CONTENT_EDITOR);
        $application = JobApplication::factory()->create();

        $this->actingAs($editor)->get(route('admin.applications.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.applications.cv', $application))->assertForbidden();
    }

    public function test_hr_reviews_an_application_and_cv_download_is_audited(): void
    {
        $admin = $this->userWithRole(Rbac::ADMINISTRATOR);
        Storage::disk('documents')->put('applications/test-cv.pdf', 'PDF');
        $application = JobApplication::factory()->create(['cv_path' => 'applications/test-cv.pdf']);

        $this->actingAs($admin)->get(route('admin.applications.index'))->assertOk()->assertSee($application->name);
        $this->actingAs($admin)->get(route('admin.applications.show', $application))->assertOk();
        $this->actingAs($admin)->get(route('admin.applications.cv', $application))->assertOk()->assertDownload('cv.pdf');

        $this->actingAs($admin)->patch(route('admin.applications.update', $application), [
            'status' => 'shortlisted', 'internal_notes' => 'Strong candidate',
        ])->assertRedirect();

        $this->assertSame(ApplicationStatus::Shortlisted, $application->fresh()->status);
        $this->assertTrue(AuditLog::query()->where('action', 'jobapplication.cv_downloaded')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'jobapplication.status_changed')->exists());
    }

    public function test_job_with_applications_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole(Rbac::ADMINISTRATOR);
        $application = JobApplication::factory()->create();

        $this->actingAs($admin)->delete(route('admin.jobs.destroy', $application->job))->assertSessionHas('error');
        $this->assertNotNull($application->job->fresh());
    }

    public function test_admin_job_screens_render(): void
    {
        $admin = $this->userWithRole(Rbac::ADMINISTRATOR);
        $job = JobOpening::factory()->create();

        $this->actingAs($admin)->get(route('admin.jobs.index'))->assertOk()->assertSee($job->title);
        $this->actingAs($admin)->get(route('admin.jobs.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.jobs.edit', $job))->assertOk();
    }
}
