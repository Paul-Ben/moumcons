{{-- Admin → Quote requests: the Flow B queue (PRD §13). --}}
@use('Illuminate\Support\Str')

<x-layouts.admin title="Quote Requests">
    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal">Quote Requests</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Submitted through the public Request a Quote form &mdash;
                    {{ number_format($quotes->total()) }} {{ Str::plural('quote request', $quotes->total()) }} match the current view.
                </p>
            </div>
            @if ($counts['unassigned'] > 0)
                <x-badge color="warning">{{ $counts['unassigned'] }} awaiting an owner</x-badge>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dashboard.stat label="Needs action" :value="$counts['actionable']" icon="inbox" tone="blue" hint="Not yet decided" />
            <x-dashboard.stat label="Unassigned" :value="$counts['unassigned']" icon="users" tone="charcoal" hint="No owner yet" />
            <x-dashboard.stat label="Awaiting customer reply" :value="$counts['awaiting_reply']" icon="clock" tone="red" hint="Quote sent" />
            <x-dashboard.stat label="Won" :value="$counts['accepted']" icon="check-circle" tone="green" hint="Accepted or contracted" />
        </div>

        <x-admin.triage-filters :action="route('admin.quote-requests.index')" :filters="$filters"
            :statuses="$statuses" :divisions="$divisions" :staff="$staff"
            placeholder="Search name, organisation, email, reference or project…" />

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Requester</th>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Division / service</th>
                            <th class="px-4 py-3">Owner</th>
                            <th class="px-4 py-3 text-right">Quoted</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Received</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($quotes as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="block font-medium text-moaum-charcoal">{{ $item->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $item->organization ?: $item->email }}</span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <span class="block text-slate-700 truncate">{{ $item->project_title }}</span>
                                    <span class="block text-xs text-slate-400 font-mono">{{ $item->reference }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $item->division?->name ?? '—' }}
                                    @if ($item->service)
                                        <span class="block text-xs text-slate-400">{{ $item->service->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $item->assignee?->name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-slate-700">
                                    {{ $item->quoted_amount !== null ? '₦'.number_format((float) $item->quoted_amount) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $item->status->badgeClasses() }}">{{ $item->status->label() }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500" title="{{ $item->created_at->toIso8601String() }}">
                                    {{ $item->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.quote-requests.show', $item) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                    <x-icon name="inbox" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                    No quote requests match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($quotes->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $quotes->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
