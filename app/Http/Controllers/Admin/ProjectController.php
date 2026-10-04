<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\BusinessDivision;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Projects / portfolio (PRD §14, /admin/projects in §31). */
class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Project::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'status' => ['nullable', Rule::enum(ProjectStatus::class)],
        ]);

        $projects = Project::query()
            ->with('division:id,name')
            ->withCount('images')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($inner) => $inner
                ->where('title', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%')
                ->orWhere('client', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%')))
            ->when($filters['division'] ?? null, fn ($q, $id) => $q->where('business_division_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->ordered()
            ->paginate(25)
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'filters' => $filters,
            'statuses' => ProjectStatus::options(),
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return view('admin.projects.form', $this->formData(new Project(['status' => ProjectStatus::Ongoing])));
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = DB::transaction(function () use ($request) {
            $project = Project::create($request->projectData());
            $this->syncGallery($project, $request->galleryRows());

            return $project;
        });

        return redirect()->route('admin.projects.edit', $project)->with('success', "Project \"{$project->title}\" created.");
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('admin.projects.form', $this->formData($project->load('images')));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project) {
            $project->update($request->projectData());

            if ($request->has('gallery') || $request->boolean('gallery_submitted')) {
                $this->syncGallery($project, $request->galleryRows());
            }
        });

        return redirect()->route('admin.projects.edit', $project)->with('success', "Project \"{$project->title}\" saved.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', "Project \"{$project->title}\" deleted.");
    }

    /** @param  list<array{image: string, caption: ?string}>  $rows */
    private function syncGallery(Project $project, array $rows): void
    {
        $project->images()->delete();

        foreach ($rows as $position => $row) {
            $project->images()->create($row + ['sort_order' => $position + 1]);
        }
    }

    /** @return array<string, mixed> */
    private function formData(Project $project): array
    {
        return [
            'project' => $project,
            'statuses' => ProjectStatus::options(),
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id')->all(),
            'canPublish' => auth()->user()->can('publish', Project::class),
        ];
    }
}
