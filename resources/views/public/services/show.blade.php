{{-- Module 6 — Service detail with Request-a-Service / Request-a-Quote deep links (PRD §11–13). --}}
@php
    /** @var \App\Models\Service $service */
    $requestUrl = fn (string $route) => route($route, ['service' => $service->slug]);
@endphp
<x-layouts.public :title="$service->seo_title ?: ($service->name . ' | ' . config('moaum.company.short_name'))"
                  :meta-description="$service->seo_description ?: $service->short_description">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-14 lg:py-16">
            <nav aria-label="Breadcrumb" class="flex flex-wrap text-sm text-slate-500 mb-4 gap-1">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a><span>/</span>
                <a href="{{ route('services.index') }}" class="hover:text-moaum-red">Services</a><span>/</span>
                @if ($service->division)
                    <a href="{{ route('businesses.show', $service->division) }}" class="hover:text-moaum-red">{{ $service->division->name }}</a><span>/</span>
                @endif
                <span class="text-moaum-charcoal font-medium">{{ $service->name }}</span>
            </nav>
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-3xl">
                    <h1 class="font-display text-3xl lg:text-5xl font-bold text-moaum-charcoal mb-3">{{ $service->name }}</h1>
                    @if ($service->short_description)
                        <p class="text-lg text-slate-600">{{ $service->short_description }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
                        <x-badge color="blue">{{ $service->service_type ?? 'Service' }}</x-badge>
                        <span class="text-slate-500">{{ $service->pricing_type?->priceLabel() ?? 'Contact Us' }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-3 min-w-[220px]">
                    <a href="{{ $requestUrl('requests.service.create') }}" class="btn-primary justify-center">Request this Service</a>
                    <a href="{{ $requestUrl('requests.quote.create') }}" class="btn-secondary justify-center">Request a Quote</a>
                </div>
            </div>
        </x-container>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid lg:grid-cols-[2fr_1fr] gap-10">
        <div>
            <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">Overview</h2>
            @if ($service->description)
                <x-rich-content :html="$service->description" />
            @else
                <p class="text-slate-700">Contact the {{ $service->division?->name ?? 'MOAUM' }} team to discuss how this service can support your organisation.</p>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            @if ($service->division)
                <div class="card p-6">
                    <h3 class="font-semibold text-moaum-charcoal mb-3">Provided by</h3>
                    <a href="{{ route('businesses.show', $service->division) }}" class="text-moaum-blue font-medium hover:underline">{{ $service->division->name }}</a>
                    @if ($service->division->contact_email)
                        <p class="text-sm text-slate-600 mt-3 flex items-center gap-2"><x-icon name="mail" class="w-4 h-4" />{{ $service->division->contact_email }}</p>
                    @endif
                    @if ($service->division->contact_phone)
                        <p class="text-sm text-slate-600 mt-1 flex items-center gap-2"><x-icon name="phone" class="w-4 h-4" />{{ $service->division->contact_phone }}</p>
                    @endif
                </div>
            @endif
            @if ($moreFromDivision->isNotEmpty())
                <div class="card p-6">
                    <h3 class="font-semibold text-moaum-charcoal mb-3">More from this division</h3>
                    <ul class="space-y-2">
                        @foreach ($moreFromDivision as $other)
                            <li><a href="{{ route('services.show', $other) }}" class="text-sm text-moaum-blue hover:underline">{{ $other->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </section>
</x-layouts.public>
