<?php

namespace App\Http\Controllers\Public;

use App\Enums\DivisionStatus;
use App\Enums\ServiceStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessDivision;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Module 5 — Business Directory.
 *
 * /businesses          → directory grid with search + category filter (PRD §9)
 * /businesses/{slug}   → division detail page replicating business-detail.html
 */
class BusinessController extends Controller
{
    /** Directory listing: publicly visible divisions, searchable & filterable. */
    public function index(Request $request): View
    {
        $query = BusinessDivision::query()
            ->publiclyVisible()
            ->withCount(['services as active_services_count' => fn ($q) => $q->where('status', ServiceStatus::Active)])
            ->ordered();

        // Category chips (distinct categories across visible divisions).
        $categories = BusinessDivision::query()
            ->publiclyVisible()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        if (($category = $request->filled('category') ? $request->string('category')->toString() : null)
            && $categories->contains($category)) {
            $query->where('category', $category);
        }

        if ($search = $request->filled('q') ? trim($request->string('q')->toString()) : null) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('short_description', 'like', $like);
            });
        }

        // Deep-link from the mega menu (?division=slug) → jump straight to detail.
        if ($slug = $request->filled('division') ? $request->string('division')->toString() : null) {
            $direct = BusinessDivision::publiclyVisible()->where('slug', $slug)->first();
            if ($direct) {
                return redirect()->route('businesses.show', $direct);
            }
        }

        return view('public.businesses.index', [
            'divisions' => $query->paginate(12)->withQueryString(),
            'categories' => $categories,
            'activeCategory' => $category,
            'search' => $search,
        ]);
    }

    /** Division detail — hero, overview, key capabilities, services, contact sidebar. */
    public function show(Request $request, BusinessDivision $division): View
    {
        abort_unless(
            in_array($division->status, [DivisionStatus::Active, DivisionStatus::ComingSoon], true),
            404
        );

        $division->load([
            'capabilities' => fn ($q) => $q->orderBy('sort_order'),
            'services' => fn ($q) => $q->where('status', ServiceStatus::Active)
                ->orderByDesc('featured')
                ->orderBy('sort_order'),
        ]);

        $related = BusinessDivision::query()
            ->publiclyVisible()
            ->whereKeyNot($division->id)
            ->when($division->category, fn ($q) => $q->where('category', $division->category))
            ->ordered()
            ->take(3)
            ->get();

        return view('public.businesses.show', [
            'division' => $division,
            'related' => $related,
        ]);
    }
}
