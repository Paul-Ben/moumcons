{{-- Admin → Service requests: the Flow A triage queue (PRD §12). --}}
@use('Illuminate\Support\Str')

<x-layouts.admin title="Service Requests">
    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal">Service Requests</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Submitted through the public Request a Service form &mdash;
                    {{ number_format($requests->total()) }} {{ Str::plural('request', $requests->total()) }} match the current view.
                </p>
            </div>
            @if ($counts['unassigned'] > 0)
                <x-badge color="warning">{{ $counts['unassigned'] }} awaiting an owner</x-badge>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dashboard.stat label="Open" :value="$counts['open']" icon="inbox" tone="blue" hint="Not yet completed or closed" />
            <x-dashboard.stat label="Unassigned" :value="$counts['unassigned']" icon="users" tone="charcoal" hint="Open with no owner" />
            <x-dashboard.stat label="Awaiting customer" :value="$counts['awaiting_customer']" icon="clock" tone="red" hint="Blocked on a reply" />
            <x-dashboard.stat label="Completed this month" :value="$counts['completed_month']" icon="check-circle" tone="green" />
        </div>

        <x-admin.triage-filters :action="route('admin.service-requests.index')" :filters="$filters"
            :statuses="$statuses" :divisions="$divisions" :staff="$staff"
            placeholder="Search name, organisation, email, reference or location…" />

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Requester</th>
                            <th class="px-4 py-3">Division / service</th>
                            <th class="px-4 py-3">Preferred date</th>
                            <th class="px-4 py-3">Owner</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Received</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="block font-medium text-moaum-charcoal">{{ $item->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $item->organization ?: $item->email }}</span>
                                    <span class="block text-xs text-slate-400 font-mono">{{ $item->reference }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $item->division?->name ?? '—' }}
                                    @if ($item->service)
                                        <span class="block text-xs text-slate-400">{{ $item->service->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $item->preferred_date?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $item->assignee?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $item->status->badgeClasses() }}">{{ $item->status->label() }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500" title="{{ $item->created_at->toIso8601String() }}">
                                    {{ $item->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.service-requests.show', $item) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                    <x-icon name="inbox" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                    No service requests match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
