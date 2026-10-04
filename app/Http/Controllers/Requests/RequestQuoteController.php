<?php

namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequest;
use App\Models\BusinessDivision;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Services\RequestNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** PRD §13 — Request-a-Quote (Flow B): form → confirmation → tracking. */
class RequestQuoteController extends Controller
{
    public function create(Request $request): View
    {
        $divisions = BusinessDivision::publiclyVisible()->ordered()->get();

        $preselectedService = null;
        if ($slug = $request->filled('service') ? $request->string('service')->toString() : null) {
            $preselectedService = Service::active()->where('slug', $slug)->with('division')->first();
        }

        return view('requests.quote.create', [
            'divisions' => $divisions,
            'preselectedService' => $preselectedService,
            'prefillDivision' => $request->filled('division') ? $request->string('division')->toString() : null,
        ]);
    }

    public function store(StoreQuoteRequest $request, RequestNotifier $notifier): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('quote-requests', 'private');
        }

        $quote = QuoteRequest::create($data);

        $notifier->quoteSubmitted($quote);

        return redirect()
            ->route('requests.quote.confirmation', $quote->reference)
            ->with('success', 'Your quote request has been submitted.');
    }

    public function confirmation(string $reference): View
    {
        $quote = QuoteRequest::where('reference', strtoupper($reference))->firstOrFail();

        return view('requests.quote.confirmation', ['quote' => $quote]);
    }
}
