<?php

namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** PRD §12/§13 — public status tracking by reference + email (no account needed). */
class TrackRequestController extends Controller
{
    public function index(Request $request): View
    {
        $reference = trim((string) $request->query('reference', ''));

        return view('requests.track', [
            'reference' => $reference,
            'result' => null,
            'error' => null,
        ]);
    }

    public function show(Request $request): View
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email'],
        ], [], ['reference' => 'reference number']);

        $ref = strtoupper(trim($validated['reference']));

        $service = ServiceRequest::findByPublicReference($ref, $validated['email']);
        $quote = $service ? null : QuoteRequest::findByPublicReference($ref, $validated['email']);

        if (!$service && !$quote) {
            return view('requests.track', [
                'reference' => $ref,
                'result' => null,
                'error' => 'No request found for that reference number and email combination.',
            ])->withStatusCode(422);
        }

        return view('requests.track', [
            'reference' => $ref,
            'result' => $service ?? $quote,
            'isQuote' => (bool) $quote,
            'error' => null,
        ]);
    }
}
