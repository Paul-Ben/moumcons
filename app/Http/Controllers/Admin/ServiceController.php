<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PricingType;
use App\Enums\ServiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequestForm;
use App\Models\BusinessDivision;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Services (PRD §11, route /admin/services in §31). Every service
 * belongs to a division; pricing visibility follows its pricing type.
 */
class ServiceController extends Controller
{
    /** GET /admin/services */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Service::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'category' => ['nullable', 'integer', 'exists:service_categories,id'],
            'status' => ['nullable', Rule::enum(ServiceStatus::class)],
        ]);

        $services = Service::query()
            ->with(['division:id,name', 'category:id,name'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%'))
            ->when($filters['division'] ?? null, fn ($q, $id) => $q->where('business_division_id', $id))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('service_category_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        return view('admin.services.index', [
            'services' => $services,
            'filters' => $filters,
            'statuses' => ServiceStatus::options(),
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id'),
            'categories' => ServiceCategory::query()->ordered()->pluck('name', 'id'),
        ]);
    }

    /** GET /admin/services/create */
    public function create(Request $request): View
    {
        Gate::authorize('create', Service::class);

        return view('admin.services.form', $this->formData(new Service([
            'status' => ServiceStatus::Draft,
            'pricing_type' => PricingType::QuoteRequired,
            'business_division_id' => $request->integer('division') ?: null,
        ])));
    }

    /** POST /admin/services */
    public function store(ServiceRequestForm $request): RedirectResponse
    {
        $data = $request->serviceData();
        // Without publish rights a new service starts as a draft.
        $data['status'] ??= ServiceStatus::Draft->value;

        $service = Service::create($data);

        return redirect()->route('admin.services.edit', $service)->with('success', "Service \"{$service->name}\" created.");
    }

    /** GET /admin/services/{service}/edit */
    public function edit(Service $service): View
    {
        Gate::authorize('update', $service);

        return view('admin.services.form', $this->formData($service));
    }

    /** PUT /admin/services/{service} */
    public function update(ServiceRequestForm $request, Service $service): RedirectResponse
    {
        $service->update($request->serviceData());

        return redirect()->route('admin.services.edit', $service)->with('success', "Service \"{$service->name}\" saved.");
    }

    /** DELETE /admin/services/{service} — past requests keep their record, unlinked. */
    public function destroy(Service $service): RedirectResponse
    {
        Gate::authorize('delete', $service);

        $service->delete();

        return redirect()->route('admin.services.index')->with('success', "Service \"{$service->name}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function formData(Service $service): array
    {
        return [
            'service' => $service,
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id')->all(),
            'categories' => ServiceCategory::query()->ordered()->pluck('name', 'id')->all(),
            'statuses' => ServiceStatus::options(),
            'pricingTypes' => PricingType::options(),
            'canPublish' => auth()->user()->can('publish', Service::class),
        ];
    }
}
