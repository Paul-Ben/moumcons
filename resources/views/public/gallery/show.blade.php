{{-- PRD §19 — one photo album. --}}
<x-layouts.public :title="$gallery->title.' | Gallery | '.config('moaum.company.short_name')"
                  :meta-description="$gallery->description ?: 'Photos: '.$gallery->title"
                  :og-image="$gallery->coverUrl()">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-14 lg:py-16">
            <nav aria-label="Breadcrumb" class="flex flex-wrap text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('gallery.index') }}" class="hover:text-moaum-red">Gallery</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">{{ $gallery->title }}</span>
            </nav>
            <p class="eyebrow mb-2">{{ $gallery->type->label() }}{{ $gallery->event_date ? ' · '.$gallery->event_date->format('j F Y') : '' }}</p>
            <h1 class="font-display text-3xl lg:text-4xl font-bold text-moaum-charcoal mb-3">{{ $gallery->title }}</h1>
            @if ($gallery->description)
                <p class="text-lg text-slate-600 max-w-2xl">{{ $gallery->description }}</p>
            @endif
            @if ($gallery->division || $gallery->project?->isPublished())
                <p class="mt-4 flex flex-wrap gap-4 text-sm">
                    @if ($gallery->division)
                        <a href="{{ route('businesses.show', $gallery->division) }}" class="text-moaum-blue font-semibold hover:underline">{{ $gallery->division->name }}</a>
                    @endif
                    @if ($gallery->project?->isPublished())
                        <a href="{{ route('projects.show', $gallery->project) }}" class="text-moaum-blue font-semibold hover:underline">Project: {{ $gallery->project->title }}</a>
                    @endif
                </p>
            @endif
        </x-container>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <x-lightbox-gallery :images="$gallery->images" :alt="$gallery->title" />
    </section>
</x-layouts.public>
