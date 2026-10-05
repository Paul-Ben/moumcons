{{-- Admin → Job applications (PRD §17). --}}
<x-layouts.admin title="Applications">
    <div class="space-y-6">
        <x-admin.page-header title="Job Applications" description="Applicants' personal data — handle in line with the privacy notice."
            :back="auth()->user()->can('viewAny', App\Models\JobOpening::class) ? route('admin.jobs.index') : null" back-label="Careers" />

        <form method="GET" class="card p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
            <div>
                <label for="q" class="form-label">Search</label>
                <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email or reference…" class="form-input text-sm">
            </div>
            <x-admin.select name="job" label="Job" :options="$jobs" :value="$filters['job'] ?? null" placeholder="All jobs" />
            <x-admin.select name="status" label="Status" :options="$statuses" :value="$filters['status'] ?? null" placeholder="All statuses" />
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-sm">Apply</button>
                @if (collect($filters)->filter()->isNotEmpty())
                    <a href="{{ route('admin.applications.index') }}" class="btn-ghost text-sm">Clear</a>
                @endif
            </div>
        </form>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Applicant</th>
                            <th class="px-4 py-3">Job</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Received</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($applications as $application)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="block font-medium text-moaum-charcoal">{{ $application->name }}</span>
                                    <span class="block text-xs text-slate-400">{{ $application->email }} · <span class="font-mono">{{ $application->reference }}</span></span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $application->job?->title ?? '—' }}</td>
                                <td class="px-4 py-3"><span class="badge {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span></td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $application->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.applications.show', $application) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">No applications match.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($applications->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $applications->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
