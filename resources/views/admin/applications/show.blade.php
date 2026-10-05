{{-- Admin → one job application (PRD §17). --}}
<x-layouts.admin title="Application {{ $application->reference }}">
    <div class="space-y-6">
        <x-admin.page-header :title="$application->name" :back="route('admin.applications.index')" back-label="All applications"
            :description="'Applied for '.($application->job?->title ?? 'a removed job').' · '.$application->reference.' · '.$application->created_at->format('d M Y, H:i')">
            <span class="badge {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span>
        </x-admin.page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-10 h-10 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0"><x-icon name="file-text" class="w-5 h-5" /></span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-moaum-charcoal truncate">{{ $application->cv_original_name }}</p>
                            <p class="text-xs text-slate-400">CV — private storage, downloads are logged</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.applications.cv', $application) }}" class="btn-secondary text-sm"><x-icon name="download" class="w-4 h-4" /> Download CV</a>
                </div>

                @if ($application->cover_letter)
                    <div class="card">
                        <h2 class="font-semibold text-moaum-charcoal mb-3">Cover letter</h2>
                        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $application->cover_letter }}</p>
                    </div>
                @endif

                @if ($application->qualifications)
                    <div class="card">
                        <h2 class="font-semibold text-moaum-charcoal mb-3">Qualifications</h2>
                        <p class="text-slate-700 whitespace-pre-line">{{ $application->qualifications }}</p>
                    </div>
                @endif

                <x-admin.activity-trail :trail="$trail" />
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h2 class="font-semibold text-moaum-charcoal mb-4">Applicant</h2>
                    <dl class="space-y-3 text-sm">
                        <x-admin.detail label="Email"><a href="mailto:{{ $application->email }}" class="text-moaum-blue hover:underline break-all">{{ $application->email }}</a></x-admin.detail>
                        <x-admin.detail label="Phone">{{ $application->phone }}</x-admin.detail>
                        <x-admin.detail label="Consent">{{ $application->consent ? 'Given' : 'Not recorded' }}</x-admin.detail>
                    </dl>
                </div>

                @can('update', $application)
                    <form method="POST" action="{{ route('admin.applications.update', $application) }}" class="card space-y-4">
                        @csrf
                        @method('PATCH')
                        <h2 class="font-semibold text-moaum-charcoal">Review</h2>
                        <x-admin.select name="status" label="Status" :options="$statuses" :value="$application->status" required />
                        <x-admin.textarea name="internal_notes" label="Internal notes" :value="$application->internal_notes" rows="5" maxlength="5000" placeholder="Visible to staff only…" />
                        <button type="submit" class="btn-primary w-full"><x-icon name="check-circle" class="w-4 h-4" /> Save</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.admin>
