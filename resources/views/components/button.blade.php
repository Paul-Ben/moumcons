@props([
    'variant' => 'primary',   // primary | secondary | outline | ghost | white
    'href' => null,
    'type' => 'button',
    'size' => 'md',           // md | lg
])

@php
    $base = match ($variant) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'outline' => 'btn-outline',
        'ghost' => 'btn-ghost',
        'white' => 'inline-flex justify-center items-center gap-2 px-6 py-3 rounded-lg font-semibold bg-white text-moaum-charcoal hover:bg-slate-100 transition',
        default => 'btn-primary',
    };

    if ($size === 'lg') {
        $base .= ' !px-8 !py-4';
    }

    $classes = $base . ' ' . ($attributes->get('class') ?? '');
    $attributes = $attributes->except('class');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
