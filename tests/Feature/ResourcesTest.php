<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\DivisionStatus;
use App\Enums\DownloadCategory;
use App\Models\BusinessDivision;
use App\Models\Document;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** M8 — downloads (PRD §18), FAQs (§22) and gallery (§19). */
class ResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('documents');
    }

    private function editor(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        return $user->fresh();
    }

    private function document(array $attributes = []): Document
    {
        $path = UploadedFile::fake()->create('profile.pdf', 20, 'application/pdf')->storeAs('library', 'profile.pdf', 'documents');

        return Document::create($attributes + [
            'title' => 'Company Profile',
            'category' => DownloadCategory::CompanyProfile,
            'access_level' => AccessLevel::Public,
            'file_path' => $path,
            'original_name' => 'company-profile.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 20480,
            'is_published' => true,
        ]);
    }

    /* ------------------------------- Downloads ------------------------------ */

    public function test_editor_uploads_a_document(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.documents.store'), [
            'title' => 'Service Catalogue 2026',
            'category' => 'service_catalogues',
            'access_level' => 'public',
            'is_published' => '1',
            'file' => UploadedFile::fake()->create('Catalogue 2026.pdf', 120, 'application/pdf'),
        ])->assertRedirect();

        $document = Document::sole();
        $this->assertSame('catalogue-2026.pdf', $document->original_name);
        $this->assertTrue($document->is_published);
        Storage::disk('documents')->assertExists($document->file_path);
    }

    public function test_executable_uploads_are_rejected(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.documents.store'), [
            'title' => 'Bad', 'category' => 'forms', 'access_level' => 'public',
            'file' => UploadedFile::fake()->create('run.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('file');
    }

    public function test_public_download_is_counted(): void
    {
        $document = $this->document();

        $this->get(route('downloads.index'))->assertOk()->assertSee('Company Profile');
        $this->get(route('downloads.show', $document))->assertOk()->assertDownload('company-profile.pdf');

        $this->assertSame(1, $document->fresh()->download_count);
    }

    public function test_registered_documents_need_sign_in(): void
    {
        $document = $this->document(['title' => 'Tender Pack', 'access_level' => AccessLevel::Registered]);

        $this->get(route('downloads.show', $document))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('downloads.show', $document))->assertOk();
    }

    public function test_internal_documents_are_hidden_from_the_public(): void
    {
        $document = $this->document(['title' => 'Internal Policy', 'access_level' => AccessLevel::Internal]);

        $this->get(route('downloads.index'))->assertDontSee('Internal Policy');
        $this->get(route('downloads.show', $document))->assertNotFound();

        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('downloads.show', $document))->assertNotFound();

        $this->actingAs($this->editor())->get(route('downloads.show', $document))->assertOk();
    }

    public function test_unpublished_documents_are_404(): void
    {
        $document = $this->document(['is_published' => false]);

        $this->get(route('downloads.show', $document))->assertNotFound();
    }

    public function test_deleting_a_document_removes_its_file(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(Rbac::ADMINISTRATOR);
        $document = $this->document();

        $this->actingAs($admin->fresh())->delete(route('admin.documents.destroy', $document))->assertRedirect();

        Storage::disk('documents')->assertMissing($document->file_path);
    }

    /* ---------------------------------- FAQs -------------------------------- */

    public function test_faqs_show_on_faq_page_and_division_page(): void
    {
        $editor = $this->editor();
        $division = BusinessDivision::factory()->create(['status' => DivisionStatus::Active]);

        $this->actingAs($editor)->post(route('admin.faqs.store'), [
            'question' => 'How do I request a quote?',
            'answer' => '<div>Use the <strong>Request a Quote</strong> form.</div>',
            'is_published' => '1',
        ])->assertRedirect();
        Faq::create(['question' => 'Do you fumigate warehouses?', 'answer' => '<div>Yes.</div>', 'business_division_id' => $division->id, 'is_published' => true]);
        Faq::create(['question' => 'Hidden question?', 'answer' => '<div>No.</div>', 'is_published' => false]);

        $this->get(route('faqs.index'))
            ->assertOk()
            ->assertSee('How do I request a quote?')
            ->assertSee('Do you fumigate warehouses?')
            ->assertDontSee('Hidden question?');

        $this->get(route('businesses.show', $division))->assertOk()->assertSee('Do you fumigate warehouses?');
    }

    /* -------------------------------- Gallery ------------------------------- */

    public function test_editor_creates_an_album_and_it_is_shown(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.galleries.store'), [
            'title' => 'Graduation Day 2026',
            'type' => 'event',
            'publish' => '1',
            'gallery' => [
                ['image' => '/storage/media/g1.webp', 'caption' => 'Graduates'],
                ['image' => '/storage/media/g2.webp', 'caption' => ''],
            ],
            'gallery_submitted' => '1',
        ])->assertRedirect();

        $gallery = Gallery::sole();
        $this->assertSame(2, $gallery->images()->count());

        $this->get(route('gallery.index'))->assertOk()->assertSee('Graduation Day 2026');
        $this->get(route('gallery.show', $gallery))->assertOk()->assertSee('/storage/media/g1.webp')->assertSee('Graduates');
    }

    public function test_unpublished_and_empty_albums_are_hidden(): void
    {
        $draft = Gallery::create(['title' => 'Draft album', 'type' => 'corporate']);
        $draft->images()->create(['image' => '/storage/x.webp']);
        Gallery::create(['title' => 'Empty album', 'type' => 'corporate', 'published_at' => now()->subDay()]);

        $this->get(route('gallery.index'))->assertDontSee('Draft album')->assertDontSee('Empty album');
        $this->get(route('gallery.show', $draft))->assertNotFound();
    }

    public function test_admin_screens_render(): void
    {
        $editor = $this->editor();
        $document = $this->document();
        $faq = Faq::create(['question' => 'Q?', 'answer' => '<div>A</div>']);
        $gallery = Gallery::create(['title' => 'Album', 'type' => 'corporate']);

        $this->actingAs($editor);
        $this->get(route('admin.documents.index'))->assertOk()->assertSee($document->title);
        $this->get(route('admin.documents.create'))->assertOk();
        $this->get(route('admin.documents.edit', $document))->assertOk();
        $this->get(route('admin.faqs.index'))->assertOk();
        $this->get(route('admin.faqs.edit', $faq))->assertOk();
        $this->get(route('admin.galleries.index'))->assertOk();
        $this->get(route('admin.galleries.edit', $gallery))->assertOk();
    }

    public function test_customer_login_lands_on_the_public_site(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create(['password' => 'Secret!2026']);
        $customer->assignRole(Rbac::CUSTOMER);

        $this->post(route('login.store'), ['email' => $customer->email, 'password' => 'Secret!2026'])
            ->assertRedirect(route('home'));
    }
}
