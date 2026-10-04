<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Admin\Concerns\StreamsPrivateAttachment;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateServiceRequestRequest;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin → Service requests triage (PRD §12, Flow A).
 *
 * Staff see what the public request form captured, take ownership, move the
 * request through its workflow and, optionally, email the customer about the
 * new status. Field changes are audited by AuditableTriageObserver.
 */
class ServiceRequestController extends Controller
{
    use StreamsPrivateAttachment;

    public function __construct(private readonly RequestNotifier $notifier) {}

    /** GET /admin/service-requests */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(RequestStatus::class)],
            'division' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'assigned' => ['nullable', 'integer', 'exists:users,id'],
            'unassigned' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $requests = ServiceRequest::query()
            ->with(['division:id,name', 'service:id,name', 'assignee:id,name'])
            ->search($filters['q'] ?? null)
            ->status($filters['status'] ?? null)
            ->division($filters['division'] ?? null)
            ->assignedTo($filters['assigned'] ?? null)
            ->unassigned(filter_var($filters['unassigned'] ?? false, FILTER_VALIDATE_BOOL))
            ->receivedBetween($filters['from'] ?? null, $filters['to'] ?? null)
            // Open work first (oldest at the top), finished requests after.
            ->orderByRaw("CASE WHEN status IN ('completed', 'closed', 'cancelled') THEN 1 ELSE 0 END")
            ->orderBy('created_at')
            ->paginate(25)
            ->withQueryString();

        $open = ServiceRequest::query()->open();

        return view('admin.service-requests.index', [
            'requests' => $requests,
            'filters' => $filters,
            'statuses' => RequestStatus::options(),
            'divisions' => BusinessDivision::query()->orderBy('name')->get(['id', 'name']),
            'staff' => User::query()->assignableStaff()->get(['id', 'name']),
            'counts' => [
                'open' => (clone $open)->count(),
                'unassigned' => (clone $open)->whereNull('assigned_to')->count(),
                'awaiting_customer' => ServiceRequest::query()->where('status', RequestStatus::AwaitingCustomer)->count(),
                'completed_month' => ServiceRequest::query()
                    ->where('status', RequestStatus::Completed)
                    ->where('updated_at', '>=', now()->startOfMonth())
                    ->count(),
            ],
        ]);
    }

    /** GET /admin/service-requests/{serviceRequest} */
    public function show(ServiceRequest $serviceRequest): View
    {
        $serviceRequest->load(['division', 'service', 'assignee']);

        return view('admin.service-requests.show', [
            'item' => $serviceRequest,
            'trail' => AuditLog::query()->trailFor($serviceRequest)->get(),
            'statuses' => RequestStatus::options(),
            'staff' => User::query()->assignableStaff()->get(['id', 'name']),
        ]);
    }

    /** PATCH /admin/service-requests/{serviceRequest} */
    public function update(UpdateServiceRequestRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $payload = $request->triagePayload();

        if ($payload === []) {
            return back()->with('error', 'Nothing to update.');
        }

        $serviceRequest->fill($payload)->save();

        if ($serviceRequest->wasChanged('assigned_to') && $serviceRequest->assigned_to !== null) {
            $this->notifier->requestAssigned($serviceRequest->assignee()->first(), $serviceRequest);
        }

        if ($serviceRequest->wasChanged('status') && $request->shouldNotifyCustomer()) {
            $this->notifier->statusChanged($serviceRequest);
        }

        return redirect()
            ->route('admin.service-requests.show', $serviceRequest)
            ->with('success', "Service request {$serviceRequest->reference} updated.");
    }

    /** GET /admin/service-requests/{serviceRequest}/attachment */
    public function downloadAttachment(Request $request, ServiceRequest $serviceRequest, AuditLogger $audit): StreamedResponse
    {
        abort_if($request->user()->cannot('downloadAttachment', $serviceRequest), 403);

        return $this->streamAttachment($serviceRequest, 'servicerequest', $audit);
    }
}
