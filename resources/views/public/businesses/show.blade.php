{{--
    Module 5 — Division detail page.
    Faithful to prototype-docs/business-detail.html: dark hero + breadcrumbs,
    overview, key capabilities grid, services list, sticky contact sidebar.
--}}
@php
    $contactEmail = $division->contact_email ?: \App\Models\Setting::get('contact.email', config('moaum.company.email'));
    $contactPhone = $division->contact_phone ?: \App\Models\Setting::get('contact.phone', null);
    $location     = $division->location ?: \App\Models\Setting::get('contact.address', 'University Campus, Makurdi, Benue State');
    $hours        = $division->operating_hours ?: \App\Models\Setting::get('contact.hours', config('moaum.company.hours'));
@endphp

<x-layouts.public :title="($division->seo_title ?: $division->name . ' | ' . config('moaum.company.short_name'))"
                  :meta-description="$division->seo_description ?: $division->short_description">

    {{-- Division hero --}}
    <section class="relative bg-moaum-charcoal py-20 lg:py-28">
        <div class="absolute inset-0">
            @if ($division->hero_image)
                <img src="{{ $division->hero_image }}" alt="{{ $division->name }}" class="w-full h-full object-cover opacity-30">
            @else
                <div class="w-full h-full bg-gradient-to-br from-moaum-charcoal via-slate-800 to-moaum-charcoal opacity-60"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-moaum-charcoal via-moaum-charcoal/80 to-transparent"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumbs --}}
            <nav class="flex text-sm text-slate-400 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('businesses.index') }}" class="hover:text-white">Our Businesses</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $division->name }}</span>
            </nav>

            <span class="inline-block {{ $division->isAvailable() ? 'bg-moaum-blue' : 'bg-slate-500' }} text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-4">
                {{ $division->status->label() }} Division
            </span>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-white mb-4 max-w-3xl">{{ $division->name }}</h1>
            <p class="text-xl text-slate-300 max-w-2xl">{{ $division->short_description }}</p>
        </div>
    </section>

    {{-- Main content layout (2 cols + sidebar) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid lg:grid-cols-3 gap-12">

            {{-- Left column: content --}}
            <div class="lg:col-span-2 space-y-12">

                {{-- Overview --}}
                <div>
                    <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">Overview</h2>
                    @foreach (preg_split('/\R{2}/', (string) $division->full_description) as $paragraph)
                        @if (trim($paragraph) !== '')
                            <p class="text-slate-600 leading-relaxed mb-4">{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>

                {{-- Key Capabilities --}}
                @if ($division->capabilities->count())
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-6">Key Capabilities</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach ($division->capabilities as $capability)
                                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-lg">
                                    <x-icon name="check-circle" class="w-6 h-6 text-moaum-blue flex-shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="font-semibold text-moaum-charcoal">{{ $capability->title }}</h4>
                                        @if ($capability->description)
                                            <p class="text-sm text-slate-600">{{ $capability->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Services Offered --}}
                <div>
                    <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-6">Services Offered</h2>
                    @if ($division->services->count())
                        <div class="space-y-4">
                            @foreach ($division->services as $service)
                                <a href="{{ route('services.show', $service) }}"
                                   class="block p-5 border border-slate-200 rounded-lg hover:border-moaum-blue hover:shadow-md transition group">
                                    <div class="flex justify-between items-center gap-4">
                                        <div>
                                            <h4 class="font-semibold text-moaum-charcoal group-hover:text-moaum-blue transition">{{ $service->name }}</h4>
                                            <p class="text-sm text-slate-600 mt-1">
                                                {{ $service->short_description }}
                                                @if ($priceLabel = $service->pricing_type?->priceLabel())
                                                    <span class="text-slate-400">• {{ $priceLabel }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <x-icon name="chevron-right" class="w-5 h-5 text-slate-400 group-hover:text-moaum-blue flex-shrink-0" />
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 bg-slate-50 border border-dashed border-slate-300 rounded-lg p-5">
                            Service details for this division are being finalised — contact us to discuss your requirement.
                        </p>
                    @endif
                </div>

                {{-- Related divisions --}}
                @if ($related->count())
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-6">Related Divisions</h2>
                        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach ($related as $other)
                                <x-business-card :division="$other" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right column: sticky contact sidebar --}}
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                        <h3 class="font-display font-bold text-moaum-charcoal mb-4">Division Contact</h3>
                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <x-icon name="mail" class="w-5 h-5 text-moaum-red mt-0.5 flex-shrink-0" />
                                <a href="mailto:{{ $contactEmail }}" class="text-slate-600 hover:text-moaum-red">{{ $contactEmail }}</a>
                            </div>
                            @if ($contactPhone)
                                <div class="flex items-start gap-3">
                                    <x-icon name="phone" class="w-5 h-5 text-moaum-red mt-0.5 flex-shrink-0" />
                                    <span class="text-slate-600">{{ $contactPhone }}</span>
                                </div>
                            @endif
                            <div class="flex items-start gap-3">
                                <x-icon name="map-pin" class="w-5 h-5 text-moaum-red mt-0.5 flex-shrink-0" />
                                <span class="text-slate-600">{{ $location }}</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <x-icon name="clock" class="w-5 h-5 text-moaum-red mt-0.5 flex-shrink-0" />
                                <span class="text-slate-600">{{ $hours }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-200 space-y-3">
                            @if ($division->isAvailable())
                                <x-button :href="route('requests.service.create', ['division' => $division->slug])" class="!block w-full">
                                    Request a Service
                                </x-button>
                                <x-button :href="route('contact.index')" variant="outline" class="!block w-full !bg-white">
                                    Ask a Question
                                </x-button>
                            @else
                                <p class="text-sm text-slate-500 text-center">This division is coming soon — check back shortly.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Track-an-existing-request helper card --}}
                    <div class="bg-white rounded-xl p-6 border border-slate-200">
                        <h3 class="font-display font-bold text-moaum-charcoal mb-2">Already requested?</h3>
                        <p class="text-sm text-slate-600 mb-4">Track any service or quote request with your reference number.</p>
                        <a href="{{ route('requests.track') }}" class="text-moaum-blue font-semibold text-sm inline-flex items-center hover:underline">
                            Track status <x-icon name="arrow-right" class="w-4 h-4 ml-1" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
