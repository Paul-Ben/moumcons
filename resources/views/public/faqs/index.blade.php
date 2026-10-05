{{-- PRD §22 — frequently asked questions. --}}
<x-layouts.public title="FAQs | {{ config('moaum.company.short_name') }}"
                  meta-description="Answers to common questions about MOAUM Consultancy Services, our divisions and how to work with us.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">FAQs</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Frequently Asked Questions</h1>
            <p class="text-lg text-slate-600 max-w-2xl">Can't find what you need? <a href="{{ route('contact.index') }}" class="text-moaum-blue font-semibold hover:underline">Contact us</a>.</p>
        </x-container>
    </section>

    <section class="max-w-3xl mx-auto px-4 sm:px-6 py-12 space-y-14">
        @if ($general->isEmpty() && $divisional->isEmpty())
            <p class="text-center text-slate-600 py-16 bg-slate-50 rounded-2xl border border-dashed border-slate-300">No FAQs have been published yet.</p>
        @endif

        @foreach ($general as $topic => $faqs)
            <div>
                <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-2">{{ $topic }}</h2>
                <x-faq-list :faqs="$faqs" />
            </div>
        @endforeach

        @foreach ($divisional as $divisionName => $faqs)
            <div>
                <div class="flex flex-wrap items-baseline justify-between gap-2 mb-2">
                    <h2 class="font-display text-2xl font-bold text-moaum-charcoal">{{ $divisionName }}</h2>
                    <a href="{{ route('businesses.show', $faqs->first()->division) }}" class="text-sm font-semibold text-moaum-blue hover:underline">About this division</a>
                </div>
                <x-faq-list :faqs="$faqs" />
            </div>
        @endforeach
    </section>
</x-layouts.public>
