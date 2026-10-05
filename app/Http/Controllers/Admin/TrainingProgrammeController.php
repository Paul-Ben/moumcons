<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryMode;
use App\Enums\TrainingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingProgrammeRequest;
use App\Models\BusinessDivision;
use App\Models\TrainingProgramme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Training programmes (PRD §15, /admin/training in §31). */
class TrainingProgrammeController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', TrainingProgramme::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(TrainingStatus::class)],
        ]);

        $programmes = TrainingProgramme::query()
            ->with('division:id,name')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('title', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%'))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByRaw("CASE WHEN status IN ('completed', 'cancelled') THEN 1 ELSE 0 END")
            ->chronological()
            ->paginate(25)
            ->withQueryString();

        return view('admin.training.index', [
            'programmes' => $programmes,
            'filters' => $filters,
            'statuses' => TrainingStatus::options(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', TrainingProgramme::class);

        return view('admin.training.form', $this->formData(new TrainingProgramme([
            'status' => TrainingStatus::Upcoming,
            'delivery_mode' => DeliveryMode::Physical,
        ])));
    }

    public function store(TrainingProgrammeRequest $request): RedirectResponse
    {
        $programme = TrainingProgramme::create($request->programmeData());

        return redirect()->route('admin.training.edit', $programme)->with('success', "Programme \"{$programme->title}\" created.");
    }

    public function edit(TrainingProgramme $programme): View
    {
        Gate::authorize('update', $programme);

        return view('admin.training.form', $this->formData($programme));
    }

    public function update(TrainingProgrammeRequest $request, TrainingProgramme $programme): RedirectResponse
    {
        $programme->update($request->programmeData());

        return redirect()->route('admin.training.edit', $programme)->with('success', "Programme \"{$programme->title}\" saved.");
    }

    public function destroy(TrainingProgramme $programme): RedirectResponse
    {
        Gate::authorize('delete', $programme);

        $programme->delete();

        return redirect()->route('admin.training.index')->with('success', "Programme \"{$programme->title}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function formData(TrainingProgramme $programme): array
    {
        return [
            'programme' => $programme,
            'statuses' => TrainingStatus::options(),
            'deliveryModes' => DeliveryMode::options(),
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id')->all(),
            // Existing categories offered as suggestions so naming stays consistent.
            'courseCategories' => TrainingProgramme::query()->whereNotNull('course_category')->distinct()->orderBy('course_category')->pluck('course_category')->all(),
            'canPublish' => auth()->user()->can('publish', TrainingProgramme::class),
        ];
    }
}
