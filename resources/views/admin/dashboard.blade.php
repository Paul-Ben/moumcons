<x-layouts.admin title="Dashboard">
    {{-- PRD §24 — Administration Dashboard. --}}

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="font-display text-2xl font-bold text-moaum-charcoal">Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ now()->format('l, j F Y') }} — {{ \App\Support\AdminNav::openTriageCount() }} item(s) awaiting attention.
            </p>
        </div>
        @can('view-audit-logs')
            <a href="{{ route('admin.audit-logs.index') }}" class="btn-outline text-sm inline-flex items-center gap-2">
                <x-icon name="shield-check" class="w-4 h-4" /> View audit trail
            </a>
        @endcan
    </div>

    {{-- Widget row --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach ($stats as $stat)
            <x-dashboard.stat
                :label="$stat['label']"
                :value="$stat['value']"
                :icon="$stat['icon']"
                :tone="$stat['tone']"
                :hint="$stat['hint']" />
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid lg:grid-cols-3 gap-4 mb-6">
        <div class="card lg:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display font-bold text-moaum-charcoal">Enquiries by month</h2>
                <span class="text-xs text-slate-400">Last 12 months</span>
            </div>
            <x-dashboard.bars :series="$enquiriesByMonth" :height="180" />
        </div>

        <div class="card">
            <h2 class="font-display font-bold text-moaum-charcoal mb-4">Open requests by division</h2>
            <x-dashboard.breakdown :series="$requestsByDivision" empty="No open service requests" />
        </div>

        <div class="card">
            <h2 class="font-display font-bold text-moaum-charcoal mb-4">Open requests by service</h2>
            <x-dashboard.breakdown :series="$requestsByService" empty="No open service requests" />
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="grid lg:grid-cols-2 gap-4 mb-6">
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display font-bold text-moaum-charcoal">Latest enquiries</h2>
                <span class="text-xs text-slate-400">{{ $recentEnquiries->count() }} most recent</span>
            </div>

            @if ($recentEnquiries->isEmpty())
                <p class="text-sm text-slate-400 py-6 text-center">No enquiries received yet.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($recentEnquiries as $enquiry)
                        <li class="py-3 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">
                                    {{ $enquiry->name }}
                                    <span class="text-slate-400 font-normal">· {{ $enquiry->division?->name ?? 'No division' }}</span>
                                </p>
                                <p class="text-xs text-slate-400 truncate">{{ $enquiry->subject }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="badge {{ $enquiry->status->badgeClasses() }}">{{ $enquiry->status->label() }}</span>
                                <p class="text-xs text-slate-400 mt-1">{{ $enquiry->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display font-bold text-moaum-charcoal">Latest service requests</h2>
                <span class="text-xs text-slate-400">{{ $recentRequests->count() }} most recent</span>
            </div>

            @if ($recentRequests->isEmpty())
                <p class="text-sm text-slate-400 py-6 text-center">No service requests yet.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($recentRequests as $request)
                        <li class="py-3 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">
                                    {{ $request->reference }}
                                    <span class="text-slate-400 font-normal">· {{ $request->division?->name ?? 'No division' }}</span>
                                </p>
                                <p class="text-xs text-slate-400 truncate">{{ $request->name }} — {{ $request->location ?? 'Location not given' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="badge {{ $request->status->badgeClasses() }}">{{ $request->status->label() }}</span>
                                <p class="text-xs text-slate-400 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Audit tail --}}
    <div class="card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-moaum-charcoal">Recent activity</h2>
            @can('view-audit-logs')
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-moaum-blue font-semibold hover:underline">See all</a>
            @endcan
        </div>

        @if ($recentAudit->isEmpty())
            <p class="text-sm text-slate-400 py-6 text-center">No activity recorded yet.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($recentAudit as $entry)
                    <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                        <span class="text-slate-600 truncate">{{ $entry->description }}</span>
                        <span class="text-xs text-slate-400 shrink-0">{{ $entry->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Counters with no data source yet are listed, not faked as zero. --}}
    <div class="card border-dashed">
        <h2 class="font-display font-bold text-moaum-charcoal mb-1">Coming with the next modules</h2>
        <p class="text-sm text-slate-500 mb-4">
            These PRD §24 counters stay hidden until their module ships, so a zero here never reads as "none exist".
        </p>
        <div class="flex flex-wrap gap-2">
            @foreach ($pendingModules as $pending)
                <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-500 bg-slate-100 rounded-full px-3 py-1.5">
                    {{ $pending['label'] }}
                    <span class="text-slate-400">{{ $pending['module'] }}</span>
                </span>
            @endforeach
        </div>
    </div>
</x-layouts.admin>