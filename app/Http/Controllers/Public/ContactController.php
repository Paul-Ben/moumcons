<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\Service;
use App\Services\RequestNotifier;
use App\Support\CompanyDetails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
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
            ],
        ]);
    }

    public function store(StoreEnquiryRequest $request, RequestNotifier $notifier): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('enquiries', 'private');
        }

        $enquiry = Enquiry::create($data);

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
}
