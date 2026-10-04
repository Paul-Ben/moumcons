{{-- PRD §20/§21 — Contact page and enquiry form (Flow C). --}}
<x-layouts.public title="Contact Us | {{ config('moaum.company.short_name') }}"
                  meta-description="Send an enquiry to MOAUM Consultancy Services. Tell us what you need and our team will respond.">

<section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
    <x-container class="py-14">
        <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4 gap-1">
            <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a><span>/</span>
            <span class="text-moaum-charcoal font-medium">Contact Us</span>
        </nav>
        <h1 class="font-display text-4xl font-bold text-moaum-charcoal">Get in Touch</h1>
        <p class="text-slate-600 mt-2 max-w-2xl">
            No account needed. Send us a message and a member of the team will get back to you —
            every enquiry is logged with a reference so you can quote it in any follow-up.
        </p>
    </x-container>
</section>

<section class="px-4 sm:px-6 lg:px-8 py-12">
    <div class="mx-auto max-w-6xl grid lg:grid-cols-3 gap-8 items-start">

        {{-- Details panel --}}
        <aside class="lg:col-span-1 space-y-4 lg:sticky lg:top-8">
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold text-moaum-charcoal mb-4">Contact details</h2>
                <ul class="space-y-4 text-sm">
                    @if ($details['address'])
                        <li class="flex items-start">
                            <x-icon name="map-pin" class="w-5 h-5 mr-3 mt-0.5 text-moaum-red shrink-0" />
                            <span class="text-slate-600">{{ $details['address'] }}</span>
                        </li>
                    @endif
                    @if ($details['phone'])
                        <li class="flex items-center">
                            <x-icon name="phone" class="w-5 h-5 mr-3 text-moaum-red shrink-0" />
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $details['phone']) }}"
                               class="text-slate-600 hover:text-moaum-blue">{{ $details['phone'] }}</a>
                        </li>
                    @endif
                    @if ($details['email'])
                        <li class="flex items-center">
                            <x-icon name="mail" class="w-5 h-5 mr-3 text-moaum-red shrink-0" />
                            <a href="mailto:{{ $details['email'] }}"
                               class="text-slate-600 hover:text-moaum-blue break-all">{{ $details['email'] }}</a>
                        </li>
                    @endif
                    @if ($details['hours'])
                        <li class="flex items-center">
                            <x-icon name="clock" class="w-5 h-5 mr-3 text-moaum-red shrink-0" />
                            <span class="text-slate-600">{{ $details['hours'] }}</span>
                        </li>
                    @endif
                </ul>

                @if (! $details['address'] && ! $details['phone'] && ! $details['email'])
                    <p class="text-sm text-slate-500">
                        Our office address and phone line are being confirmed and will appear here shortly.
                        Email us in the meantime.
                    </p>
                @endif

                @if ($details['social'])
                    <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-200">
                        @foreach ($details['social'] as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 hover:text-moaum-blue transition"
                               aria-label="{{ ucfirst($network) }}">
                                <x-icon :name="$network" class="w-4 h-4" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Alternative flows: not every need is an enquiry. --}}
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold text-moaum-charcoal mb-2">Looking for something specific?</h2>
                <p class="text-sm text-slate-600 mb-4">These forms route straight to the right team.</p>
                <div class="space-y-2">
                    <x-button href="{{ route('requests.service.create') }}" variant="outline" class="w-full justify-start">
                        Request a Service
                    </x-button>
                    <x-button href="{{ route('requests.quote.create') }}" variant="outline" class="w-full justify-start">
                        Request a Quote
                    </x-button>
                    <x-button href="{{ route('requests.track') }}" variant="outline" class="w-full justify-start">
                        Track an Existing Request
                    </x-button>
                </div>
            </div>
        </aside>

        {{-- Enquiry form --}}
        <div class="lg:col-span-2">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" enctype="multipart/form-data" class="card p-8 space-y-6">
                @csrf
                {{-- honeypot --}}
                <div class="hidden" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject <span class="text-moaum-red">*</span></label>
                    <input id="subject" name="subject" type="text" required maxlength="190" value="{{ old('subject') }}"
                           placeholder="What is your enquiry about?"
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                    @error('subject')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <x-requests.form-field name="message" label="Message" type="textarea" :required="true"
                                       placeholder="Tell us what you need, and any deadlines or constraints…"
                                       help="At least 20 characters." />

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="business_division_id" class="block text-sm font-medium text-slate-700 mb-1">Business Division (optional)</label>
                        <select id="business_division_id" name="business_division_id"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                            <option value="">General — not division specific</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" @selected(old('business_division_id') == $division->id)>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('business_division_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="service_id" class="block text-sm font-medium text-slate-700 mb-1">Specific Service (optional)</label>
                        <select id="service_id" name="service_id"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                            <option value="">Not sure yet</option>
                            @foreach ($servicesByDivision as $divisionName => $services)
                                <optgroup label="{{ $divisionName }}">
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('service_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="attachment" class="block text-sm font-medium text-slate-700 mb-1">Attachment (optional)</label>
                    <input id="attachment" type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip"
                           class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-100 file:text-sm file:font-medium hover:file:bg-slate-200">
                    <p class="text-xs text-slate-500 mt-1">PDF, Word, Excel, images or zip — up to 10 MB.</p>
                    @error('attachment')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <h2 class="font-semibold text-lg text-moaum-charcoal border-b border-slate-200 pb-2 pt-2">Your details</h2>
                <div class="grid sm:grid-cols-2 gap-5">
                    <x-requests.form-field name="name" label="Full Name" :required="true" />
                    <x-requests.form-field name="organization" label="Organisation" />
                    <x-requests.form-field name="email" label="Email Address" type="email" :required="true" />
                    <x-requests.form-field name="phone" label="Phone Number" type="tel" />
                </div>

                <label class="flex items-start gap-3 text-sm text-slate-700">
                    <input type="checkbox" name="consent" value="1" @checked(old('consent')) class="mt-1 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                    <span>I consent to being contacted by MOAUM about this enquiry. <span class="text-moaum-red">*</span></span>
                </label>
                @error('consent')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full justify-center">Send Enquiry</button>
                </div>
            </form>
        </div>
    </div>
</section>
</x-layouts.public>