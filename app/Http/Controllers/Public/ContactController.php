<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\Service;
use App\Models\Setting;
use App\Services\RequestNotifier;
use App\Support\CompanyDetails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * PRD §20/§21 — Contact page and enquiry intake (Flow C).
 *
 * Anyone can send an enquiry without an account. The record is persisted for
 * triage, the visitor gets a reference for future correspondence, and staff are
 * alerted by email.
 */
class ContactController extends Controller
{
    /**
     * Must stay in step with the 'attachment' mimes rule on StoreEnquiryRequest:
     * anything outside this list is stored with a neutral extension.
     */
    private const ALLOWED_ATTACHMENT_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'zip',
    ];

    public function create(): View
    {
        return view('public.contact.create', [
            'divisions' => BusinessDivision::publiclyVisible()->ordered()->get(),
            // Grouped so the service list stays meaningful without dependent-select JS.
            'servicesByDivision' => Service::active()
                ->with('division:id,name')
                ->orderBy('name')
                ->get()
                ->groupBy(fn (Service $service) => $service->division?->name ?? 'Other'),
            'details' => [
                'address' => CompanyDetails::get('address'),
                'phone' => CompanyDetails::get('phone'),
                'email' => CompanyDetails::get('email'),
                'hours' => CompanyDetails::get('hours'),
                'social' => CompanyDetails::socialLinks(),
                // Re-checked here as well as on save: the iframe must only ever load Google Maps.
                'map' => str_starts_with((string) Setting::get('contact.map_embed_url'), 'https://www.google.com/maps/embed')
                    ? Setting::get('contact.map_embed_url')
                    : null,
            ],
        ]);
    }

    public function store(StoreEnquiryRequest $request, RequestNotifier $notifier): RedirectResponse
    {
        $data = $request->validated();

        $enquiry = Enquiry::create($data);

        // Saved before the upload so the stored name can carry the enquiry
        // reference, which makes attachments recognisable in the triage list
        // instead of showing an opaque hash.
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            $enquiry->update([
                'attachment' => $file->storeAs(
                    'enquiries',
                    $enquiry->reference.'-'.self::safeAttachmentName($file),
                    'private'
                ),
            ]);
        }

        $notifier->enquirySubmitted($enquiry);

        // Signed so the confirmation page — which shows the visitor's own
        // details — cannot be reached by guessing references.
        return redirect(URL::temporarySignedRoute(
            'contact.confirmation',
            now()->addDays(30),
            ['reference' => $enquiry->reference],
        ))->with('success', 'Your enquiry has been sent.');
    }

    public function confirmation(string $reference): View
    {
        $enquiry = Enquiry::where('reference', strtoupper($reference))->firstOrFail();

        return view('public.contact.confirmation', ['enquiry' => $enquiry]);
    }

    /**
     * Keep something recognisable of the visitor's filename.
     *
     * Laravel's store() hashes names, which makes the triage list and the
     * download show a hash. The extension is taken from the original name but is
     * constrained to the allow-list the form request already enforced, and the
     * path is stripped, so a crafted name cannot escape the directory.
     */
    private static function safeAttachmentName(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_ATTACHMENT_EXTENSIONS, true)) {
            $extension = 'bin';
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

        if ($base === '') {
            $base = 'attachment';
        }

        return Str::limit($base, 60, '').'.'.$extension;
    }
}
