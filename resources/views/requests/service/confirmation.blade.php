{{-- PRD §12 — service request confirmation (Flow A step 7). --}}
<x-layouts.public title="Request Submitted | {{ config('moaum.company.short_name') }}"
                  meta-description="Your service request has been received. Keep your reference number to track progress.">

<section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="card p-10 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mb-6">
            <x-icon name="check-circle" class="w-8 h-8 text-moaum-green" />
        </div>

        <h1 class="font-display text-3xl font-bold text-moaum-charcoal mb-2">Service Request Received</h1>
        <p class="text-slate-600 mb-8">A confirmation email is on its way to <strong class="text-moaum-charcoal">{{ $request->email }}</strong>.</p>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-8">
            <p class="text-sm text-slate-500 mb-1">Your reference number</p>
            <p class="font-mono text-2xl font-bold text-moaum-charcoal tracking-wide">{{ $request->reference }}</p>
        </div>

        <p class="text-sm text-slate-600 mb-8">Keep this reference and the email address you used — you can check your request's progress at any time.</p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <x-button href="{{ route('requests.track', ['reference' => $request->reference]) }}">Track This Request <x-icon name="arrow-right" class="h-4 w-4" /></x-button>
            <x-button href="{{ route('home') }}" variant="outline">Back to Home</x-button>
        </div>
    </div>
</section>

</x-layouts.public>
