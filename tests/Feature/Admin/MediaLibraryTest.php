<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** M2 — media library: optimisation on upload, picker JSON, permissions (PRD §19/§22). */
class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

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

    public function test_library_requires_view_permission(): void
    {
        $staff = $this->staffWith(['view-enquiries']);

        $this->actingAs($staff)->get(route('admin.media.index'))->assertForbidden();
    }

    public function test_upload_is_resized_converted_and_thumbnailed(): void
    {
        $staff = $this->staffWith(['view-media', 'upload-media']);

        $this->actingAs($staff)
            ->post(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image('Site Visit.jpg', 3000, 1500)],
                'alt_text' => 'Engineers on site',
            ])
            ->assertRedirect(route('admin.media.index'));

        $media = Media::sole();
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertSame(2000, $media->width);
        $this->assertSame(1000, $media->height);
        $this->assertSame('Engineers on site', $media->alt_text);
        $this->assertStringStartsWith('/storage/media/', $media->url());
        $this->assertStringEndsWith('.webp', $media->path);
        Storage::disk('public')->assertExists([$media->path, $media->thumb_path]);
        $this->assertTrue(AuditLog::query()->where('action', 'media.created')->exists());
    }

    public function test_json_upload_returns_picker_payload(): void
    {
        $staff = $this->staffWith(['view-media', 'upload-media']);

        $this->actingAs($staff)
            ->postJson(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('a.png', 400, 300)]])
            ->assertCreated()
            ->assertJsonStructure(['data' => [['id', 'url', 'thumb', 'name']]]);
    }

    public function test_non_images_are_rejected(): void
    {
        $staff = $this->staffWith(['view-media', 'upload-media']);

        $this->actingAs($staff)
            ->postJson(route('admin.media.store'), ['files' => [UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml')]])
            ->assertUnprocessable();

        $this->assertSame(0, Media::count());
    }

    public function test_viewer_cannot_upload(): void
    {
        $staff = $this->staffWith(['view-media']);

        $this->actingAs($staff)
            ->post(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('a.jpg')]])
            ->assertForbidden();
    }

    public function test_picker_json_lists_and_searches(): void
    {
        $staff = $this->staffWith(['view-media', 'upload-media']);
        $this->actingAs($staff)->post(route('admin.media.store'), ['files' => [
            UploadedFile::fake()->image('farm-harvest.jpg'),
            UploadedFile::fake()->image('office.jpg'),
        ]]);

        $this->actingAs($staff)->getJson(route('admin.media.index', ['q' => 'harvest']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'farm-harvest.jpg');
    }

    public function test_alt_text_update_and_delete_removes_files(): void
    {
        $staff = $this->staffWith(['view-media', 'upload-media', 'delete-media']);
        $this->actingAs($staff)->post(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('x.jpg')]]);
        $media = Media::sole();

        $this->actingAs($staff)->patch(route('admin.media.update', $media), ['alt_text' => 'New alt'])->assertRedirect();
        $this->assertSame('New alt', $media->fresh()->alt_text);

        $this->actingAs($staff)->delete(route('admin.media.destroy', $media))->assertRedirect(route('admin.media.index'));
        $this->assertSame(0, Media::count());
        Storage::disk('public')->assertMissing($media->path);
    }

    public function test_delete_requires_delete_permission(): void
    {
        $staff = $this->staffWith(['view-media', 'upload-media']);
        $this->actingAs($staff)->post(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('x.jpg')]]);

        $this->actingAs($staff)->delete(route('admin.media.destroy', Media::sole()))->assertForbidden();
    }
}
