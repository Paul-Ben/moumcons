@props(['name', 'label', 'value' => null, 'hint' => null, 'required' => false])

{{--
    Trix editor. Output is sanitised server-side by App\Casts\RichTextCast;
    images dropped in are uploaded to the media library when the user may
    upload media.
--}}
@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name));
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $uploadUrl = auth()->user()?->can('upload-media') ? route('admin.media.store') : null;
@endphp

<div>
    <label for="{{ $id }}-editor" class="form-label">
        {{ $label }} @if ($required)<span class="text-moaum-red" aria-hidden="true">*</span>@endif
    </label>
    <input id="{{ $id }}" type="hidden" name="{{ $name }}" value="{{ old($key, $value) }}">
    <trix-editor id="{{ $id }}-editor" input="{{ $id }}" @if ($uploadUrl) data-upload-url="{{ $uploadUrl }}" @endif
                 @error($key) aria-invalid="true" @enderror
                 class="trix-content prose-moaum block w-full min-h-48 rounded-[10px] border border-slate-300 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-moaum-blue"></trix-editor>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @error($key) <p class="form-error">{{ $message }}</p> @enderror
</div>
