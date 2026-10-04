<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DivisionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessDivisionRequest;
use App\Models\BusinessDivision;
use App\Support\Icons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Business divisions (PRD §5/§10, route /admin/businesses in §31).
 *
 * The 16 divisions are records, not templates (§5): staff edit copy, imagery,
 * contact details, lifecycle status and the "Key Capabilities" list here.
 */
class BusinessDivisionController extends Controller
{
    /** GET /admin/businesses */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', BusinessDivision::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(DivisionStatus::class)],
        ]);

        $divisions = BusinessDivision::query()
            ->withCount('services')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%'))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->ordered()
            ->get();

        return view('admin.divisions.index', [
            'divisions' => $divisions,
            'filters' => $filters,
            'statuses' => DivisionStatus::options(),
        ]);
    }

    /** GET /admin/businesses/create */
    public function create(): View
    {
        Gate::authorize('create', BusinessDivision::class);

        return view('admin.divisions.form', $this->formData(new BusinessDivision([
            'status' => DivisionStatus::Planned,
        ])));
    }

    /** POST /admin/businesses */
    public function store(BusinessDivisionRequest $request): RedirectResponse
    {
        $data = $request->divisionData();
        // Without publish rights a new division starts hidden from the public site.
        $data['status'] ??= DivisionStatus::Planned->value;

        $division = DB::transaction(function () use ($request, $data) {
            $division = BusinessDivision::create($data);
            $this->syncCapabilities($division, $request->capabilityRows());

            return $division;
        });

        return redirect()
            ->route('admin.divisions.edit', $division)
            ->with('success', "Division \"{$division->name}\" created.");
    }

    /** GET /admin/businesses/{division}/edit */
    public function edit(BusinessDivision $division): View
    {
        Gate::authorize('update', $division);

        return view('admin.divisions.form', $this->formData($division->load('capabilities')));
    }

    /** PUT /admin/businesses/{division} */
    public function update(BusinessDivisionRequest $request, BusinessDivision $division): RedirectResponse
    {
        DB::transaction(function () use ($request, $division) {
            $division->update($request->divisionData());
            $this->syncCapabilities($division, $request->capabilityRows());
        });

        return redirect()
            ->route('admin.divisions.edit', $division)
            ->with('success', "Division \"{$division->name}\" saved.");
    }

    /**
     * DELETE /admin/businesses/{division}
     *
     * A division that has services or has received customer requests is part
     * of the record, so it is archived rather than deleted.
     */
    public function destroy(BusinessDivision $division): RedirectResponse
    {
        Gate::authorize('delete', $division);

        $inUse = $division->services()->exists()
            || $division->serviceRequests()->exists()
            || $division->quoteRequests()->exists()
            || $division->enquiries()->exists();

        if ($inUse) {
            return back()->with('error', "\"{$division->name}\" has services or customer requests, so it cannot be deleted. Set its status to Archived instead.");
        }

        $division->delete();

        return redirect()->route('admin.divisions.index')->with('success', "Division \"{$division->name}\" deleted.");
    }

    /**
     * Update rows that kept their id, create new ones, delete the rest — so
     * the audit trail shows real edits rather than delete-and-recreate churn.
     *
     * @param  list<array{id: ?int, title: string, description: ?string}>  $rows
     */
    private function syncCapabilities(BusinessDivision $division, array $rows): void
    {
        $existing = $division->capabilities()->get()->keyBy('id');
        $kept = [];

        foreach ($rows as $position => $row) {
            $attributes = ['title' => $row['title'], 'description' => $row['description'], 'sort_order' => $position + 1];
            $capability = $row['id'] !== null ? $existing->get($row['id']) : null;

            if ($capability) {
                $capability->update($attributes);
                $kept[] = $capability->id;
            } else {
                $kept[] = $division->capabilities()->create($attributes)->id;
            }
        }

        $existing->except($kept)->each->delete();
    }

    /** @return array<string, mixed> */
    private function formData(BusinessDivision $division): array
    {
        return [
            'division' => $division,
            'statuses' => DivisionStatus::options(),
            'categories' => collect(config('moaum.nav.business_groups'))->pluck('heading', 'heading')->all(),
            'icons' => collect(Icons::names())->mapWithKeys(fn ($name) => [$name => $name])->all(),
            'canPublish' => auth()->user()->can('publish', BusinessDivision::class),
        ];
    }
}
