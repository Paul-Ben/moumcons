{{-- PRD §12/§13 — public status tracking by reference + email (no account needed). --}}
<x-layouts.public title="Track Your Request | {{ config('moaum.company.short_name') }}"
                  meta-description="Check the progress of your service or quote request using your reference number and email address.">

<section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
    <x-container class="py-14">
        <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4 gap-1">
            <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a><span>/</span>
            <span class="text-moaum-charcoal font-medium">Track Your Request</span>
        </nav>
        <h1 class="font-display text-4xl font-bold text-moaum-charcoal">Track Your Request</h1>
        <p class="text-slate-600 mt-2 max-w-2xl">Enter the reference number from your confirmation email together with the email address you used when submitting.</p>
    </x-container>
</section>

<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">

    @if ($result)
        <div class="card p-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Reference</p>
                    <p class="font-mono text-xl font-bold text-moaum-charcoal">{{ $result->reference }}</p>
                    <p class="text-sm text-slate-600 mt-1">{{ $isQuote ? 'Quote request' : 'Service request' }} · {{ $result->division?->name ?? '—' }}</p>
                </div>
                <span class="badge {{ $result->status->badgeClasses() }}">{{ $result->status->label() }}</span>
            </div>

            @if ($timeline = $result->statusTimeline())
                <ol class="flex flex-wrap items-center gap-x-3 gap-y-4" aria-label="Progress timeline">
                    @foreach ($timeline as $i => $step)
                        <li class="flex items-center gap-2">
                            <span aria-hidden="true"
                                  class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $step['done'] ? 'bg-moaum-green text-white' : 'bg-slate-200 text-slate-500' }}">
                                {{ $step['done'] ? '✓' : ($i + 1) }}
                            </span>
                            <span class="text-sm font-medium {{ $step['done'] ? 'text-moaum-charcoal' : 'text-slate-400' }}">{{ $step['label'] }}</span>
                        </li>
                        @if ($i < count($timeline) - 1)
                            <li aria-hidden="true" class="h-px w-5 bg-slate-200"></li>
                        @endif
                    @endforeach
                </ol>
            @endif

            {{-- PRD §13/§25 — the prepared quote, once staff have sent it. --}}
            @if ($isQuote && $result->quoteIsVisibleToCustomer())
                <div class="mt-8 rounded-xl border border-moaum-green/30 bg-moaum-green/5 p-5">
                    <p class="text-sm font-semibold text-moaum-charcoal">Your quote</p>
                    @if ($result->quoted_amount !== null)
                        <p class="font-display text-2xl font-bold text-moaum-charcoal mt-1">₦{{ number_format((float) $result->quoted_amount, 2) }}</p>
                    @endif
                    @if (filled($result->quote_message))
                        <p class="text-sm text-slate-600 whitespace-pre-line mt-2">{{ $result->quote_message }}</p>
                    @endif
                    @if ($result->quote_valid_until)
                        <p class="text-xs text-slate-500 mt-2">Valid until {{ $result->quote_valid_until->format('j F Y') }}</p>
                    @endif
                </div>
            @endif

            <p class="text-sm text-slate-500 mt-8 border-t border-slate-100 pt-5">
                @if (in_array($result->status->value, ['completed', 'closed', 'cancelled', 'accepted', 'declined', 'order_contract'], true))
                    This request has reached a final status. For anything else, <a href="{{ route('contact.index') }}" class="text-moaum-blue font-semibold hover:underline">contact us</a>.
                @else
                    Our team will update the status as your request moves forward.
                @endif
            </p>
        </div>
    @endif

    @if ($error)
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700" role="alert">
            {{ $error }}
        </div>
    @endif

    <div class="card p-8">
        <h2 class="font-semibold text-lg text-moaum-charcoal mb-5">{{ $result ? 'Check another request' : 'Check your status' }}</h2>
        <form method="POST" action="{{ route('requests.track.lookup') }}" class="grid sm:grid-cols-2 gap-5">
            @csrf
            <x-requests.form-field name="reference" label="Reference Number" :required="true"
                                   placeholder="e.g. SRQ-261004-AB12" :extra-value="$reference" />
            <x-requests.form-field name="email" label="Email Address" type="email" :required="true" placeholder="you@example.com" />
            <div class="sm:col-span-2">
                <button type="submit" class="btn-secondary w-full justify-center">Check Status</button>
            </div>
        </form>
    </div>

</section>

</x-layouts.public>
