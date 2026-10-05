{{-- Admin → create / edit a job opening (PRD §17). --}}
@php($editing = $job->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$job->title : 'New job'">
    <form method="POST" action="{{ $editing ? route('admin.jobs.update', $job) : route('admin.jobs.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $job->title : 'New job opening'" :back="route('admin.jobs.index')" back-label="All jobs">
            @if ($editing && $job->isPublished())
                <a href="{{ route('careers.show', $job) }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="title" label="Job title" :value="$job->title" required maxlength="255" />
                    <x-admin.input name="slug" label="URL slug" :value="$job->slug" maxlength="255" hint="Leave blank to generate from the title." />
                    <x-admin.textarea name="summary" label="Summary" :value="$job->summary" rows="2" maxlength="300" />
                    <x-admin.rich-text name="description" label="About the role" :value="$job->description" />
                    <x-admin.rich-text name="responsibilities" label="Responsibilities" :value="$job->responsibilities" />
                    <x-admin.rich-text name="qualifications" label="Qualifications" :value="$job->qualifications" />
                    <x-admin.rich-text name="requirements" label="Other requirements" :value="$job->requirements" />
                </div>
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    <x-admin.publish-fields :model="$job" :can-publish="$canPublish" :featured="false" />
                    <x-admin.select name="status" label="Job status" :options="$statuses" :value="$job->status" required
                                    hint="Applications are accepted while Open and before the deadline." />
                    <x-admin.input name="application_deadline" type="date" label="Application deadline" :value="$job->application_deadline?->format('Y-m-d')" />
                </div>
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Details</h2>
                    <x-admin.select name="employment_type" label="Employment type" :options="$employmentTypes" :value="$job->employment_type" required />
                    <x-admin.input name="location" label="Location" :value="$job->location" />
                    <x-admin.select name="business_division_id" label="Division" :options="$divisions" :value="$job->business_division_id" placeholder="— Company-wide —" />
                </div>
                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.jobs.index')" :submit="$editing ? 'Save job' : 'Create job'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $job)
            <div class="card mt-6 flex justify-end">
                <x-admin.delete-button :action="route('admin.jobs.destroy', $job)" label="Delete job" confirm="Delete this job opening?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
