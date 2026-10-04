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

            {{-- Library modal --}}
            <div x-show="open" x-cloak @keydown.escape.window="open = false"
                 class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-label="Choose an image">
                <div @click.outside="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[85vh] flex flex-col">
                    <div class="flex flex-wrap items-center gap-3 p-4 border-b border-slate-100">
                        <h2 class="font-semibold text-moaum-charcoal mr-auto">Media library</h2>
                        <input type="search" x-model="query" @keydown.enter.prevent="search()" placeholder="Search images…"
                               class="form-input text-sm h-10 w-56" aria-label="Search images">
                        @if ($canUpload)
                            <label class="btn-secondary text-sm py-2 px-4 cursor-pointer">
                                <span x-text="uploading ? 'Uploading…' : 'Upload'"></span>
                                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="upload($event)" :disabled="uploading">
                            </label>
                        @endif
                        <button type="button" @click="open = false" class="btn-ghost p-2" aria-label="Close"><x-icon name="x" class="w-5 h-5" /></button>
                    </div>
                    <p x-show="error" x-text="error" class="form-error px-4" role="alert"></p>
                    <div class="p-4 overflow-y-auto">
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            <template x-for="item in items" :key="item.id">
                                <button type="button" @click="choose(item)" :title="item.name"
                                        class="aspect-square rounded-lg overflow-hidden border-2 transition"
                                        :class="value === item.url ? 'border-moaum-blue' : 'border-transparent hover:border-slate-300'">
                                    <img :src="item.thumb" :alt="item.alt || item.name" class="w-full h-full object-cover" loading="lazy">
                                </button>
                            </template>
                        </div>
                        <p x-show="!loading && items.length === 0" class="text-sm text-slate-500 text-center py-10">No images yet.</p>
                        <p x-show="loading" class="text-sm text-slate-400 text-center py-4">Loading…</p>
                        <div x-show="next && !loading" class="text-center mt-4">
                            <button type="button" @click="more()" class="btn-ghost text-sm">Load more</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @error($key) <p class="form-error">{{ $message }}</p> @enderror
</div>
