<?php

namespace App\Http\Controllers\Public;

use App\Enums\DeliveryMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingInterestRequest;
use App\Models\Enquiry;
use App\Models\TrainingProgramme;
use App\Services\RequestNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * PRD §15 — public training catalogue (/training, /training/{slug}).
 *
 * Registration and payment are future scope (§4.2), so "register interest"
 * files an enquiry: it reaches the existing triage queue, notifications and
 * audit trail without a parallel workflow.
 */
class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'mode' => ['nullable', Rule::enum(DeliveryMode::class)],
            'category' => ['nullable', 'string', 'max:255'],
            'past' => ['nullable', 'boolean'],
        ]);
        $showPast = (bool) ($filters['past'] ?? false);

        $base = TrainingProgramme::query()->published();

        $programmes = (clone $base)
            ->with('division:id,name,slug')
            ->when($showPast, fn ($q) => $q->whereNot(fn ($inner) => $inner->current()), fn ($q) => $q->current())
            ->when($filters['mode'] ?? null, fn ($q, $mode) => $q->where('delivery_mode', $mode))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('course_category', $category))
            ->when($showPast, fn ($q) => $q->orderByDesc('start_date'), fn ($q) => $q->chronological())
            ->paginate(12)
            ->withQueryString();

        return view('public.training.index', [
            'programmes' => $programmes,
            'categories' => (clone $base)->whereNotNull('course_category')->distinct()->orderBy('course_category')->pluck('course_category'),
            'modes' => DeliveryMode::options(),
            'filters' => $filters,
            'showPast' => $showPast,
        ]);
    }

    public function show(TrainingProgramme $programme): View
    {
        abort_unless($programme->isPublished(), 404);

        $programme->load('division');

        return view('public.training.show', ['programme' => $programme]);
    }

    /** POST /training/{programme}/interest */
    public function registerInterest(TrainingInterestRequest $request, TrainingProgramme $programme, RequestNotifier $notifier): RedirectResponse
    {
        abort_unless($programme->isPublished(), 404);

        if (! $programme->acceptsInterest()) {
            return back()->with('error', 'Registration for this programme has closed.');
        }

        $data = $request->validated();

        $lines = array_filter([
            "Training programme: {$programme->title}",
            $programme->dateRange() ? 'Dates: '.$programme->dateRange() : null,
            "Participants: {$data['participants']}",
            filled($data['message'] ?? null) ? "\n".$data['message'] : null,
        ]);

        $enquiry = Enquiry::create([
            'name' => $data['name'],
            'organization' => $data['organization'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => 'Training interest: '.$programme->title,
            'business_division_id' => $programme->business_division_id,
            'message' => implode("\n", $lines),
            'consent' => true,
        ]);

        $notifier->enquirySubmitted($enquiry);

        return redirect()
            ->route('training.show', $programme)
            ->with('success', "Thank you — your interest has been registered (reference {$enquiry->reference}). Our training team will be in touch.");
    }
}
