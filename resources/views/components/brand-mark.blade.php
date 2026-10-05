@props([
    'size' => 'md',        // sm (admin sidebar) | md (login) | lg (footer)
    'subtitle' => null,    // second line under "MOAUM"; defaults to the company line
    'href' => null,
])

{{--
    MOAUM logo lock-up for dark backgrounds. The logo is a JPEG on white, so it
    sits on a white rounded tile (a CSS invert would turn it into a white block),
    next to the wordmark.
--}}
@php
    $tile = ['sm' => 'p-1 rounded-lg', 'md' => 'p-1.5 rounded-xl', 'lg' => 'p-2 rounded-2xl'][$size] ?? 'p-1.5 rounded-xl';
    $logo = ['sm' => 'h-8', 'md' => 'h-11', 'lg' => 'h-14'][$size] ?? 'h-11';
    $name = ['sm' => 'text-base', 'md' => 'text-xl', 'lg' => 'text-2xl'][$size] ?? 'text-xl';
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="bg-white shadow-sm ring-1 ring-white/10 shrink-0 {{ $tile }}">
        <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('moaum.company.name') }}" class="{{ $logo }} w-auto">
    </span>
    <span class="flex flex-col leading-tight min-w-0">
        <span class="font-display font-extrabold tracking-tight text-white {{ $name }}">MOAUM</span>
        <span class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">{{ $subtitle ?? 'Consultancy Services Ltd' }}</span>
    </span>
</{{ $tag }}>
