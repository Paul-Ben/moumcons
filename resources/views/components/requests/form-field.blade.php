{{-- Shared field for the public request forms (Design System §25). --}}
@props([
    'name',
    'label',
    'type' => 'text',
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'extraValue' => null,
])

@php
    $value = old($name) ?? $extraValue ?? '';
@endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1">
        {{ $label }} @if($required)<span class="text-moaum-red">*</span>@endif
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="5" @required($required) placeholder="{{ $placeholder ?? '' }}"
                  class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-moaum-blue">{{ $value }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" @required($required) placeholder="{{ $placeholder ?? '' }}"
               class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-moaum-blue">
    @endif

    @if ($help)<p class="text-xs text-slate-500 mt-1">{{ $help }}</p>@endif
    @error($name)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>
