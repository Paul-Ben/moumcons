<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Module 6 — Cross-division Services Catalogue (PRD §11).
 *
 * /services         → listing grouped by division, filterable by category + search
 * /services/{slug}  → service detail with Request-a-Service / Request-a-Quote CTAs
 */
class ServiceCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Service::query()
            ->active()
            ->with(['division' => fn ($q) => $q->publiclyVisible()->ordered(), 'category'])
            ->ordered();

        $categories = \App\Models\ServiceCategory::query()
            ->orderBy('sort_order')
            ->get();

        if (($categoryId = $request->integer('category')) > 0 && $categories->contains($categoryId, 'id')) {
            $query->where('service_category_id', $categoryId);
        }

        if (($divisionSlug = $request->filled('division') ? $request->string('division')->toString() : null)) {
            $query->whereHas('division', fn ($q) => $q->where('slug', $divisionSlug));
        }

        if ($search = $request->filled('q') ? trim($request->string('q')->toString()) : null) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('short_description', 'like', $like);
            });
        }

        // Group results under their division headings (prototype structure).
        $grouped = $query->get()->groupBy(fn (Service $s) => $s->division?->name ?? 'General Services');

        return view('public.services.index', [
            'grouped' => $grouped,
            'total' => $grouped->sum->count(),
            'categories' => $categories,
            'activeCategory' => $request->integer('category') ?: null,
            'activeDivision' => $divisionSlug ?? null,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Service $service): View
    {
        abort_unless($service->status === \App\Enums\ServiceStatus::Active, 404);
        abort_unless(
            $service->division && in_array($service->division->status, [\App\Enums\DivisionStatus::Active, \App\Enums\DivisionStatus::ComingSoon], true),
            404
        );

        $moreFromDivision = Service::query()
            ->active()
            ->where('business_division_id', $service->business_division_id)
            ->whereKeyNot($service->id)
            ->ordered()
            ->take(3)
            ->get();

        return view('public.services.show', [
            'service' => $service->load(['division', 'category']),
            'moreFromDivision' => $moreFromDivision,
        ]);
    }
}
