<?php

namespace App\Http\Controllers\Public;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessDivision;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** PRD §14 — public project portfolio (/projects, /projects/{slug}). */
class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'division' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([ProjectStatus::Ongoing->value, ProjectStatus::Completed->value])],
        ]);

        // Only divisions that actually have published projects are offered as filters.
        $divisions = BusinessDivision::query()
            ->publiclyVisible()
            ->whereHas('projects', fn ($q) => $q->published())
            ->ordered()
            ->get(['id', 'name', 'slug']);

        $activeDivision = $divisions->firstWhere('slug', $filters['division'] ?? null);

        $projects = Project::query()
            ->published()
            ->with('division:id,name,slug')
            ->when($activeDivision, fn ($q) => $q->where('business_division_id', $activeDivision->id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('featured')
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('public.projects.index', [
            'projects' => $projects,
            'divisions' => $divisions,
            'activeDivision' => $activeDivision,
            'activeStatus' => $filters['status'] ?? null,
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->isPublished(), 404);

        $project->load(['division', 'images']);

        $related = Project::query()
            ->published()
            ->whereKeyNot($project->id)
            ->when($project->business_division_id, fn ($q, $id) => $q->where('business_division_id', $id))
            ->ordered()
            ->take(3)
            ->get();

        return view('public.projects.show', ['project' => $project, 'related' => $related]);
    }
}
