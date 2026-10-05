{{-- PRD §19 — photo gallery albums. --}}
<x-layouts.public title="Gallery | {{ config('moaum.company.short_name') }}"
                  meta-description="Photos from MOAUM Consultancy Services — corporate events, business divisions, projects and training.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">Gallery</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Gallery</h1>
            <p class="text-lg text-slate-600 max-w-2xl">Moments from across MOAUM's work and events.</p>
        </x-container>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <nav aria-label="Album types" class="flex flex-wrap gap-2 mb-10">
            <a href="{{ route('gallery.index') }}" @class(['px-4 py-2 rounded-full text-sm font-medium border transition',
                'bg-moaum-charcoal text-white border-moaum-charcoal' => ! $activeType,
                'bg-white text-slate-600 border-slate-200 hover:border-slate-400' => $activeType])>All</a>
            @foreach ($types as $value => $label)
                <a href="{{ route('gallery.index', ['type' => $value]) }}" @class(['px-4 py-2 rounded-full text-sm font-medium border transition',
                    'bg-moaum-charcoal text-white border-moaum-charcoal' => $activeType === $value,
                    'bg-white text-slate-600 border-slate-200 hover:border-slate-400' => $activeType !== $value])>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($galleries->isEmpty())
            <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <x-icon name="image" class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-600">No albums to show yet.</p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($galleries as $gallery)
                    <a href="{{ route('gallery.show', $gallery) }}" class="group block rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-card hover:shadow-lg transition">
                        <div class="aspect-[4/3] bg-slate-100 overflow-hidden">
                            @if ($cover = $gallery->coverUrl())
                                <img src="{{ $cover }}" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 motion-reduce:transform-none">
                            @endif
                        </div>
                        <div class="p-5">
                            <p class="text-xs text-slate-500 mb-1">{{ $gallery->type->label() }}{{ $gallery->event_date ? ' · '.$gallery->event_date->format('M Y') : '' }}</p>
                            <h2 class="font-display text-lg font-bold text-moaum-charcoal group-hover:text-moaum-blue transition">{{ $gallery->title }}</h2>
                            <p class="text-sm text-slate-500 mt-1">{{ $gallery->images_count }} {{ Str::plural('photo', $gallery->images_count) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            @if ($galleries->hasPages())
                <div class="mt-12">{{ $galleries->links() }}</div>
            @endif
        @endif
    </section>
</x-layouts.public>
