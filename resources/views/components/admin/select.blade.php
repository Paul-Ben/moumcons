@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false])

{{-- $options is value => label. Enum values are compared as strings. --}}
@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name));
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $current = old($key, $value instanceof \BackedEnum ? $value->value : $value);
@endphp

<div>
    <label for="{{ $id }}" class="form-label">
        {{ $label }} @if ($required)<span class="text-moaum-red" aria-hidden="true">*</span>@endif
    </label>
    <select id="{{ $id }}" name="{{ $name }}" @required($required)
            @error($key) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror
            {{ $attributes->except('id')->merge(['class' => 'form-input text-sm']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @error($key) <p id="{{ $id }}-error" class="form-error">{{ $message }}</p> @enderror
</div>
