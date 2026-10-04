{{-- Admin → Enquiries: the triage queue (PRD §21, route §31). --}}
@use('Illuminate\Support\Str')

<x-layouts.admin title="Enquiries">
    <div class="space-y-6">

        {{-- Page header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal">Enquiries &amp; Requests</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Everything captured from the contact form &mdash;
                    {{ number_format($enquiries->total()) }} {{ Str::plural('enquiry', $enquiries->total()) }} match the current view.
                </p>
            </div>
            @if ($counts['unassigned'] > 0)
                <x-badge color="warning">
                    {{ $counts['unassigned'] }} awaiting an owner
                </x-badge>
            @endif
        </div>

        {{-- Queue summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dashboard.stat label="Open" :value="$counts['open']" icon="inbox" tone="blue" hint="New, open or in progress" />
            <x-dashboard.stat label="Unassigned" :value="$counts['unassigned']" icon="users" tone="charcoal" hint="No owner yet" />
            <x-dashboard.stat label="High / urgent" :value="$counts['urgent']" icon="zap" tone="red" hint="Open and escalated" />
            <x-dashboard.stat label="Resolved today" :value="$counts['resolved_today']" icon="check-circle" tone="green" hint="Closed out today" />
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.enquiries.index') }}" class="card p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2">
                    <label for="q" class="form-label sr-only">Search</label>
                    <div class="relative">
                        <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Search name, organisation, email, reference or subject&hellip;"
                               class="form-input pl-9 text-sm">
                    </div>
                </div>

                <div>
                    <label for="status" class="form-label sr-only">Status</label>
                    <select id="status" name="status" class="form-input text-sm">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="form-label sr-only">Priority</label>
                    <select id="priority" name="priority" class="form-input text-sm">
                        <option value="">All priorities</option>
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="division" class="form-label sr-only">Division</label>
                    <select id="division" name="division" class="form-input text-sm">
                        <option value="">All divisions</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected((int) ($filters['division'] ?? 0) === $division->id)>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="assigned" class="form-label sr-only">Owner</label>
                    <select id="assigned" name="assigned" class="form-input text-sm">
                        <option value="">Anyone</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected((int) ($filters['assigned'] ?? 0) === $member->id)>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-input text-sm" aria-label="Received from">
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-input text-sm" aria-label="Received to">
                </div>

                <div class="flex items-center">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="unassigned" value="1" @checked($filters['unassigned'] ?? false)
                               class="rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                        Unassigned only
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-4">
                <button type="submit" class="btn-primary text-sm inline-flex items-center gap-2">
                    <x-icon name="sliders-horizontal" class="w-4 h-4" /> Apply filters
                </button>
                @if (collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty())
                    <a href="{{ route('admin.enquiries.index') }}" class="btn-ghost text-sm">Clear</a>
                @endif
            </div>
        </form>

        {{-- Queue --}}
        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Requester</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Division</th>
                            <th class="px-4 py-3">Owner</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Received</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($enquiries as $enquiry)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="block font-medium text-moaum-charcoal">{{ $enquiry->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $enquiry->organization ?: $enquiry->email }}</span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <span class="block text-slate-700 truncate">{{ $enquiry->subject }}</span>
                                    <span class="block text-xs text-slate-400 font-mono">{{ $enquiry->reference }}</span>
                                    @if ($enquiry->attachment)
                                        <span class="inline-flex items-center gap-1 mt-1 text-xs text-slate-500"
                                              title="Has an attachment: {{ basename($enquiry->attachment) }}">
                                            <x-icon name="download" class="w-3 h-3" /> Attachment
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $enquiry->division?->name ?? '—' }}
                                    @if ($enquiry->service)
                                        <span class="block text-xs text-slate-400">{{ $enquiry->service->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $enquiry->assignee?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $enquiry->priority->badgeClasses() }}">{{ $enquiry->priority->label() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $enquiry->status->badgeClasses() }}">{{ $enquiry->status->label() }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500" title="{{ $enquiry->created_at->toIso8601String() }}">
                                    {{ $enquiry->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.enquiries.show', $enquiry) }}"
                                       class="text-moaum-blue hover:text-moaum-red font-medium">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                    <x-icon name="inbox" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                    No enquiries match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($enquiries->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $enquiries->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>