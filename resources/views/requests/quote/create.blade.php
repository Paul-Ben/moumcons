{{-- PRD §13 — Request-a-Quote (Flow B). --}}
<x-layouts.public title="Request a Quote | {{ config('moaum.company.short_name') }}"
                  meta-description="Request a quote for your project. Submit scope, quantity and timeline and we'll prepare a detailed quote.">

<section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
    <x-container class="py-14">
        <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4 gap-1">
            <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a><span>/</span>
            <span class="text-moaum-charcoal font-medium">Request a Quote</span>
        </nav>
        <h1 class="font-display text-4xl font-bold text-moaum-charcoal">Request a Quote</h1>
        <p class="text-slate-600 mt-2 max-w-2xl">Share your project scope and we'll prepare a detailed quotation. You'll receive a reference number to track it.</p>
    </x-container>
</section>

<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <form method="POST" action="{{ route('requests.quote.store') }}" enctype="multipart/form-data" class="card p-8 space-y-6">
        @csrf
        <div class="hidden" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

        <h2 class="font-semibold text-lg text-moaum-charcoal border-b border-slate-200 pb-2">Project details</h2>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="business_division_id" class="block text-sm font-medium text-slate-700 mb-1">Business Division <span class="text-moaum-red">*</span></label>
                <select id="business_division_id" name="business_division_id" required class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                    <option value="">Select a division…</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}"
                            @selected(old('business_division_id') == $division->id || ($preselectedService?->business_division_id === $division->id) || ($prefillDivision === $division->slug))>
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
                @error('business_division_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="service_id" class="block text-sm font-medium text-slate-700 mb-1">Service (optional)</label>
                <select id="service_id" name="service_id" class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                    <option value="">General project</option>
                    @if ($preselectedService)
                        <option value="{{ $preselectedService->id }}" selected>{{ $preselectedService->name }} (pre-selected)</option>
                    @endif
                </select>
            </div>
        </div>
        <x-requests.form-field name="project_title" label="Project Title" :required="true" placeholder="e.g. 5,000 corporate notebooks with logo" />
        <div class="grid sm:grid-cols-2 gap-5">
            <x-requests.form-field name="estimated_quantity" label="Estimated Quantity" placeholder="e.g. 500 units" />
            <x-requests.form-field name="location" label="Delivery Location" placeholder="City, State" />
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <x-requests.form-field name="desired_start_date" label="Desired Start Date" type="date" />
            <x-requests.form-field name="desired_completion_date" label="Desired Completion Date" type="date" />
        </div>
        <x-requests.form-field name="requirements" label="Scope / Specifications" type="textarea" :required="true"
                               placeholder="Materials, dimensions, finishes, deliverables…" help="At least 20 characters." />
        <x-requests.form-field name="budget_range" label="Budget Range (optional)" placeholder="e.g. ₦2M – ₦5M" />
        <div>
            <label for="attachment" class="block text-sm font-medium text-slate-700 mb-1">Attachment (optional)</label>
            <input id="attachment" type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip"
                   class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-100 file:text-sm file:font-medium hover:file:bg-slate-200">
            <p class="text-xs text-slate-500 mt-1">Drawings, briefs or references — PDF/Word/Excel/images/zip, up to 10 MB.</p>
            @error('attachment')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <h2 class="font-semibold text-lg text-moaum-charcoal border-b border-slate-200 pb-2 pt-2">Your details</h2>
        <div class="grid sm:grid-cols-2 gap-5">
            <x-requests.form-field name="name" label="Full Name" :required="true" />
            <x-requests.form-field name="organization" label="Organisation" />
            <x-requests.form-field name="email" label="Email Address" type="email" :required="true" />
            <x-requests.form-field name="phone" label="Phone Number" type="tel" :required="true" />
        </div>

        <label class="flex items-start gap-3 text-sm text-slate-700">
            <input type="checkbox" name="consent" value="1" @checked(old('consent')) class="mt-1 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
            <span>I consent to being contacted by MOAUM regarding this quote request. <span class="text-moaum-red">*</span></span>
        </label>
        @error('consent')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

        <div class="pt-2">
            <button type="submit" class="btn-primary w-full justify-center">Submit Quote Request</button>
        </div>
    </form>
</section>
</x-layouts.public>
