{{-- Module 6 — Cross-division Services Catalogue (PRD §11). --}}
<x-layouts.public title="Services | {{ config('moaum.company.short_name') }}"
                  meta-description="Browse services across all MOAUM business divisions — consulting, technology, agriculture and more.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium">Services</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Our Services</h1>
            <p class="text-lg text-slate-600 max-w-2xl">
                {{ $total ?? 0 }} professional services spanning every MOAUM division — filter by sector or search for a solution.
            </p>
        </x-container>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Search + category filter --}}
        <form method="GET" action="{{ route('services.index') }}" class="mb-8 grid gap-4 md:grid-cols-[1fr_auto] items-end">
            <div>
                <label for="service-search" class="block text-sm font-medium text-slate-600 mb-1">Search services</label>
                <div class="relative">
                    <x-icon name="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" />
                    <input id="service-search" type="search" name="q" value="{{ $search ?? '' }}"
                           placeholder="e.g. training, printing, AI…"
                           class="w-full pl-12 pr-4 py-3 rounded-lg border border-slate-300 focus:border-moaum-blue focus:ring-2 focus:ring-moaum-blue/20 outline-none transition">
                </div>
            </div>
            <div class="flex gap-3">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                @endif
                <select name="category" class="py-3 px-4 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                    <option value="">All categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($activeCategory === $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary">Filter</button>
            </div>
        </form>

        @forelse ($grouped as $divisionName => $services)
            <div class="mb-12">
                <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-1">{{ $divisionName }}</h2>
                <p class="text-sm text-slate-500 mb-5">{{ $services->count() }} service{{ $services->count() === 1 ? '' : 's' }}</p>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($services as $service)
                        <a href="{{ route('services.show', $service) }}"
                           class="card p-6 hover:shadow-lg hover:-translate-y-0.5 transition block">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <h3 class="font-semibold text-lg text-moaum-charcoal leading-snug">{{ $service->name }}</h3>
                                <x-badge color="blue">{{ $service->service_type ?? 'Service' }}</x-badge>
                            </div>
                            @if ($service->short_description)
                                <p class="text-sm text-slate-600 line-clamp-3 mb-4">{{ $service->short_description }}</p>
                            @endif
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">{{ $service->pricing_type?->priceLabel() ?? 'Contact Us' }}</span>
                                <span class="text-moaum-blue font-medium">Details →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card p-12 text-center">
                <p class="text-slate-500 mb-4">No services match your search.</p>
                <a href="{{ route('services.index') }}" class="btn-secondary">Clear filters</a>
            </div>
        @endforelse
    </section>
</x-layouts.public>
