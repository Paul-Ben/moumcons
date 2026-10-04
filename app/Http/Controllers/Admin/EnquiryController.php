<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Enums\Priority;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEnquiryRequest;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin → Enquiries triage (PRD §21, route /admin/enquiries in §31).
 *
 * Read side: a filterable queue of everything the contact form has captured.
 * Write side: staff move an enquiry through its statuses, set priority, take
 * ownership and leave notes for colleagues.
 *
 * Access control lives in routes/web.php ('permission:view-enquiries') and
 * App\Policies\EnquiryPolicy, which splits progression, ownership and closure
 * into separate permissions (PRD §23). The observer audits every save, so this
 * controller does not write audit rows for field changes itself.
 */
class EnquiryController extends Controller
{
    public function __construct(private readonly RequestNotifier $notifier) {}

    /** GET /admin/enquiries — the triage queue. */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(EnquiryStatus::class)],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'division' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'assigned' => ['nullable', 'integer', 'exists:users,id'],
            'unassigned' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $enquiries = Enquiry::query()
            ->with(['division:id,name', 'service:id,name', 'assignee:id,name'])
            ->search($filters['q'] ?? null)
            ->status($filters['status'] ?? null)
            ->priority($filters['priority'] ?? null)
            ->division($filters['division'] ?? null)
            ->assignedTo($filters['assigned'] ?? null)
            ->unassigned(filter_var($filters['unassigned'] ?? false, FILTER_VALIDATE_BOOL))
            ->receivedBetween($filters['from'] ?? null, $filters['to'] ?? null)
            // Urgent first, then oldest — the queue should surface what is both
            // most time-critical and longest waiting.
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->orderBy('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'filters' => $filters,
            'statuses' => EnquiryStatus::options(),
            'priorities' => Priority::options(),
            'divisions' => BusinessDivision::query()->orderBy('name')->get(['id', 'name']),
            'staff' => $this->assignableStaff(),
            'counts' => $this->queueCounts(),
        ]);
    }

    /** GET /admin/enquiries/{enquiry} — full enquiry plus its audit trail. */
    public function show(Enquiry $enquiry): View
    {
        $enquiry->load(['division', 'service', 'assignee']);

        $trail = AuditLog::query()
            ->where('subject_type', $enquiry->getMorphClass())
            ->where('subject_id', $enquiry->getKey())
            ->with('user:id,name')
            ->latest('id')
            ->get();

        return view('admin.enquiries.show', [
            'enquiry' => $enquiry,
            'trail' => $trail,
            'statuses' => EnquiryStatus::options(),
            'priorities' => Priority::options(),
            'staff' => $this->assignableStaff(),
        ]);
    }

    /**
     * PATCH /admin/enquiries/{enquiry} — apply triage changes.
     *
     * The resolution timestamp is derived from the status rather than accepted
     * from the form, so an enquiry cannot be marked resolved without a date and
     * reopening it clears the stale date.
     */
    public function update(UpdateEnquiryRequest $request, Enquiry $enquiry, AuditLogger $audit): RedirectResponse
    {
        $payload = $request->triagePayload();

        if ($payload === []) {
            return back()->with('error', 'Nothing to update.');
        }

        if (array_key_exists('status', $payload)) {
            $closing = in_array($payload['status'], [
                EnquiryStatus::Resolved->value,
                EnquiryStatus::Closed->value,
            ], true);

            $payload['resolved_at'] = $closing ? ($enquiry->resolved_at ?? now()) : null;
        }

        $newAssignee = $payload['assigned_to'] ?? null;
        $enquiry->fill($payload)->save();

        if (array_key_exists('assigned_to', $payload) && $newAssignee !== null) {
            $this->notifier->enquiryAssigned($enquiry->assignee()->first(), $enquiry);
        }

        return redirect()
            ->route('admin.enquiries.show', $enquiry)
            ->with('success', "Enquiry {$enquiry->reference} updated.");
    }

    /**
     * GET /admin/enquiries/{enquiry}/attachment — stream a customer upload.
     *
     * Attachments live on the non-public disk, so this is the only way to read
     * one. Access is audited: opening a customer's document is exactly the kind
     * of event PRD §32 expects in the trail.
     */
    public function downloadAttachment(Request $request, Enquiry $enquiry, AuditLogger $audit): StreamedResponse
    {
        abort_if($request->user()->cannot('downloadAttachment', $enquiry), 403);

        if (blank($enquiry->attachment) || ! Storage::disk('private')->exists($enquiry->attachment)) {
            abort(404);
        }

        $audit->log(
            action: 'enquiry.attachment_downloaded',
            description: sprintf('Attachment for Enquiry %s downloaded by %s', $enquiry->reference, $request->user()->name),
            properties: ['reference' => $enquiry->reference, 'path' => $enquiry->attachment],
            subject: $enquiry,
        );

        return Storage::disk('private')->download($enquiry->attachment, $this->attachmentFilename($enquiry));
    }

    /** Active staff who are eligible to own an enquiry. */
    private function assignableStaff(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** Queue summary shown as tiles above the table. */
    private function queueCounts(): array
    {
        $open = Enquiry::query()->open();

        return [
            'open' => (clone $open)->count(),
            'unassigned' => (clone $open)->whereNull('assigned_to')->count(),
            'urgent' => (clone $open)->whereIn('priority', [Priority::Urgent->value, Priority::High->value])->count(),
            'resolved_today' => Enquiry::query()
                ->whereNotNull('resolved_at')
                ->whereDate('resolved_at', today())
                ->count(),
        ];
    }

    /**
     * The stored name already carries the enquiry reference and the visitor's own
     * filename, so it can be handed to the browser unchanged.
     */
    private function attachmentFilename(Enquiry $enquiry): string
    {
        return basename($enquiry->attachment);
    }
}
