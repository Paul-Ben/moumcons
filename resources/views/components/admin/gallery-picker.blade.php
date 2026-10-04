@props(['name' => 'gallery', 'label' => 'Gallery', 'items' => [], 'hint' => null])

{{--
    Ordered images with captions, chosen from the media library. Submits
    {name}[i][image] and {name}[i][caption]. $items: list of ['image', 'caption'].
--}}
@php
    $user = auth()->user();
    $canUpload = $user?->can('upload-media');
    $rows = old($name, $items);
@endphp

<div class="card space-y-4"
     x-data="galleryPicker({ items: @js(array_values($rows ?? [])), libraryUrl: @js(route('admin.media.index')), uploadUrl: @js($canUpload ? route('admin.media.store') : null) })">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="font-semibold text-moaum-charcoal">{{ $label }}</h2>
            @if ($hint)
                <p class="text-xs text-slate-400 mt-0.5">{{ $hint }}</p>
            @endif
        </div>
        @can('view-media')
            <button type="button" class="btn-ghost text-sm border border-slate-200" @click="show()">
                <x-icon name="plus" class="w-4 h-4" /> Add images
            </button>
        @endcan
    </div>

    {{-- Lets the server tell "removed every image" apart from "field not on the form". --}}
    <input type="hidden" name="{{ $name }}_submitted" value="1">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <template x-for="(row, index) in items" :key="row.image">
            <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                <img :src="row.image" alt="" class="w-full aspect-[4/3] object-cover">
                <div class="p-2 space-y-2">
                    <input type="hidden" :name="`{{ $name }}[${index}][image]`" :value="row.image">
                    <input type="text" :name="`{{ $name }}[${index}][caption]`" x-model="row.caption" maxlength="255" placeholder="Caption"
                           class="form-input h-9 text-xs" :aria-label="`Caption for image ${index + 1}`">
                    <div class="flex items-center gap-1">
                        <button type="button" class="btn-ghost p-1.5" @click="move(index, -1)" :disabled="index === 0" aria-label="Move earlier">
                            <x-icon name="chevron-right" class="w-4 h-4 rotate-180" />
                        </button>
                        <button type="button" class="btn-ghost p-1.5" @click="move(index, 1)" :disabled="index === items.length - 1" aria-label="Move later">
                            <x-icon name="chevron-right" class="w-4 h-4" />
                        </button>
                        <button type="button" class="btn-ghost p-1.5 text-moaum-red ml-auto" @click="remove(index)" aria-label="Remove image">
                            <x-icon name="trash" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <p x-show="items.length === 0" class="text-sm text-slate-400">No images added.</p>
    @error($name) <p class="form-error">{{ $message }}</p> @enderror
    @error($name.'.*.image') <p class="form-error">{{ $message }}</p> @enderror

    <x-admin.media-library-modal :can-upload="$canUpload" multiple />
</div>
