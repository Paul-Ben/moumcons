<?php

namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\BusinessDivision;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\RequestSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** PRD §12 — Request-a-Service (Flow A): form → confirmation → tracking. */
class RequestServiceController extends Controller
{
    public function create(Request $request): View
    {
        $divisions = BusinessDivision::publiclyVisible()->ordered()->get();

        $preselectedService = null;
        if ($slug = $request->filled('service') ? $request->string('service')->toString() : null) {
            $preselectedService = Service::active()->where('slug', $slug)->with('division')->first();
        }

        return view('requests.service.create', [
            'divisions' => $divisions,
            'preselectedService' => $preselectedService,
            'prefillDivision' => $request->filled('division') ? $request->string('division')->toString() : null,
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['website']);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('service-requests', 'private');
        }

        $serviceRequest = ServiceRequest::create($data);

        // Customer receipt (queued mail).
        Mail::to($serviceRequest->email)->queue(
            new RequestSubmitted('service', $serviceRequest->reference, $serviceRequest->service?->name ?? $serviceRequest->division?->name ?? 'MOAUM services')
        );

        // Alert triage users (role: super_admin, business_manager, or anyone with manage-requests).
        User::permission('manage-requests')->each(fn (User $user) =>
            $user->notify(new AdminNewRequestAlert('service', $serviceRequest->reference, $serviceRequest->name, $serviceRequest->division?->name ?? '—'))
        );

        return redirect()
            ->route('requests.service.confirmation', $serviceRequest->reference)
            ->with('success', 'Your service request has been submitted.');
    }

    public function confirmation(string $reference): View
    {
        $serviceRequest = ServiceRequest::where('reference', strtoupper($reference))->firstOrFail();

        return view('requests.service.confirmation', ['request' => $serviceRequest]);
    }
}
