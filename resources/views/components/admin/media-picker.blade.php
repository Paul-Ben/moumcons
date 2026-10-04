@props(['name', 'label', 'value' => null, 'hint' => null])

{{--
    Image field backed by the media library: stores the chosen image's
    site-relative URL. Staff without upload rights can still pick existing
    images; those without view-media get a plain URL field.
--}}
@php
    $id = str_replace(['[', ']', '.'], '_', $name);
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $current = old($key, $value);
    $user = auth()->user();
    $canBrowse = $user?->can('view-media');
    $canUpload = $user?->can('upload-media');
@endphp

<div>
    <span class="form-label" id="{{ $id }}-label">{{ $label }}</span>

    @if (! $canBrowse)
        <input id="{{ $id }}" name="{{ $name }}" type="text" value="{{ $current }}" class="form-input text-sm" aria-labelledby="{{ $id }}-label">
    @else
        <div x-data="mediaPicker({ value: @js($current ?? ''), libraryUrl: @js(route('admin.media.index')), uploadUrl: @js($canUpload ? route('admin.media.store') : null) })"
             class="space-y-2">
            <input type="hidden" name="{{ $name }}" :value="value">

            <div class="flex items-center gap-3">
                <div class="w-28 h-20 rounded-lg border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center shrink-0">
                    <template x-if="value"><img :src="value" alt="" class="w-full h-full object-cover"></template>
                    <template x-if="!value"><x-icon name="layout-grid" class="w-6 h-6 text-slate-300" /></template>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="show()" class="btn-ghost text-sm border border-slate-200" aria-describedby="{{ $id }}-label">
                        Choose image
                    </button>
                    <button type="button" x-show="value" @click="clear()" class="btn-ghost text-sm text-moaum-red">Remove</button>
                </div>
            </div>

            <x-admin.media-library-modal :can-upload="$canUpload" />
        </div>
    @endif

    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @error($key) <p class="form-error">{{ $message }}</p> @enderror
</div>
