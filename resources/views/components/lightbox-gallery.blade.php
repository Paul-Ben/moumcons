@props(['images', 'alt' => ''])

{{--
    Thumbnail grid with a keyboard-accessible lightbox (Esc closes, arrows
    navigate). $images: iterable of objects/arrays with `image` and `caption`.
--}}
@php
    $items = collect($images)->map(fn ($i) => ['src' => data_get($i, 'image'), 'caption' => data_get($i, 'caption')])->values();
@endphp

<div x-data="{ active: null, images: @js($items) }" {{ $attributes }}>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach ($items as $index => $item)
            <button type="button" @click="active = {{ $index }}" class="group block aspect-[4/3] rounded-xl overflow-hidden bg-slate-100 focus-visible:ring-2 focus-visible:ring-moaum-blue">
                <img src="{{ $item['src'] }}" alt="{{ $item['caption'] ?: trim($alt.' — image '.($index + 1), ' —') }}" loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500 motion-reduce:transform-none">
            </button>
        @endforeach
    </div>

    <div x-show="active !== null" x-cloak @keydown.escape.window="active = null"
         @keydown.arrow-right.window="if (active !== null) active = (active + 1) % images.length"
         @keydown.arrow-left.window="if (active !== null) active = (active - 1 + images.length) % images.length"
         class="fixed inset-0 z-[70] bg-black/90 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Image viewer">
        <button type="button" @click="active = null" class="absolute top-4 right-4 text-white/80 hover:text-white" aria-label="Close">
            <x-icon name="x" class="w-8 h-8" />
        </button>
        <button type="button" @click="active = (active - 1 + images.length) % images.length" class="absolute left-2 sm:left-6 text-white/80 hover:text-white p-2" aria-label="Previous image">
            <x-icon name="chevron-right" class="w-8 h-8 rotate-180" />
        </button>
        <figure class="max-w-5xl w-full" @click.outside="active = null">
            <img :src="active !== null ? images[active].src : ''" :alt="active !== null ? (images[active].caption || '') : ''" class="max-h-[80vh] mx-auto rounded-lg">
            <figcaption x-show="active !== null && images[active].caption" x-text="active !== null ? images[active].caption : ''" class="text-center text-white/80 text-sm mt-3"></figcaption>
        </figure>
        <button type="button" @click="active = (active + 1) % images.length" class="absolute right-2 sm:right-6 text-white/80 hover:text-white p-2" aria-label="Next image">
            <x-icon name="chevron-right" class="w-8 h-8" />
        </button>
    </div>
</div>
