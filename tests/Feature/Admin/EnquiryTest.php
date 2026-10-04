<?php

namespace Tests\Feature\Admin;

use App\Enums\EnquiryStatus;
use App\Enums\Priority;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\User;
use App\Notifications\EnquiryAssigned;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Module 4 — Enquiry triage queue (PRD §21, route /admin/enquiries in §31).
 *
 * The interesting guarantees here are the per-field permission split: viewing,
 * progressing, taking ownership and closing are four separate powers (§23), and
 * the resolution timestamp is derived from status rather than trusted from the
 * form.
 */
class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(Rbac::SUPER_ADMINISTRATOR);

        return $user->fresh();
    }

    /**
     * A staff account holding exactly the given enquiry permissions, so each
     * capability can be exercised in isolation.
     *
     * @param  list<string>  $permissions
     */
    private function staffWith(array $permissions): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'Test '.implode('-', $permissions),
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return $user->fresh();
    }

    /* ----------------------------- Access control ---------------------------- */

    public function test_queue_requires_authentication(): void
    {
        $this->get(route('admin.enquiries.index'))->assertRedirect(route('login'));
    }

    public function test_queue_requires_the_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole(Rbac::CUSTOMER);

        $this->actingAs($customer)->get(route('admin.enquiries.index'))->assertForbidden();
    }

    public function test_detail_requires_the_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole(Rbac::CUSTOMER);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($customer)->get(route('admin.enquiries.show', $enquiry))->assertForbidden();
    }

    public function test_triage_update_requires_the_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole(Rbac::CUSTOMER);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($customer)
            ->from(route('admin.enquiries.show', $enquiry))
            ->patch(route('admin.enquiries.update', $enquiry), ['status' => 'closed'])
            ->assertForbidden();

        $this->assertSame(EnquiryStatus::New, $enquiry->fresh()->status);
    }

    /* --------------------------------- Index --------------------------------- */

    public function test_queue_lists_enquiries_with_their_triage_facts(): void
    {
        $admin = $this->superAdmin();
        $division = BusinessDivision::factory()->create(['name' => 'Facility Management']);
        $owner = User::factory()->create(['name' => 'Bola Admin']);

        Enquiry::factory()->create([
            'name' => 'Ada Okonkwo',
            'subject' => 'Deep clean quotation',
            'business_division_id' => $division->id,
            'assigned_to' => $owner->id,
            'priority' => Priority::Urgent,
            'status' => EnquiryStatus::Open,
        ]);

        $this->actingAs($admin)->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertSee('Ada Okonkwo')
            ->assertSee('Deep clean quotation')
            ->assertSee('Facility Management')
            ->assertSee('Bola Admin')
            ->assertSee('Urgent')
            ->assertSee('Open');
    }

    public function test_queue_is_ordered_urgent_first_then_oldest(): void
    {
        $admin = $this->superAdmin();

        $normalOld = Enquiry::factory()->create([
            'subject' => 'Normal and old',
            'priority' => Priority::Normal,
            'created_at' => Carbon::now()->subDays(10),
        ]);
        $normalNew = Enquiry::factory()->create([
            'subject' => 'Normal and new',
            'priority' => Priority::Normal,
            'created_at' => Carbon::now()->subDay(),
        ]);
        $urgent = Enquiry::factory()->create([
            'subject' => 'Urgent one',
            'priority' => Priority::Urgent,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.enquiries.index'))->assertOk();
        $html = $response->getContent();

        $this->assertLessThan(
            strpos($html, 'Normal and old'),
            strpos($html, 'Urgent one'),
            'Urgent enquiries must sort above normal ones.',
        );
        $this->assertLessThan(
            strpos($html, 'Normal and new'),
            strpos($html, 'Normal and old'),
            'Within a priority, the longest-waiting enquiry comes first.',
        );

        unset($normalOld, $normalNew, $urgent);
    }

    public function test_queue_filters_by_status_priority_and_division(): void
    {
        $admin = $this->superAdmin();
        $division = BusinessDivision::factory()->create();

        Enquiry::factory()->create([
            'subject' => 'Matches every filter',
            'status' => EnquiryStatus::Open,
            'priority' => Priority::High,
            'business_division_id' => $division->id,
        ]);
        Enquiry::factory()->create([
            'subject' => 'Excluded by status',
            'status' => EnquiryStatus::Closed,
            'priority' => Priority::Low,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', [
                'status' => EnquiryStatus::Open->value,
                'priority' => Priority::High->value,
                'division' => $division->id,
            ]))
            ->assertOk()
            ->assertSee('Matches every filter')
            ->assertDontSee('Excluded by status');
    }

    public function test_queue_search_covers_requester_and_reference(): void
    {
        $admin = $this->superAdmin();

        $target = Enquiry::factory()->create(['name' => 'Chiamaka Nwosu', 'email' => 'cn@example.test']);
        Enquiry::factory()->create(['name' => 'Unrelated Person']);

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', ['q' => 'Chiamaka']))
            ->assertOk()
            ->assertSee('Chiamaka Nwosu')
            ->assertDontSee('Unrelated Person');

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', ['q' => $target->reference]))
            ->assertOk()
            ->assertSee('Chiamaka Nwosu');
    }

    public function test_queue_search_treats_wildcards_literally(): void
    {
        $admin = $this->superAdmin();

        Enquiry::factory()->create(['name' => 'Percent Match', 'subject' => 'Bells and whistles']);
        Enquiry::factory()->create(['name' => 'Someone Else', 'subject' => 'Anything at all']);

        // "%" must not match every row.
        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', ['q' => '%']))
            ->assertOk()
            ->assertDontSee('Someone Else');
    }

    public function test_queue_can_filter_to_unassigned_enquiries(): void
    {
        $admin = $this->superAdmin();
        $owner = User::factory()->create();

        Enquiry::factory()->create(['subject' => 'Nobody owns this', 'assigned_to' => null]);
        Enquiry::factory()->create(['subject' => 'Already owned', 'assigned_to' => $owner->id]);

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', ['unassigned' => 1]))
            ->assertOk()
            ->assertSee('Nobody owns this')
            ->assertDontSee('Already owned');
    }

    public function test_queue_rejects_an_unknown_status_filter(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', ['status' => 'nonsense']))
            ->assertSessionHasErrors('status');
    }

    public function test_queue_shows_counts_that_match_the_data(): void
    {
        $admin = $this->superAdmin();

        Enquiry::factory()->count(2)->create(['status' => EnquiryStatus::New, 'assigned_to' => null]);
        Enquiry::factory()->create(['status' => EnquiryStatus::Open, 'priority' => Priority::Urgent]);
        Enquiry::factory()->create(['status' => EnquiryStatus::Closed, 'resolved_at' => now()]);

        $html = $this->actingAs($admin)->get(route('admin.enquiries.index'))->assertOk()->getContent();

        // 4 enquiries: 3 open (new, new, open), 1 closed.
        $this->assertStringContainsString('Open', $html);
        $this->assertSame(4, Enquiry::count());
        $this->assertSame(3, Enquiry::query()->open()->count());
        $this->assertSame(3, Enquiry::query()->open()->whereNull('assigned_to')->count());
        $this->assertSame(1, Enquiry::query()->open()->whereIn('priority', [
            Priority::Urgent->value, Priority::High->value,
        ])->count());
        $this->assertSame(1, Enquiry::query()->whereDate('resolved_at', today())->count());
    }

    /* --------------------------------- Detail -------------------------------- */

    public function test_detail_shows_the_enquiry_and_its_activity(): void
    {
        $admin = $this->superAdmin();
        $enquiry = Enquiry::factory()->create([
            'name' => 'Ada Okonkwo',
            'email' => 'ada@example.test',
            'subject' => 'Freight enquiry',
            'message' => 'Please quote for moving twelve pallets.',
            'internal_notes' => 'Called back on Tuesday.',
        ]);

        $this->actingAs($admin)->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee('Freight enquiry')
            ->assertSee('ada@example.test')
            ->assertSee('Please quote for moving twelve pallets.')
            ->assertSee('Called back on Tuesday.')
            ->assertSee('enquiry.created');
    }

    public function test_internal_notes_are_absent_from_the_index(): void
    {
        $admin = $this->superAdmin();
        Enquiry::factory()->create([
            'subject' => 'Has notes',
            'internal_notes' => 'Confidential: pricing floor is 40k',
        ]);

        $this->actingAs($admin)->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertDontSee('Confidential: pricing floor is 40k');
    }

    /* --------------------------------- Triage -------------------------------- */

    public function test_staff_can_progress_status_priority_and_notes(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'status' => EnquiryStatus::Open->value,
            'priority' => Priority::High->value,
            'internal_notes' => 'Emailed the customer for details.',
        ])->assertRedirect(route('admin.enquiries.show', $enquiry))
            ->assertSessionHas('success');

        $enquiry->refresh();

        $this->assertSame(EnquiryStatus::Open, $enquiry->status);
        $this->assertSame(Priority::High, $enquiry->priority);
        $this->assertSame('Emailed the customer for details.', $enquiry->internal_notes);
    }

    public function test_closing_stamps_resolved_at_and_reopening_clears_it(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries', 'close-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'status' => EnquiryStatus::Resolved->value,
        ]);

        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::Resolved, $enquiry->status);
        $this->assertNotNull($enquiry->resolved_at, 'Resolving must stamp a resolution date.');

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'status' => EnquiryStatus::Open->value,
        ]);

        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::Open, $enquiry->status);
        $this->assertNull($enquiry->resolved_at, 'Reopening must clear the stale resolution date.');
    }

    public function test_closing_needs_the_close_permission(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)
            ->from(route('admin.enquiries.show', $enquiry))
            ->patch(route('admin.enquiries.update', $enquiry), ['status' => EnquiryStatus::Closed->value])
            ->assertForbidden();

        $this->assertSame(EnquiryStatus::New, $enquiry->fresh()->status);
    }

    public function test_reopening_a_closed_enquiry_needs_the_close_permission(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries']);
        $enquiry = Enquiry::factory()->create([
            'status' => EnquiryStatus::Closed,
            'resolved_at' => now(),
        ]);

        $this->actingAs($staff)
            ->from(route('admin.enquiries.show', $enquiry))
            ->patch(route('admin.enquiries.update', $enquiry), ['status' => EnquiryStatus::Open->value])
            ->assertForbidden();

        $this->assertSame(EnquiryStatus::Closed, $enquiry->fresh()->status);
    }

    public function test_progressing_without_close_permission_is_allowed(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'status' => EnquiryStatus::InProgress->value,
        ])->assertSessionHasNoErrors();

        $this->assertSame(EnquiryStatus::InProgress, $enquiry->fresh()->status);
    }

    public function test_assigning_notifies_the_new_owner(): void
    {
        Notification::fake();

        $staff = $this->staffWith(['view-enquiries', 'assign-enquiries']);
        $owner = $this->staffWith(['view-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'assigned_to' => $owner->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($owner->id, $enquiry->fresh()->assigned_to);
        Notification::assertSentTo($owner, EnquiryAssigned::class);
    }

    public function test_unassigning_does_not_send_a_notification(): void
    {
        Notification::fake();

        $staff = $this->staffWith(['view-enquiries', 'assign-enquiries']);
        $owner = $this->staffWith(['view-enquiries']);
        $enquiry = Enquiry::factory()->create(['assigned_to' => $owner->id]);

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'assigned_to' => '',
        ])->assertSessionHasNoErrors();

        $this->assertNull($enquiry->fresh()->assigned_to);
        Notification::assertNotSentTo($owner, EnquiryAssigned::class);
    }

    public function test_assigning_needs_the_assign_permission(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries']);
        $owner = $this->staffWith(['view-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)
            ->from(route('admin.enquiries.show', $enquiry))
            ->patch(route('admin.enquiries.update', $enquiry), ['assigned_to' => $owner->id])
            ->assertForbidden();

        $this->assertNull($enquiry->fresh()->assigned_to);
    }

    public function test_an_update_only_user_cannot_smuggle_assignment_through_the_notes_field(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries']);
        $owner = $this->staffWith(['view-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'internal_notes' => 'Fine to do',
            'assigned_to' => $owner->id,
        ])->assertForbidden();

        $enquiry->refresh();
        $this->assertNull($enquiry->assigned_to);
        $this->assertNull($enquiry->internal_notes);
    }

    public function test_a_user_with_no_triage_rights_cannot_post_at_all(): void
    {
        $staff = $this->staffWith(['view-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)
            ->from(route('admin.enquiries.show', $enquiry))
            ->patch(route('admin.enquiries.update', $enquiry), ['status' => EnquiryStatus::Open->value])
            ->assertForbidden();
    }

    public function test_inactive_users_cannot_be_given_ownership(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'assign-enquiries']);
        $leaver = User::factory()->create(['is_active' => false]);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->from(route('admin.enquiries.show', $enquiry))
            ->patch(route('admin.enquiries.update', $enquiry), ['assigned_to' => $leaver->id])
            ->assertSessionHasErrors('assigned_to');

        $this->assertNull($enquiry->fresh()->assigned_to);
    }

    public function test_invalid_status_and_priority_are_rejected(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries', 'close-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->from(route('admin.enquiries.show', $enquiry))
            ->patch(route('admin.enquiries.update', $enquiry), [
                'status' => 'exploded',
                'priority' => 'whenever',
            ])->assertSessionHasErrors(['status', 'priority']);

        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame(Priority::Normal, $enquiry->priority);
    }

    public function test_triage_changes_are_audited(): void
    {
        $staff = $this->staffWith(['view-enquiries', 'update-enquiries', 'close-enquiries', 'assign-enquiries']);
        $owner = $this->staffWith(['view-enquiries']);
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($staff)->patch(route('admin.enquiries.update', $enquiry), [
            'status' => EnquiryStatus::Resolved->value,
            'assigned_to' => $owner->id,
        ]);

        $actions = AuditLog::query()
            ->where('subject_type', $enquiry->getMorphClass())
            ->where('subject_id', $enquiry->getKey())
            ->pluck('action');

        $this->assertTrue($actions->contains('enquiry.status_changed'));
        $this->assertTrue($actions->contains('enquiry.assigned'));
    }

    /* ------------------------------- Attachment ------------------------------ */

    public function test_queue_flags_enquiries_that_carry_an_attachment(): void
    {
        $admin = $this->superAdmin();

        Enquiry::factory()->create([
            'subject' => 'Has a document',
            'attachment' => 'enquiries/ENQ-261004-AAAA-site-brief.pdf',
        ]);
        Enquiry::factory()->create(['subject' => 'No document', 'attachment' => null]);

        $this->actingAs($admin)->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertSee('site-brief.pdf')
            ->assertSee('Has a document');
    }

    public function test_attachment_can_be_downloaded_by_staff(): void
    {
        $admin = $this->superAdmin();
        Storage::fake('private');
        Storage::disk('private')->put('enquiries/brief.pdf', 'PDF CONTENTS');

        $enquiry = Enquiry::factory()->create(['attachment' => 'enquiries/brief.pdf']);

        $this->actingAs($admin)->get(route('admin.enquiries.attachment', $enquiry))
            ->assertOk()
            ->assertDownload('brief.pdf');

        Storage::disk('private')->assertExists('enquiries/brief.pdf');
    }

    public function test_attachment_download_requires_the_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole(Rbac::CUSTOMER);
        $enquiry = Enquiry::factory()->create(['attachment' => 'enquiries/brief.pdf']);

        $this->actingAs($customer)
            ->get(route('admin.enquiries.attachment', $enquiry))
            ->assertForbidden();
    }

    public function test_attachment_download_is_audited(): void
    {
        $admin = $this->superAdmin();
        Storage::fake('private');
        Storage::disk('private')->put('enquiries/brief.pdf', 'PDF CONTENTS');
        $enquiry = Enquiry::factory()->create(['attachment' => 'enquiries/brief.pdf']);

        $this->actingAs($admin)->get(route('admin.enquiries.attachment', $enquiry))->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'enquiry.attachment_downloaded',
            'subject_type' => $enquiry->getMorphClass(),
            'subject_id' => $enquiry->getKey(),
        ]);
    }

    public function test_missing_or_absent_attachments_return_404(): void
    {
        $admin = $this->superAdmin();
        Storage::fake('private');

        $withoutFile = Enquiry::factory()->create(['attachment' => null]);
        $missingFile = Enquiry::factory()->create(['attachment' => 'enquiries/gone.pdf']);

        $this->actingAs($admin)->get(route('admin.enquiries.attachment', $withoutFile))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.enquiries.attachment', $missingFile))->assertNotFound();
    }

    public function test_attachments_stored_by_the_public_form_are_reachable(): void
    {
        // Ties the Module 3 upload path to the Module 4 download path.
        $admin = $this->superAdmin();
        Notification::fake();
        Storage::fake('private');

        $this->post(route('contact.store'), [
            'name' => 'Ada Okonkwo',
            'email' => 'ada@example.test',
            'subject' => 'Freight enquiry',
            'message' => 'We need to move twelve pallets from Makurdi to Lagos.',
            'consent' => '1',
            'website' => '',
            'attachment' => UploadedFile::fake()->create('brief.pdf', 60, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $enquiry = Enquiry::sole();

        $this->actingAs($admin)->get(route('admin.enquiries.attachment', $enquiry))->assertOk();
        Storage::disk('private')->assertExists($enquiry->attachment);
    }

    /* -------------------------------- Navbar --------------------------------- */

    public function test_sidebar_links_to_the_queue_with_an_open_enquiry_count(): void
    {
        $admin = $this->superAdmin();
        Enquiry::factory()->count(3)->create(['status' => EnquiryStatus::New]);
        Enquiry::factory()->create(['status' => EnquiryStatus::Closed]);

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(route('admin.enquiries.index'), $html);
        unset($html);
    }

    public function test_sidebar_hides_the_queue_without_the_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole(Rbac::CONTENT_EDITOR);

        $this->actingAs($editor)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.enquiries.index'));
    }

    /* --------------------------------- Flash --------------------------------- */

    public function test_successful_triage_shows_a_flash_message(): void
    {
        $admin = $this->superAdmin();
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($admin)->patch(route('admin.enquiries.update', $enquiry), [
            'priority' => Priority::Urgent->value,
        ]);

        $this->followingRedirects()
            ->actingAs($admin)
            ->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee('updated');
    }
}
