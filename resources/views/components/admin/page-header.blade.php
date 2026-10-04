@props(['title', 'description' => null, 'back' => null, 'backLabel' => 'Back'])

{{-- Title row shared by admin screens; actions go in the default slot. --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-moaum-blue transition mb-2">
                <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> {{ $backLabel }}
            </a>
        @endif
        <h1 class="font-display text-2xl font-bold text-moaum-charcoal break-words">{{ $title }}</h1>
        @if ($description)
            <p class="text-sm text-slate-500 mt-1">{{ $description }}</p>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
