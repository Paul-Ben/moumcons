@props(['programme'])

{{-- Training programme card (PRD §15) for /training and the home spotlight. --}}
<a href="{{ route('training.show', $programme) }}"
   {{ $attributes->merge(['class' => 'group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-card hover:shadow-lg hover:-translate-y-0.5 transition']) }}>
    <div class="aspect-[16/9] bg-slate-100 overflow-hidden relative">
        @if ($programme->featured_image)
            <img src="{{ $programme->featured_image }}" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 motion-reduce:transform-none">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-moaum-blue/10 to-slate-100">
                <x-icon name="graduation-cap" class="w-12 h-12 text-moaum-blue/40" />
            </div>
        @endif
        <span class="absolute top-3 left-3 badge bg-white/95 text-moaum-charcoal shadow-sm">{{ $programme->delivery_mode->label() }}</span>
    </div>
    <div class="p-6 flex-1 flex flex-col">
        @if ($programme->course_category)
            <p class="text-xs font-semibold text-moaum-red uppercase tracking-wide mb-2">{{ $programme->course_category }}</p>
        @endif
        <h3 class="font-display text-lg font-bold text-moaum-charcoal group-hover:text-moaum-blue transition mb-2">{{ $programme->title }}</h3>
        @if ($programme->summary)
            <p class="text-sm text-slate-600 line-clamp-2 mb-4">{{ $programme->summary }}</p>
        @endif
        <dl class="mt-auto grid grid-cols-2 gap-3 text-xs text-slate-500 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-1.5"><x-icon name="calendar" class="w-4 h-4 shrink-0" /><dt class="sr-only">Dates</dt><dd>{{ $programme->dateRange() ?? 'Dates TBC' }}</dd></div>
            <div class="flex items-center gap-1.5"><x-icon name="clock" class="w-4 h-4 shrink-0" /><dt class="sr-only">Duration</dt><dd>{{ $programme->duration ?: '—' }}</dd></div>
            <div class="col-span-2 flex items-center justify-between">
                <dt class="sr-only">Fee</dt><dd class="font-semibold text-moaum-charcoal text-sm">{{ $programme->feeLabel() }}</dd>
                <span class="badge {{ $programme->status->badgeClasses() }}">{{ $programme->status->label() }}</span>
            </div>
        </dl>
    </div>
</a>
