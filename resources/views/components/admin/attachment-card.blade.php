@props(['path', 'href'])

{{-- A customer upload held on the private disk; downloads are audited. --}}
<div {{ $attributes->merge(['class' => 'card flex flex-wrap items-center justify-between gap-3']) }}>
    <div class="flex items-center gap-3 min-w-0">
        <span class="w-10 h-10 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
            <x-icon name="download" class="w-5 h-5" />
        </span>
        <div class="min-w-0">
            <p class="text-sm font-medium text-moaum-charcoal truncate">{{ basename($path) }}</p>
            <p class="text-xs text-slate-400">Customer upload &mdash; private storage</p>
        </div>
    </div>
    <a href="{{ $href }}" class="btn-secondary text-sm inline-flex items-center gap-2 shrink-0">
        <x-icon name="download" class="w-4 h-4" /> Download
    </a>
</div>
