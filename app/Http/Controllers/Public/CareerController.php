<?php

namespace App\Http\Controllers\Public;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\Document;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Services\RequestNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** PRD §17 — public careers listing, job pages and applications. */
class CareerController extends Controller
{
    public function index(): View
    {
        return view('public.careers.index', [
            'jobs' => JobOpening::query()
                ->published()
                ->where('status', JobStatus::Open)
                ->with('division:id,name')
                ->orderByRaw('application_deadline IS NULL')
                ->orderBy('application_deadline')
                ->get(),
        ]);
    }

    public function show(JobOpening $job): View
    {
        abort_unless($job->isPublished(), 404);

        return view('public.careers.show', ['job' => $job->load('division')]);
    }

    public function apply(StoreJobApplicationRequest $request, JobOpening $job, RequestNotifier $notifier): RedirectResponse
    {
        abort_unless($job->isPublished(), 404);

        if (! $job->acceptsApplications()) {
            return back()->with('error', 'Applications for this position have closed.');
        }

        $data = $request->validated();
        $cv = $request->file('cv');
        $extension = in_array(strtolower($cv->getClientOriginalExtension()), ['pdf', 'doc', 'docx'], true)
            ? strtolower($cv->getClientOriginalExtension())
            : 'pdf';
        $name = Str::limit(Str::slug($data['name']) ?: 'applicant', 60, '').'-cv.'.$extension;

        $application = JobApplication::create([
            'career_id' => $job->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'qualifications' => $data['qualifications'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            // Random stored name; the readable one is used only for staff downloads.
            'cv_path' => $cv->storeAs('applications/'.now()->format('Y/m'), Str::uuid().'.'.$extension, Document::DISK),
            'cv_original_name' => $name,
            'consent' => true,
        ]);

        $notifier->applicationSubmitted($application);

        return redirect()
            ->route('careers.show', $job)
            ->with('success', "Thank you, your application has been received (reference {$application->reference}). We will contact shortlisted candidates.");
    }
}
