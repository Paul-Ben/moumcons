@props(['color' => 'red'])

@php
    $map = [
        'red' => 'bg-moaum-red/10 text-moaum-red',
        'blue' => 'bg-moaum-blue/10 text-moaum-blue',
        'green' => 'bg-moaum-green/10 text-moaum-green',
        'solid-blue' => 'bg-moaum-blue text-white',
        'solid-red' => 'bg-moaum-red text-white',
        'slate' => 'bg-slate-100 text-slate-700',
        'warning' => 'bg-warning/10 text-warning',
        'danger' => 'bg-danger/10 text-danger',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . ($map[$color] ?? $map['red'])]) }}>{{ $slot }}</span>
