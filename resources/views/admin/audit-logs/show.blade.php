{{-- Admin → Audit Logs: single entry detail incl. property diff (PRD §31/§32). --}}
@use('Illuminate\Support\Str')
<x-layouts.admin title="Audit Entry">
    <div class="space-y-6">

        {{-- Page header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-moaum-blue transition">
                    <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> Back to audit logs
                </a>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal mt-2">
                    {{ $log->actionLabel() }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Entry #{{ $log->id }} &middot; {{ $log->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            <x-badge :color="match (true) {
                str_starts_with($log->action, 'auth.login_failed') => 'danger',
                str_ends_with($log->action, '.deleted') => 'red',
                str_contains($log->action, '.') => 'blue',
                default => 'slate',
            }">{{ $log->action }}</x-badge>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Summary --}}
            <div class="card lg:col-span-2 space-y-4">
                <h2 class="font-semibold text-moaum-charcoal">Summary</h2>
                <p class="text-slate-600">{{ $log->description ?: 'No description recorded.' }}</p>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Performed by</dt>
                        <dd class="mt-1 text-moaum-charcoal font-medium">
                            {{ $log->user?->name ?? 'System' }}
                            @if ($log->user)
                                <span class="block text-xs font-normal text-slate-400">{{ $log->user->email }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Subject</dt>
                        <dd class="mt-1 text-moaum-charcoal font-medium">
                            {{ $log->subject_type ? $log->subjectLabel() : '—' }}
                            @if ($log->subject)
                                <span class="block text-xs font-normal text-slate-400">{{ class_basename($log->subject_type) }} record</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">IP address</dt>
                        <dd class="mt-1 font-mono text-slate-600">{{ $log->ip_address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Recorded at</dt>
                        <dd class="mt-1 text-slate-600" title="{{ $log->created_at->toIso8601String() }}">
                            {{ $log->created_at->format('d M Y, H:i:s') }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Context --}}
            <div class="space-y-6">
                <div class="card">
                    <h2 class="font-semibold text-moaum-charcoal mb-3">Request context</h2>
                    <p class="text-sm text-slate-600 break-words">
                        {{ $log->user_agent ?? 'No user agent recorded.' }}
                    </p>
                </div>

                @if (filled($log->properties))
                    <div class="card">
                        <h2 class="font-semibold text-moaum-charcoal mb-3">Properties</h2>
                        @foreach ($log->properties as $key => $value)
                            @continue($key === 'changes')
                            <div class="text-sm border-b border-slate-100 last:border-0 py-2 first:pt-0">
                                <span class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ Str::headline((string) $key) }}
                                </span>
                                <span class="text-slate-600 break-words">
                                    {{ is_scalar($value) ? $value : json_encode($value) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Change diff --}}
        @php($changes = $log->changesSummary())
        @if (filled($changes))
            <div class="card overflow-hidden p-0">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="font-semibold text-moaum-charcoal">Recorded changes</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Field</th>
                                <th class="px-4 py-3">From</th>
                                <th class="px-4 py-3">To</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($changes as $change)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-moaum-charcoal whitespace-nowrap">{{ $change['field'] }}</td>
                                    <td class="px-4 py-3 text-slate-500 line-through break-words">{{ $change['from'] }}</td>
                                    <td class="px-4 py-3 text-slate-700 break-words">{{ $change['to'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card text-sm text-slate-500">
                No field-level changes were recorded for this entry.
            </div>
        @endif
    </div>
</x-layouts.admin>