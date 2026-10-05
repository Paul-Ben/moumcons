{{-- PRD §15 — training programme detail + register interest. --}}
<x-layouts.public :title="($programme->seo_title ?: $programme->title).' | '.config('moaum.company.short_name')"
                  :meta-description="$programme->seo_description ?: ($programme->summary ?: \Illuminate\Support\Str::limit(\App\Support\RichText::toPlainText($programme->description), 155))"
                  :og-image="$programme->featured_image">

    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $programme->title,
        'description' => $programme->summary ?: \Illuminate\Support\Str::limit(\App\Support\RichText::toPlainText($programme->description), 300),
        'provider' => ['@type' => 'Organization', 'name' => config('moaum.company.name'), 'url' => url('/')],
    ]" />

    <section class="relative bg-moaum-charcoal py-20 lg:py-24">
        <div class="absolute inset-0">
            @if ($programme->featured_image)
                <img src="{{ $programme->featured_image }}" alt="" class="w-full h-full object-cover opacity-30">
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-moaum-charcoal via-moaum-charcoal/80 to-transparent"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap text-sm text-slate-400 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('training.index') }}" class="hover:text-white">Training</a>
                <span class="mx-2">/</span>
                <span class="text-white" aria-current="page">{{ $programme->title }}</span>
            </nav>
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="inline-block bg-moaum-blue text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">{{ $programme->status->label() }}</span>
                <span class="inline-block bg-white/10 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">{{ $programme->delivery_mode->label() }}</span>
            </div>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-white mb-4 max-w-3xl">{{ $programme->title }}</h1>
            @if ($programme->summary)
                <p class="text-xl text-slate-300 max-w-2xl">{{ $programme->summary }}</p>
            @endif
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid lg:grid-cols-3 gap-12">
            <div class="lg:col-span-2 space-y-12">
                @if ($programme->description)
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">About this programme</h2>
                        <x-rich-content :html="$programme->description" />
                    </div>
                @endif
                @if ($programme->curriculum)
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">Curriculum</h2>
                        <x-rich-content :html="$programme->curriculum" />
                    </div>
                @endif
                @if ($programme->requirements)
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">Entry requirements</h2>
                        <x-rich-content :html="$programme->requirements" />
                    </div>
                @endif
                @if ($programme->certificate_info)
                    <div class="flex gap-4 p-6 rounded-xl bg-moaum-green/5 border border-moaum-green/20">
                        <x-icon name="check-circle" class="w-6 h-6 text-moaum-green shrink-0" />
                        <div>
                            <h2 class="font-semibold text-moaum-charcoal mb-1">Certificate</h2>
                            <p class="text-slate-600 whitespace-pre-line">{{ $programme->certificate_info }}</p>
                        </div>
                    </div>
                @endif

                {{-- Register interest --}}
                <div id="register" class="scroll-mt-24">
                    <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-2">Register your interest</h2>
                    @if (session('success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-800" role="status">{{ session('success') }}</div>
                    @elseif (! $programme->acceptsInterest())
                        <p class="text-slate-600 bg-slate-50 border border-dashed border-slate-300 rounded-lg p-5">
                            Registration for this programme is closed. <a href="{{ route('contact.index') }}" class="text-moaum-blue font-semibold hover:underline">Contact us</a> about future dates.
                        </p>
                    @else
                        <p class="text-slate-600 mb-6">Tell us who would like to attend and our training team will confirm availability and next steps.</p>
                        @if (session('error'))
                            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 mb-4" role="alert">{{ session('error') }}</div>
                        @endif
                        <form method="POST" action="{{ route('training.interest', $programme) }}" class="card space-y-5">
                            @csrf
                            {{-- Honeypot: hidden from people, tempting to bots. --}}
                            <div class="hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                            <div class="grid sm:grid-cols-2 gap-5">
                                <x-requests.form-field name="name" label="Full name" :required="true" />
                                <x-requests.form-field name="organization" label="Organisation" />
                                <x-requests.form-field name="email" label="Email" type="email" :required="true" />
                                <x-requests.form-field name="phone" label="Phone" type="tel" />
                                <x-requests.form-field name="participants" label="Number of participants" type="number" :required="true" extra-value="1" />
                            </div>
                            <x-requests.form-field name="message" label="Anything we should know?" type="textarea" />
                            <label class="flex items-start gap-3 text-sm text-slate-600">
                                <input type="checkbox" name="consent" value="1" @checked(old('consent')) required class="mt-1 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                                <span>I agree to be contacted by MOAUM about this programme.</span>
                            </label>
                            @error('consent') <p class="form-error">{{ $message }}</p> @enderror
                            <button type="submit" class="btn-primary">Register interest</button>
                        </form>
                    @endif
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                        <h2 class="font-display font-bold text-moaum-charcoal mb-4">Programme details</h2>
                        <dl class="space-y-4 text-sm">
                            <div><dt class="text-slate-500">Dates</dt><dd class="font-medium text-moaum-charcoal">{{ $programme->dateRange() ?? 'To be confirmed' }}</dd></div>
                            @if ($programme->duration)
                                <div><dt class="text-slate-500">Duration</dt><dd class="font-medium text-moaum-charcoal">{{ $programme->duration }}</dd></div>
                            @endif
                            <div><dt class="text-slate-500">Delivery</dt><dd class="font-medium text-moaum-charcoal">{{ $programme->delivery_mode->label() }}{{ $programme->venue ? ' · '.$programme->venue : '' }}</dd></div>
                            <div><dt class="text-slate-500">Fee</dt><dd class="font-medium text-moaum-charcoal">{{ $programme->feeLabel() }}</dd></div>
                            @if ($programme->capacity)
                                <div><dt class="text-slate-500">Capacity</dt><dd class="font-medium text-moaum-charcoal">{{ $programme->capacity }} participants</dd></div>
                            @endif
                            @if ($programme->registration_deadline)
                                <div><dt class="text-slate-500">Register by</dt><dd class="font-medium text-moaum-charcoal">{{ $programme->registration_deadline->format('j F Y') }}</dd></div>
                            @endif
                            @if ($programme->trainer)
                                <div><dt class="text-slate-500">Facilitator</dt><dd class="font-medium text-moaum-charcoal">{{ $programme->trainer }}</dd></div>
                            @endif
                            @if ($programme->division)
                                <div><dt class="text-slate-500">Offered by</dt>
                                    <dd><a href="{{ route('businesses.show', $programme->division) }}" class="font-medium text-moaum-blue hover:underline">{{ $programme->division->name }}</a></dd></div>
                            @endif
                        </dl>
                        @if ($programme->acceptsInterest())
                            <a href="#register" class="btn-primary w-full mt-6">Register interest</a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.public>
