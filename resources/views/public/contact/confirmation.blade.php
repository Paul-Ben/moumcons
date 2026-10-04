@use('Illuminate\Support\Str')
{{-- PRD §21 — enquiry sent confirmation (Flow C). --}}
<x-layouts.public title="Enquiry Sent | {{ config('moaum.company.short_name') }}"
                  meta-description="Your enquiry has been received by MOAUM Consultancy Services.">

<section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="card p-10 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mb-6">
            <x-icon name="check-circle" class="w-8 h-8 text-moaum-green" />
        </div>

        <h1 class="font-display text-3xl font-bold text-moaum-charcoal mb-2">Enquiry Received</h1>
        <p class="text-slate-600 mb-8">
            Thanks{{ $enquiry->name ? ', '.$enquiry->name : '' }} — a confirmation is on its way to
            <strong class="text-moaum-charcoal">{{ $enquiry->email }}</strong>.
        </p>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-6">
            <p class="text-sm text-slate-500 mb-1">Your reference number</p>
            <p class="font-mono text-2xl font-bold text-moaum-charcoal tracking-wide">{{ $enquiry->reference }}</p>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-8 text-left">
            <p class="text-sm font-semibold text-moaum-charcoal mb-1">{{ $enquiry->subject }}</p>
            <p class="text-sm text-slate-600 whitespace-pre-line">{{ Str::limit($enquiry->message, 280) }}</p>
            @if ($enquiry->division)
                <p class="text-xs text-slate-400 mt-3">Regarding: {{ $enquiry->division->name }}</p>
            @endif
        </div>

        <p class="text-sm text-slate-600 mb-8">
            Quote your reference in any reply so we can find your enquiry quickly.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <x-button href="{{ route('businesses.index') }}">Explore Our Businesses <x-icon name="arrow-right" class="h-4 w-4" /></x-button>
            <x-button href="{{ route('home') }}" variant="outline">Back to Home</x-button>
        </div>
    </div>
</section>

</x-layouts.public>