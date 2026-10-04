@props(['name', 'label', 'checked' => false, 'hint' => null])

{{-- The hidden input makes an unticked box submit "0" instead of nothing. --}}
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div>
    <label class="inline-flex items-start gap-2 text-sm text-slate-700">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($key, $checked))
               class="mt-0.5 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
        <span>
            <span class="font-medium">{{ $label }}</span>
            @if ($hint)
                <span class="block text-xs text-slate-400">{{ $hint }}</span>
            @endif
        </span>
    </label>
    @error($key) <p class="form-error">{{ $message }}</p> @enderror
</div>
