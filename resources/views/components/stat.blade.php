@props(['value', 'label', 'color' => 'red'])

{{-- Trust-banner statistic — Design System §21 (large numbers, concise labels) --}}
<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <div class="text-4xl font-display font-bold text-moaum-{{ $color }} mb-2">{{ $value }}</div>
    <p class="text-slate-300">{{ $label }}</p>
</div>
