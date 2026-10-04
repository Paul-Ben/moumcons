@props(['name', 'label', 'value' => null, 'type' => 'text', 'hint' => null, 'required' => false])

@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name));
    $key = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div>
    <label for="{{ $id }}" class="form-label">
        {{ $label }} @if ($required)<span class="text-moaum-red" aria-hidden="true">*</span>@endif
    </label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($key, $value) }}"
           @required($required) @error($key) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror
           {{ $attributes->except('id')->merge(['class' => 'form-input text-sm']) }}>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @error($key) <p id="{{ $id }}-error" class="form-error">{{ $message }}</p> @enderror
</div>
