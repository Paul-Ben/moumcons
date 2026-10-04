<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Admin\Concerns\StreamsPrivateAttachment;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuoteRequestRequest;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin → Quote requests (PRD §13, Flow B).
 *
 * Request → Review → Clarification → Quote Preparation → Quote Sent →
 * Accepted / Declined → Order / Contract. Staff price the work here; moving to
 * Quote Sent stamps quote_sent_at and emails the customer their quote.
 */
class QuoteRequestController extends Controller
{
    use StreamsPrivateAttachment;

    public function __construct(private readonly RequestNotifier $notifier) {}

    /** GET /admin/quote-requests */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(QuoteStatus::class)],
            'division' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'assigned' => ['nullable', 'integer', 'exists:users,id'],
            'unassigned' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $quotes = QuoteRequest::query()
            ->with(['division:id,name', 'service:id,name', 'assignee:id,name'])
            ->search($filters['q'] ?? null)
            ->status($filters['status'] ?? null)
            ->division($filters['division'] ?? null)
            ->assignedTo($filters['assigned'] ?? null)
            ->unassigned(filter_var($filters['unassigned'] ?? false, FILTER_VALIDATE_BOOL))
            ->receivedBetween($filters['from'] ?? null, $filters['to'] ?? null)
            ->orderByRaw("CASE WHEN status IN ('accepted', 'declined', 'order_contract') THEN 1 ELSE 0 END")
            ->orderBy('created_at')
            ->paginate(25)
            ->withQueryString();

        $actionable = QuoteRequest::query()->actionable();

        return view('admin.quote-requests.index', [
            'quotes' => $quotes,
            'filters' => $filters,
            'statuses' => QuoteStatus::options(),
            'divisions' => BusinessDivision::query()->orderBy('name')->get(['id', 'name']),
            'staff' => User::query()->assignableStaff()->get(['id', 'name']),
            'counts' => [
                'actionable' => (clone $actionable)->count(),
                'unassigned' => (clone $actionable)->whereNull('assigned_to')->count(),
                'awaiting_reply' => QuoteRequest::query()->where('status', QuoteStatus::QuoteSent)->count(),
                'accepted' => QuoteRequest::query()->whereIn('status', [QuoteStatus::Accepted, QuoteStatus::OrderContract])->count(),
            ],
        ]);
    }

    /** GET /admin/quote-requests/{quoteRequest} */
    public function show(QuoteRequest $quoteRequest): View
    {
        $quoteRequest->load(['division', 'service', 'assignee']);

        return view('admin.quote-requests.show', [
            'item' => $quoteRequest,
            'trail' => AuditLog::query()->trailFor($quoteRequest)->get(),
            'statuses' => QuoteStatus::options(),
            'staff' => User::query()->assignableStaff()->get(['id', 'name']),
        ]);
    }

    /** PATCH /admin/quote-requests/{quoteRequest} */
    public function update(UpdateQuoteRequestRequest $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $payload = $request->triagePayload();

        if ($payload === []) {
            return back()->with('error', 'Nothing to update.');
        }

        $sending = ($payload['status'] ?? null) === QuoteStatus::QuoteSent->value
            && $quoteRequest->status !== QuoteStatus::QuoteSent;

        if ($sending) {
            $payload['quote_sent_at'] = now();
        }

        $quoteRequest->fill($payload)->save();

        if ($quoteRequest->wasChanged('assigned_to') && $quoteRequest->assigned_to !== null) {
            $this->notifier->requestAssigned($quoteRequest->assignee()->first(), $quoteRequest);
        }

        // Sending the quote always tells the customer — that is the point of
        // the step. Other moves notify only when staff tick the box.
        if ($sending) {
            $this->notifier->quoteSent($quoteRequest);
        } elseif ($quoteRequest->wasChanged('status') && $request->shouldNotifyCustomer()) {
            $this->notifier->statusChanged($quoteRequest);
        }

        return redirect()
            ->route('admin.quote-requests.show', $quoteRequest)
            ->with('success', $sending
                ? "Quote {$quoteRequest->reference} sent to {$quoteRequest->email}."
                : "Quote request {$quoteRequest->reference} updated.");
    }

    /** GET /admin/quote-requests/{quoteRequest}/attachment */
    public function downloadAttachment(Request $request, QuoteRequest $quoteRequest, AuditLogger $audit): StreamedResponse
    {
        abort_if($request->user()->cannot('downloadAttachment', $quoteRequest), 403);

        return $this->streamAttachment($quoteRequest, 'quoterequest', $audit);
    }
}
