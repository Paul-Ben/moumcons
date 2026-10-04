@props(['trail'])

{{-- Audit entries recorded against one record (PRD §32), newest first. --}}
<div {{ $attributes->merge(['class' => 'card overflow-hidden p-0']) }}>
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-moaum-charcoal">Activity</h2>
        @can('view-audit-logs')
            <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-moaum-blue hover:underline">
                Full audit log
            </a>
        @endcan
    </div>
    @forelse ($trail as $entry)
        <div class="px-4 py-3 border-b border-slate-100 last:border-0 flex items-start gap-3">
            <x-badge :color="str_contains($entry->action, '.') ? 'blue' : 'slate'">{{ $entry->action }}</x-badge>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-slate-700 break-words">{{ $entry->description }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $entry->user?->name ?? 'System' }} &middot;
                    {{ $entry->created_at->format('d M Y, H:i') }}
                </p>
            </div>
        </div>
    @empty
        <p class="px-4 py-6 text-sm text-slate-500">No recorded activity yet.</p>
    @endforelse
</div>
