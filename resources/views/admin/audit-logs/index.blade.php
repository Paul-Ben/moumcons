{{-- Admin → Audit Logs: read-only, filterable trail (PRD §31/§32). --}}
<x-layouts.admin title="Audit Logs">
    <div class="space-y-6">

        {{-- Page header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal">Audit Logs</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Append-only record of administrative actions — {{ number_format($logs->total()) }} entries.
                </p>
            </div>
            <span class="badge bg-emerald-50 text-emerald-700 inline-flex items-center gap-1.5">
                <x-icon name="shield-check" class="w-4 h-4" /> Tamper-evident trail
            </span>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="card p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2">
                    <label for="q" class="form-label sr-only">Search</label>
                    <div class="relative">
                        <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Search description, action or user…"
                               class="form-input pl-9 text-sm">
                    </div>
                </div>

                <div>
                    <label for="action" class="form-label sr-only">Category</label>
                    <select id="action" name="action" class="form-input text-sm">
                        <option value="">All categories</option>
                        @foreach ($actionGroups as $group)
                            <option value="{{ $group }}" @selected(($filters['action'] ?? '') === $group)>
                                {{ Str::headline($group) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="user" class="form-label sr-only">User</label>
                    <select id="user" name="user" class="form-input text-sm">
                        <option value="">All users</option>
                        @foreach ($actors as $id => $actor)
                            <option value="{{ $id }}" @selected((int) ($filters['user'] ?? 0) === $id)>{{ $actor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-input text-sm" aria-label="From date">
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-input text-sm" aria-label="To date">
                </div>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <button type="submit" class="btn-primary text-sm inline-flex items-center gap-2">
                    <x-icon name="sliders-horizontal" class="w-4 h-4" /> Apply filters
                </button>
                @if (collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty())
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn-ghost text-sm">Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">When</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3"><span class="sr-only">Detail</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500" title="{{ $log->created_at }}">
                                    {{ $log->created_at->format('d M Y') }}<br>
                                    <span class="text-xs">{{ $log->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-moaum-charcoal">
                                    {{ $log->user?->name ?? 'System' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-badge :color="match (true) {
                                        str_starts_with($log->action, 'auth.login_failed') => 'danger',
                                        str_ends_with($log->action, '.deleted') => 'red',
                                        str_contains($log->action, '.') => 'blue',
                                        default => 'slate',
                                    }">{{ $log->actionLabel() }}</x-badge>
                                </td>
                                <td class="px-4 py-3 text-slate-600 max-w-xl">{{ $log->description }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-400 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.audit-logs.show', $log) }}"
                                       class="text-moaum-blue hover:underline font-medium">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                    <x-icon name="shield-check" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                    No audit entries match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
