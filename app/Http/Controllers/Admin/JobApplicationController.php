<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin → Job applications (PRD §17). Personal data: gated by
 * view-applications / update-applications, and every CV download is audited
 * (§32/§33).
 */
class JobApplicationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', JobApplication::class);

        $filters = $request->validate([
            'job' => ['nullable', 'integer', 'exists:careers,id'],
            'status' => ['nullable', Rule::enum(ApplicationStatus::class)],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $applications = JobApplication::query()
            ->with('job:id,title')
            ->when($filters['job'] ?? null, fn ($q, $id) => $q->where('career_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%')
                ->orWhere('email', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%')
                ->orWhere('reference', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'filters' => $filters,
            'statuses' => ApplicationStatus::options(),
            'jobs' => JobOpening::query()->orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function show(JobApplication $application): View
    {
        Gate::authorize('view', $application);

        return view('admin.applications.show', [
            'application' => $application->load('job'),
            'statuses' => ApplicationStatus::options(),
            'trail' => AuditLog::query()->trailFor($application)->get(),
        ]);
    }

    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $application->update($request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]));

        return redirect()->route('admin.applications.show', $application)->with('success', "Application {$application->reference} updated.");
    }

    public function cv(JobApplication $application, AuditLogger $audit): StreamedResponse
    {
        Gate::authorize('view', $application);

        abort_unless(Storage::disk(Document::DISK)->exists($application->cv_path), 404);

        $audit->log(
            action: 'jobapplication.cv_downloaded',
            description: sprintf('CV for application %s downloaded by %s', $application->reference, auth()->user()->name),
            properties: ['reference' => $application->reference],
            subject: $application,
        );

        return Storage::disk(Document::DISK)->download($application->cv_path, $application->cv_original_name);
    }
}
