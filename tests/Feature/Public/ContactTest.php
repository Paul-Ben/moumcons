<?php

namespace Tests\Feature\Public;

use App\Enums\EnquiryStatus;
use App\Enums\Priority;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\RequestSubmitted;
use App\Support\CompanyDetails;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Module 3 — Contact page and enquiry intake (PRD §20/§21).
 *
 * Also guards a defect this module fixed: the footer and contact panel used to
 * print the literal string CLIENT_TO_PROVIDE as the company's address and phone.
 */
class ContactTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ada Okonkwo',
            'organization' => 'River Logistics Ltd',
            'email' => 'ada@riverlogistics.test',
            'phone' => '+234 803 555 0110',
            'subject' => 'Freight forwarding enquiry',
            'message' => 'We need to move 12 pallets from Makurdi to Lagos and would like a quotation.',
            'consent' => '1',
            'website' => '',
        ], $overrides);
    }

    public function test_contact_page_renders_the_form(): void
    {
        BusinessDivision::factory()->create(['name' => 'Transportation & Logistics']);

        $this->get(route('contact.index'))
            ->assertOk()
            ->assertSee('Get in Touch')
            ->assertSee('Send Enquiry')
            ->assertSee('Transportation &amp; Logistics', escape: false)
            ->assertSee('Request a Quote', escape: false);
    }

    public function test_contact_page_never_prints_placeholder_company_details(): void
    {
        // config/moaum.php ships address and phone as CLIENT_TO_PROVIDE.
        $this->get(route('contact.index'))
            ->assertOk()
            ->assertDontSee('CLIENT_TO_PROVIDE');

        $this->get(route('home'))->assertOk()->assertDontSee('CLIENT_TO_PROVIDE');
    }

    public function test_company_details_filter_placeholders(): void
    {
        config()->set('moaum.company.phone', '+234 803 555 0110');
        config()->set('moaum.company.address', 'CLIENT_TO_PROVIDE');
        config()->set('moaum.company.email', 'info@moaumconsultancy.com');

        $this->assertSame('+234 803 555 0110', CompanyDetails::get('phone'));
        $this->assertNull(CompanyDetails::get('address'));
        $this->assertFalse(CompanyDetails::has('address'));
        $this->assertSame('info@moaumconsultancy.com', CompanyDetails::get('email'));
        $this->assertSame('fallback', CompanyDetails::get('address', 'fallback'));
    }

    public function test_company_details_render_when_the_client_provides_them(): void
    {
        config()->set('moaum.company.phone', '+234 803 555 0110');
        config()->set('moaum.company.address', '14 Azetu Avenue, Makurdi');

        $this->get(route('contact.index'))
            ->assertOk()
            ->assertSee('14 Azetu Avenue, Makurdi')
            ->assertSee('+234 803 555 0110');
    }

    public function test_submission_persists_the_enquiry_with_a_reference(): void
    {
        Notification::fake();

        $division = BusinessDivision::factory()->create();
        $service = Service::factory()->create();

        $response = $this->post(route('contact.store'), $this->payload([
            'business_division_id' => $division->id,
            'service_id' => $service->id,
        ]));

        $enquiry = Enquiry::sole();

        $response->assertRedirect(URL::temporarySignedRoute(
            'contact.confirmation',
            now()->addDays(30),
            ['reference' => $enquiry->reference],
        ));

        $this->assertStringStartsWith('ENQ-', $enquiry->reference);
        $this->assertSame('Ada Okonkwo', $enquiry->name);
        $this->assertSame('River Logistics Ltd', $enquiry->organization);
        $this->assertSame('Freight forwarding enquiry', $enquiry->subject);
        $this->assertSame($division->id, $enquiry->business_division_id);
        $this->assertSame($service->id, $enquiry->service_id);
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame(Priority::Normal, $enquiry->priority);
        $this->assertTrue((bool) $enquiry->consent);
    }

    public function test_submitters_cannot_inject_admin_triage_fields(): void
    {
        // Only $request->validated() is persisted, so status/priority/ownership
        // stay under staff control.
        Notification::fake();

        $this->post(route('contact.store'), $this->payload([
            'status' => 'closed',
            'priority' => 'urgent',
            'assigned_to' => 1,
            'internal_notes' => 'ignore me',
        ]))->assertSessionHasNoErrors();

        $enquiry = Enquiry::sole();

        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame(Priority::Normal, $enquiry->priority);
        $this->assertNull($enquiry->assigned_to);
        $this->assertNull($enquiry->internal_notes);
    }

    public function test_general_enquiry_needs_no_division_or_service(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload())->assertSessionHasNoErrors();

        $enquiry = Enquiry::sole();

        $this->assertNull($enquiry->business_division_id);
        $this->assertNull($enquiry->service_id);
    }

    public function test_submission_emails_the_visitor_and_alerts_triage_staff(): void
    {
        Notification::fake();

        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole(Rbac::BUSINESS_MANAGER);

        $this->post(route('contact.store'), $this->payload());

        Notification::assertSentOnDemand(RequestSubmitted::class, function (RequestSubmitted $notification, array $channels, object $notifiable) {
            return $notifiable->routes['mail'] === 'ada@riverlogistics.test';
        });

        Notification::assertSentTo($manager, AdminNewRequestAlert::class);
    }

    public function test_triage_alert_skips_inactive_users(): void
    {
        Notification::fake();

        $this->seed(RolePermissionSeeder::class);
        $inactive = User::factory()->create(['is_active' => false]);
        $inactive->assignRole(Rbac::BUSINESS_MANAGER);

        $this->post(route('contact.store'), $this->payload());

        Notification::assertNotSentTo($inactive, AdminNewRequestAlert::class);
    }

    public function test_attachment_is_stored_on_the_private_disk(): void
    {
        Notification::fake();
        Storage::fake('private');

        $this->post(route('contact.store'), $this->payload([
            'attachment' => UploadedFile::fake()->create('brief.pdf', 120, 'application/pdf'),
        ]))->assertSessionHasNoErrors();

        $enquiry = Enquiry::sole();

        $this->assertNotNull($enquiry->attachment);
        $this->assertStringStartsWith('enquiries/', $enquiry->attachment);
        Storage::disk('private')->assertExists($enquiry->attachment);
    }

    public function test_submission_requires_consent_and_a_message(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload([
            'message' => 'too short',
            'consent' => '',
        ]))->assertSessionHasErrors(['message', 'consent']);

        $this->assertSame(0, Enquiry::count());
    }

    public function test_submission_requires_a_subject_and_a_valid_email(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload([
            'subject' => '',
            'email' => 'not-an-email',
        ]))->assertSessionHasErrors(['subject', 'email']);
    }

    public function test_honeypot_submissions_are_rejected(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_unknown_division_is_rejected(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->payload(['business_division_id' => 9999]))
            ->assertSessionHasErrors('business_division_id');
    }

    public function test_confirmation_page_echoes_the_enquiry(): void
    {
        $enquiry = Enquiry::factory()->create([
            'reference' => 'ENQ-261004-TEST',
            'name' => 'Ada Okonkwo',
            'subject' => 'Freight forwarding enquiry',
        ]);

        $this->get($this->signedConfirmationUrl('ENQ-261004-TEST'))
            ->assertOk()
            ->assertSee('Enquiry Received')
            ->assertSee('ENQ-261004-TEST')
            ->assertSee('Freight forwarding enquiry');

        $this->assertSame('ENQ-261004-TEST', $enquiry->reference);
    }

    public function test_confirmation_requires_a_valid_signature(): void
    {
        Enquiry::factory()->create([
            'reference' => 'ENQ-261004-TEST',
            'name' => 'Ada Okonkwo',
            'subject' => 'Freight forwarding enquiry',
        ]);

        // A bare reference must not disclose the enquiry...
        $this->get(route('contact.confirmation', 'ENQ-261004-TEST'))->assertForbidden();

        // ...and neither must a tampered signature.
        $this->get($this->signedConfirmationUrl('ENQ-261004-TEST').'x')->assertForbidden();
    }

    public function test_confirmation_404s_for_a_validly_signed_unknown_reference(): void
    {
        $this->get($this->signedConfirmationUrl('ENQ-NOPE-0000'))->assertNotFound();
    }

    public function test_confirmation_links_expire(): void
    {
        Enquiry::factory()->create(['reference' => 'ENQ-261004-TEST']);

        $url = URL::temporarySignedRoute(
            'contact.confirmation',
            now()->subMinute(),
            ['reference' => 'ENQ-261004-TEST'],
        );

        $this->get($url)->assertForbidden();
    }

    private function signedConfirmationUrl(string $reference): string
    {
        return URL::temporarySignedRoute(
            'contact.confirmation',
            now()->addDays(30),
            ['reference' => $reference],
        );
    }

    public function test_enquiry_receipt_email_is_labelled_correctly_and_offers_no_tracking_link(): void
    {
        // Tracking only searches service/quote requests, so an enquiry receipt
        // must not advertise it (PRD §21).
        $enquiry = Enquiry::factory()->create([
            'reference' => 'ENQ-261004-TEST',
            'name' => 'Ada Okonkwo',
            'subject' => 'Freight forwarding enquiry',
        ]);

        $mail = (new RequestSubmitted('enquiry', $enquiry->reference, $enquiry->subject))->toMail($enquiry);
        $rendered = $mail->render();

        $this->assertSame('We received your Enquiry (ENQ-261004-TEST)', $mail->subject);
        $this->assertStringContainsString('Freight forwarding enquiry', $rendered);
        $this->assertStringNotContainsString('Service Request', $rendered);
        $this->assertStringNotContainsString('Track your request', $rendered);
        $this->assertStringNotContainsString('track-request', $rendered);
    }

    public function test_request_receipts_keep_their_tracking_link(): void
    {
        $request = ServiceRequest::factory()->create(['reference' => 'SRQ-261004-TEST']);

        $mail = (new RequestSubmitted('service', $request->reference, 'Cleaning'))->toMail($request);

        $this->assertSame('We received your Service Request (SRQ-261004-TEST)', $mail->subject);
        $this->assertStringContainsString('Track your request', $mail->render());
    }
}
