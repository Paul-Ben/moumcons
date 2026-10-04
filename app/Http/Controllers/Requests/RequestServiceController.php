<?php

namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\BusinessDivision;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Services\RequestNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(StoreServiceRequest $request, RequestNotifier $notifier): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('service-requests', 'private');
        }

        $serviceRequest = ServiceRequest::create($data);

        $notifier->serviceSubmitted($serviceRequest);

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
