{{-- Admin → Projects (PRD §14). --}}
<x-layouts.admin title="Projects">
    <div class="space-y-6">
        <x-admin.page-header title="Projects" description="Portfolio entries shown on /projects and on division pages.">
            @can('create', App\Models\Project::class)
                <a href="{{ route('admin.projects.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New project</a>
            @endcan
        </x-admin.page-header>

        <form method="GET" class="card p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
            <div class="lg:col-span-2">
                <label for="q" class="form-label">Search</label>
                <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Title or client…" class="form-input text-sm">
            </div>
            <x-admin.select name="division" label="Division" :options="$divisions" :value="$filters['division'] ?? null" placeholder="All divisions" />
            <x-admin.select name="status" label="Status" :options="$statuses" :value="$filters['status'] ?? null" placeholder="All statuses" />
            <div class="sm:col-span-2 lg:col-span-4 flex gap-2">
                <button type="submit" class="btn-primary text-sm"><x-icon name="sliders-horizontal" class="w-4 h-4" /> Apply</button>
                @if (collect($filters)->filter()->isNotEmpty())
                    <a href="{{ route('admin.projects.index') }}" class="btn-ghost text-sm">Clear</a>
                @endif
            </div>
        </form>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Division</th>
                            <th class="px-4 py-3">Period</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">On site</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($projects as $project)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-12 h-10 rounded-lg bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                                            @if ($project->featured_image)
                                                <img src="{{ $project->featured_image }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                            @else
                                                <x-icon name="hard-hat" class="w-5 h-5 text-slate-400" />
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <span class="block font-medium text-moaum-charcoal truncate">{{ $project->title }}</span>
                                            <span class="block text-xs text-slate-400">{{ $project->client ?: 'No client listed' }} &middot; {{ $project->images_count }} {{ Str::plural('image', $project->images_count) }}</span>
                                        </div>
                                        @if ($project->featured)
                                            <x-badge color="blue">Featured</x-badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $project->division?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $project->period() ?? '—' }}</td>
                                <td class="px-4 py-3"><span class="badge {{ $project->status->badgeClasses() }}">{{ $project->status->label() }}</span></td>
                                <td class="px-4 py-3"><span class="badge {{ $project->publicationBadgeClasses() }}">{{ $project->publicationLabel() }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @can('update', $project)
                                        <a href="{{ route('admin.projects.edit', $project) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                <x-icon name="hard-hat" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                No projects yet.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($projects->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $projects->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
