@props(['item'])

{{--
    Admin sidebar link. Items without an href belong to a module that has not
    shipped, so they render as disabled text with the module name rather than a
    dead link.
--}}
@php
    $active = $item['is_active'] ?? false;
    $href = $item['href'] ?? null;
    $base = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-left w-full';
    $tones = $active
        ? 'bg-moaum-blue/20 text-white font-medium'
        : ($href ? 'text-slate-300 hover:bg-slate-800 hover:text-white transition' : 'text-slate-500 cursor-default');
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($active) aria-current="page" @endif
       class="{{ $base }} {{ $tones }}">
        <x-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
        <span class="truncate">{{ $item['label'] }}</span>
        @if (! empty($item['count']))
            <span class="ml-auto bg-moaum-red text-white text-xs font-bold px-2 py-0.5 rounded-full shrink-0">{{ $item['count'] }}</span>
        @endif
    </a>
@else
    <span class="{{ $base }} {{ $tones }}" aria-disabled="true">
        <x-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
        <span class="truncate">{{ $item['label'] }}</span>
        <span class="ml-auto flex items-center gap-2 shrink-0">
            @if (! empty($item['count']))
                <span class="bg-slate-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $item['count'] }}</span>
            @endif
            <span class="text-[10px] uppercase tracking-wide text-slate-600">{{ $item['pending'] ?? '' }}</span>
        </span>
    </span>
@endif