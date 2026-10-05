<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobOpeningRequest;
use App\Models\BusinessDivision;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Admin → Careers / job openings (PRD §17). */
class JobOpeningController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', JobOpening::class);

        return view('admin.jobs.index', [
            'jobs' => JobOpening::query()
                ->with('division:id,name')
                ->withCount(['applications', 'applications as new_applications_count' => fn ($q) => $q->where('status', 'received')])
                ->orderByRaw("CASE status WHEN 'open' THEN 0 ELSE 1 END")
                ->latest()
                ->paginate(25),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', JobOpening::class);

        return view('admin.jobs.form', $this->formData(new JobOpening([
            'status' => JobStatus::Open,
            'employment_type' => EmploymentType::FullTime,
        ])));
    }

    public function store(JobOpeningRequest $request): RedirectResponse
    {
        $job = JobOpening::create($request->jobData());

        return redirect()->route('admin.jobs.edit', $job)->with('success', "Job \"{$job->title}\" created.");
    }

    public function edit(JobOpening $job): View
    {
        Gate::authorize('update', $job);

        return view('admin.jobs.form', $this->formData($job));
    }

    public function update(JobOpeningRequest $request, JobOpening $job): RedirectResponse
    {
        $job->update($request->jobData());

        return redirect()->route('admin.jobs.edit', $job)->with('success', "Job \"{$job->title}\" saved.");
    }

    /** Applications go with the job, so a job that has any is closed instead. */
    public function destroy(JobOpening $job): RedirectResponse
    {
        Gate::authorize('delete', $job);

        if ($job->applications()->exists()) {
            return back()->with('error', 'This job has applications, so it cannot be deleted. Set its status to Closed or Filled instead.');
        }

        $job->delete();

        return redirect()->route('admin.jobs.index')->with('success', "Job \"{$job->title}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function formData(JobOpening $job): array
    {
        return [
            'job' => $job,
            'statuses' => JobStatus::options(),
            'employmentTypes' => EmploymentType::options(),
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id')->all(),
            'canPublish' => auth()->user()->can('publish', JobOpening::class),
        ];
    }
}
