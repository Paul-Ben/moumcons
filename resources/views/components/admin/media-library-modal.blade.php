@props(['canUpload' => false, 'multiple' => false])

{{--
    Library modal used inside an Alpine mediaPicker / galleryPicker scope
    (resources/js/admin.js). Clicking an image calls choose(); in multiple mode
    the modal stays open so several images can be added.
--}}
<div x-show="open" x-cloak @keydown.escape.window="open = false"
     class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-label="Choose {{ $multiple ? 'images' : 'an image' }}">
    <div @click.outside="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[85vh] flex flex-col">
        <div class="flex flex-wrap items-center gap-3 p-4 border-b border-slate-100">
            <h2 class="font-semibold text-moaum-charcoal mr-auto">Media library</h2>
            <input type="search" x-model="query" @keydown.enter.prevent="search()" placeholder="Search images…"
                   class="form-input text-sm h-10 w-56" aria-label="Search images">
            @if ($canUpload)
                <label class="btn-secondary text-sm py-2 px-4 cursor-pointer">
                    <span x-text="uploading ? 'Uploading…' : 'Upload'"></span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="upload($event)" :disabled="uploading" @if ($multiple) multiple @endif>
                </label>
            @endif
            <button type="button" @click="open = false" class="btn-ghost p-2" aria-label="Close"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <p x-show="error" x-text="error" class="form-error px-4" role="alert"></p>
        <div class="p-4 overflow-y-auto">
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                <template x-for="item in library" :key="item.id">
                    <button type="button" @click="choose(item)" :title="item.name" :aria-pressed="isSelected(item)"
                            class="relative aspect-square rounded-lg overflow-hidden border-2 transition"
                            :class="isSelected(item) ? 'border-moaum-blue' : 'border-transparent hover:border-slate-300'">
                        <img :src="item.thumb" :alt="item.alt || item.name" class="w-full h-full object-cover" loading="lazy">
                        <span x-show="isSelected(item)" class="absolute top-1 right-1 bg-moaum-blue text-white rounded-full p-0.5">
                            <x-icon name="check-circle" class="w-4 h-4" />
                        </span>
                    </button>
                </template>
            </div>
            <p x-show="!loading && library.length === 0" class="text-sm text-slate-500 text-center py-10">No images yet.</p>
            <p x-show="loading" class="text-sm text-slate-400 text-center py-4">Loading…</p>
            <div x-show="next && !loading" class="text-center mt-4">
                <button type="button" @click="more()" class="btn-ghost text-sm">Load more</button>
            </div>
        </div>
        @if ($multiple)
            <div class="p-4 border-t border-slate-100 text-right">
                <button type="button" @click="open = false" class="btn-primary text-sm">Done</button>
            </div>
        @endif
    </div>
</div>
