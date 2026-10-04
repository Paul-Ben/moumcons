{{-- Admin → Business divisions (PRD §5/§10). --}}
<x-layouts.admin title="Business Divisions">
    <div class="space-y-6">
        <x-admin.page-header title="Business Divisions" description="The divisions shown on the public site, in display order.">
            @can('create', App\Models\BusinessDivision::class)
                <a href="{{ route('admin.divisions.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New division</a>
            @endcan
        </x-admin.page-header>

        <form method="GET" class="flex flex-wrap gap-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search divisions…" class="form-input text-sm max-w-xs">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input text-sm max-w-[12rem]">
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
                            <th class="px-4 py-3 w-12">#</th>
                            <th class="px-4 py-3">Division</th>
                            <th class="px-4 py-3">Group</th>
                            <th class="px-4 py-3">Services</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($divisions as $division)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-slate-400">{{ $division->sort_order }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden flex items-center justify-center shrink-0">
                                            @if ($division->cover_image)
                                                <img src="{{ $division->cover_image }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                            @else
                                                <x-icon :name="$division->icon ?: 'building'" class="w-5 h-5 text-slate-400" />
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <span class="block font-medium text-moaum-charcoal">{{ $division->name }}</span>
                                            <span class="block text-xs text-slate-400 font-mono">/businesses/{{ $division->slug }}</span>
                                        </div>
                                        @if ($division->featured)
                                            <x-badge color="blue">Featured</x-badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $division->category ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    @can('viewAny', App\Models\Service::class)
                                        <a href="{{ route('admin.services.index', ['division' => $division->id]) }}" class="hover:text-moaum-blue">{{ $division->services_count }}</a>
                                    @else
                                        {{ $division->services_count }}
                                    @endcan
                                </td>
                                <td class="px-4 py-3"><span class="badge {{ $division->status->badgeClasses() }}">{{ $division->status->label() }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('businesses.show', $division) }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-moaum-blue mr-3" title="View on site">
                                        <x-icon name="external-link" class="w-4 h-4 inline" /><span class="sr-only">View on site</span>
                                    </a>
                                    @can('update', $division)
                                        <a href="{{ route('admin.divisions.edit', $division) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">No divisions match.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
