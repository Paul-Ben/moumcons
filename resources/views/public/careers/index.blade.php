{{-- PRD §17 — careers. --}}
<x-layouts.public title="Careers | {{ config('moaum.company.short_name') }}"
                  meta-description="Current job openings at MOAUM Consultancy Services Limited.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">Careers</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Careers at MOAUM</h1>
            <p class="text-lg text-slate-600 max-w-2xl">Join a growing, diversified enterprise backed by the University.</p>
        </x-container>
    </section>

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @forelse ($jobs as $job)
            <a href="{{ route('careers.show', $job) }}" class="group flex flex-wrap sm:flex-nowrap items-center gap-4 p-6 mb-4 bg-white border border-slate-200 rounded-2xl hover:border-moaum-blue hover:shadow-md transition">
                <div class="flex-1 min-w-0">
                    <h2 class="font-display text-xl font-bold text-moaum-charcoal group-hover:text-moaum-blue transition">{{ $job->title }}</h2>
                    @if ($job->summary)
                        <p class="text-slate-600 mt-1">{{ $job->summary }}</p>
                    @endif
                    <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
                        <span>{{ $job->employment_type->label() }}</span>
                        @if ($job->location)
                            <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="w-4 h-4" /> {{ $job->location }}</span>
                        @endif
                        @if ($job->division)
                            <span>{{ $job->division->name }}</span>
                        @endif
                        @if ($job->application_deadline)
                            <span class="inline-flex items-center gap-1"><x-icon name="calendar" class="w-4 h-4" /> Apply by {{ $job->application_deadline->format('j M Y') }}</span>
                        @endif
                    </p>
                </div>
                <x-icon name="chevron-right" class="w-6 h-6 text-slate-400 group-hover:text-moaum-blue shrink-0" />
            </a>
        @empty
            <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <x-icon name="briefcase" class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-600">There are no open positions right now. Please check back soon.</p>
            </div>
        @endforelse
    </section>
</x-layouts.public>
