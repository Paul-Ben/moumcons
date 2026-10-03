{{--
    Module 5 — Business Directory listing (/businesses).
    Structure mirrors the portfolio section of prototype-docs/index.html,
    extended with search + category filtering per PRD §9.
--}}
<x-layouts.public title="Our Businesses | {{ config('moaum.company.short_name') }}"
                  meta-description="Explore the business divisions of MOAUM Consultancy Services Limited — search by name or sector.">

    {{-- Page header --}}
    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium">Our Businesses</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Our Business Portfolio</h1>
            <p class="text-lg text-slate-600 max-w-2xl">
                {{ config('moaum.company.name') }} operates diversified business divisions spanning
                technology, agriculture, construction, education and hospitality.
            </p>
        </x-container>
    </section>

    {{-- Filters + grid --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Search --}}
        <form method="GET" action="{{ route('businesses.index') }}" class="mb-6">
            @if ($activeCategory)
                <input type="hidden" name="category" value="{{ $activeCategory }}">
            @endif
            <label for="business-search" class="sr-only">Search businesses</label>
            <div class="relative max-w-xl">
                <x-icon name="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" />
                <input id="business-search" type="search" name="q" value="{{ $search ?? '' }}"
                       placeholder="Search divisions (e.g. printing, AI, catering)…"
                       class="w-full pl-12 pr-4 py-3 rounded-lg border border-slate-300 focus:border-moaum-blue focus:ring-2 focus:ring-moaum-blue/20 outline-none transition">
            </div>
        </form>

        {{-- Category chips --}}
        <div class="flex flex-wrap gap-2 mb-10">
            <a href="{{ route('businesses.index', array_filter(['q' => $search])) }}"
               class="px-4 py-2 rounded-full text-sm font-semibold transition border
                      {{ !$activeCategory ? 'bg-moaum-charcoal text-white border-moaum-charcoal' : 'bg-white text-slate-600 border-slate-300 hover:border-moaum-blue hover:text-moaum-blue' }}">
                All Divisions
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('businesses.index', array_filter(['category' => $category, 'q' => $search])) }}"
                   class="px-4 py-2 rounded-full text-sm font-semibold transition border
                          {{ $activeCategory === $category ? 'bg-moaum-charcoal text-white border-moaum-charcoal' : 'bg-white text-slate-600 border-slate-300 hover:border-moaum-blue hover:text-moaum-blue' }}">
                    {{ $category }}
                </a>
            @endforeach
        </div>

        {{-- Results --}}
        @if ($divisions->count())
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($divisions as $division)
                    <x-business-card :division="$division" />
                @endforeach
            </div>

            <div class="mt-12">
                {{ $divisions->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                <x-icon name="building" class="w-12 h-12 text-slate-300 mx-auto mb-4" />
                <h3 class="font-display text-xl font-bold text-moaum-charcoal mb-2">No divisions found</h3>
                <p class="text-slate-600 mb-6">
                    {{ $search ? 'Nothing matches “' . $search . '”' : 'This category has no divisions yet' }}.
                </p>
                <x-button href="{{ route('businesses.index') }}" variant="outline">Clear filters</x-button>
            </div>
        @endif
    </section>
</x-layouts.public>
