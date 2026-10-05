<?php

namespace Tests\Feature;

use App\Enums\DivisionStatus;
use App\Models\BusinessDivision;
use App\Models\User;
use App\Support\PublicNavigation;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/** M13 — security hardening, error pages, caching and backups (PRD §32/§35/§41). */
class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_branded_404_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('Page not found')->assertSee('Back to the home page');
    }

    public function test_deactivated_user_is_signed_out_on_next_request(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Rbac::ADMINISTRATOR);

        $this->actingAs($user);
        $user->forceFill(['is_active' => false])->save();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_navigation_cache_is_cleared_when_a_division_changes(): void
    {
        $division = BusinessDivision::factory()->create(['name' => 'Original Name', 'status' => DivisionStatus::Active]);
        $this->assertSame('Original Name', PublicNavigation::divisions()->first()->name);

        // Only plain data may be cached: real cache stores refuse to unserialise
        // models (cache.serializable_classes) — the array store in tests would not notice.
        $cached = Cache::get(PublicNavigation::CACHE_KEY);
        $this->assertIsArray($cached);
        $this->assertIsArray($cached[0]);

        $division->update(['name' => 'Renamed Division']);

        $this->assertSame('Renamed Division', PublicNavigation::divisions()->first()->name);
        $this->get(route('home'))->assertSee('Renamed Division');
    }

    public function test_backup_command_archives_database_and_uploads(): void
    {
        $database = storage_path('framework/testing/backup-test.sqlite');
        File::ensureDirectoryExists(dirname($database));
        File::put($database, 'SQLite format 3');
        // A separate connection: the test's own in-memory database must stay
        // the default, or RefreshDatabase loses track of its transaction.
        config(['database.connections.backup_test' => ['driver' => 'sqlite', 'database' => $database]]);

        Storage::disk('public')->put('media/2026/10/photo.webp', 'image-bytes');
        Storage::disk('documents')->put('library/brochure.pdf', 'pdf-bytes');

        $before = File::glob(storage_path('app/backups/moaum-*.zip'));

        $this->artisan('moaum:backup', ['--keep' => 50, '--connection' => 'backup_test'])->assertSuccessful();

        $created = array_values(array_diff(File::glob(storage_path('app/backups/moaum-*.zip')), $before));
        $this->assertCount(1, $created);

        $zip = new ZipArchive;
        $zip->open($created[0]);
        $names = collect(range(0, $zip->numFiles - 1))->map(fn ($i) => $zip->getNameIndex($i));
        $zip->close();
        File::delete([$created[0], $database]);
        Storage::disk('public')->delete('media/2026/10/photo.webp');
        Storage::disk('documents')->delete('library/brochure.pdf');

        $this->assertContains('database/database.sqlite', $names);
        $this->assertContains('files/media/2026/10/photo.webp', $names);
        $this->assertContains('files/private/documents/library/brochure.pdf', $names);
    }
}
