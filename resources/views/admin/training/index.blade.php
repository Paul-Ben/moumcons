{{-- Admin → Training programmes (PRD §15). --}}
<x-layouts.admin title="Training">
    <div class="space-y-6">
        <x-admin.page-header title="Training Programmes" description="Upcoming programmes first; completed and cancelled ones at the end.">
            @can('create', App\Models\TrainingProgramme::class)
                <a href="{{ route('admin.training.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New programme</a>
            @endcan
        </x-admin.page-header>

        <form method="GET" class="flex flex-wrap gap-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search programmes…" class="form-input text-sm max-w-xs">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input text-sm max-w-[14rem]">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-ghost text-sm border border-slate-200">Filter</button>
        </form>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Programme</th>
                            <th class="px-4 py-3">Dates</th>
                            <th class="px-4 py-3">Mode</th>
                            <th class="px-4 py-3">Fee</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">On site</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($programmes as $programme)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="block font-medium text-moaum-charcoal">{{ $programme->title }}</span>
                                    <span class="block text-xs text-slate-400">{{ $programme->course_category ?: ($programme->division?->name ?? '—') }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $programme->dateRange() ?? 'TBC' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $programme->delivery_mode->label() }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $programme->feeLabel() }}</td>
                                <td class="px-4 py-3"><span class="badge {{ $programme->status->badgeClasses() }}">{{ $programme->status->label() }}</span></td>
                                <td class="px-4 py-3"><span class="badge {{ $programme->publicationBadgeClasses() }}">{{ $programme->publicationLabel() }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @can('update', $programme)
                                        <a href="{{ route('admin.training.edit', $programme) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                <x-icon name="graduation-cap" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                No training programmes yet.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($programmes->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $programmes->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
