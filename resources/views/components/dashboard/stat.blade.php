@props(['label', 'value', 'icon' => 'inbox', 'tone' => 'blue', 'hint' => null])

{{--
    Dashboard widget tile. Tone classes are written out in full because Tailwind
    scans source text for complete class names — a `bg-moaum-{{ $tone }}` template
    would never be generated.
--}}
@php
    $tones = [
        'blue' => 'bg-moaum-blue/10 text-moaum-blue',
        'red' => 'bg-moaum-red/10 text-moaum-red',
        'green' => 'bg-moaum-green/10 text-moaum-green',
        'charcoal' => 'bg-slate-100 text-moaum-charcoal',
    ];
    $toneClass = $tones[$tone] ?? $tones['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'card flex items-start gap-4']) }}>
    <span class="w-11 h-11 shrink-0 rounded-lg flex items-center justify-center {{ $toneClass }}">
        <x-icon :name="$icon" class="w-5 h-5" />
    </span>
    <div class="min-w-0">
        <p class="font-display text-3xl font-bold text-moaum-charcoal leading-none">{{ number_format($value) }}</p>
        <p class="text-sm font-medium text-slate-700 mt-1.5">{{ $label }}</p>
        @if ($hint)
            <p class="text-xs text-slate-400 mt-0.5">{{ $hint }}</p>
        @endif
    </div>
</div>