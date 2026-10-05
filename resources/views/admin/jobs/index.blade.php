{{-- Admin → Careers / job openings (PRD §17). --}}
<x-layouts.admin title="Careers">
    <div class="space-y-6">
        <x-admin.page-header title="Careers" description="Job openings shown on /careers.">
            @can('viewAny', App\Models\JobApplication::class)
                <a href="{{ route('admin.applications.index') }}" class="btn-ghost text-sm border border-slate-200"><x-icon name="users" class="w-4 h-4" /> Applications</a>
            @endcan
            @can('create', App\Models\JobOpening::class)
                <a href="{{ route('admin.jobs.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New job</a>
            @endcan
        </x-admin.page-header>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Job</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Deadline</th>
                            <th class="px-4 py-3">Applications</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">On site</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($jobs as $job)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="block font-medium text-moaum-charcoal">{{ $job->title }}</span>
                                    <span class="block text-xs text-slate-400">{{ $job->location ?: '—' }}{{ $job->division ? ' · '.$job->division->name : '' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $job->employment_type->label() }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $job->application_deadline?->format('d M Y') ?? 'Open' }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    @can('viewAny', App\Models\JobApplication::class)
                                        <a href="{{ route('admin.applications.index', ['job' => $job->id]) }}" class="hover:text-moaum-blue">{{ $job->applications_count }}</a>
                                    @else
                                        {{ $job->applications_count }}
                                    @endcan
                                    @if ($job->new_applications_count)
                                        <x-badge color="red" class="ml-1">{{ $job->new_applications_count }} new</x-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><span class="badge {{ $job->status->badgeClasses() }}">{{ $job->status->label() }}</span></td>
                                <td class="px-4 py-3"><span class="badge {{ $job->publicationBadgeClasses() }}">{{ $job->publicationLabel() }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @can('update', $job)
                                        <a href="{{ route('admin.jobs.edit', $job) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">No job openings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($jobs->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $jobs->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
