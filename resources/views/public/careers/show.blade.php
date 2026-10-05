{{-- PRD §17 — job opening + application form. --}}
<x-layouts.public :title="$job->title.' | Careers | '.config('moaum.company.short_name')"
                  :meta-description="$job->summary ?: \Illuminate\Support\Str::limit(\App\Support\RichText::toPlainText($job->description), 155)">

    {{-- Google for Jobs (PRD §34 structured data). --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => $job->title,
        'description' => (string) ($job->description ?: $job->summary ?: $job->title),
        'datePosted' => ($job->published_at ?? $job->created_at)->toDateString(),
        'validThrough' => $job->application_deadline?->endOfDay()->toIso8601String(),
        'employmentType' => strtoupper($job->employment_type->value),
        'hiringOrganization' => ['@type' => 'Organization', 'name' => config('moaum.company.name'), 'sameAs' => url('/'), 'logo' => asset('images/logo.jpg')],
        'jobLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job->location ?: 'Makurdi', 'addressRegion' => 'Benue', 'addressCountry' => 'NG']],
    ]" />

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-14 lg:py-16">
            <nav aria-label="Breadcrumb" class="flex flex-wrap text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('careers.index') }}" class="hover:text-moaum-red">Careers</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">{{ $job->title }}</span>
            </nav>
            <h1 class="font-display text-3xl lg:text-4xl font-bold text-moaum-charcoal mb-4">{{ $job->title }}</h1>
            <p class="flex flex-wrap gap-x-5 gap-y-1 text-slate-600">
                <span>{{ $job->employment_type->label() }}</span>
                @if ($job->location)<span>{{ $job->location }}</span>@endif
                @if ($job->division)<span>{{ $job->division->name }}</span>@endif
                @if ($job->application_deadline)<span>Apply by {{ $job->application_deadline->format('j F Y') }}</span>@endif
            </p>
        </x-container>
    </section>

    <section class="max-w-3xl mx-auto px-4 sm:px-6 py-12 space-y-10">
        @foreach (['description' => 'About the role', 'responsibilities' => 'Responsibilities', 'qualifications' => 'Qualifications', 'requirements' => 'Requirements'] as $field => $heading)
            @if ($job->{$field})
                <div>
                    <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">{{ $heading }}</h2>
                    <x-rich-content :html="$job->{$field}" />
                </div>
            @endif
        @endforeach

        <div id="apply" class="scroll-mt-24 pt-6 border-t border-slate-200">
            <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-2">Apply for this role</h2>
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-800" role="status">{{ session('success') }}</div>
            @elseif (! $job->acceptsApplications())
                <p class="text-slate-600 bg-slate-50 border border-dashed border-slate-300 rounded-lg p-5">Applications for this position have closed.</p>
            @else
                @if (session('error'))
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 mb-4" role="alert">{{ session('error') }}</div>
                @endif
                <form method="POST" action="{{ route('careers.apply', $job) }}" enctype="multipart/form-data" class="card space-y-5 mt-4">
                    @csrf
                    <div class="hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <x-requests.form-field name="name" label="Full name" :required="true" />
                        <x-requests.form-field name="email" label="Email" type="email" :required="true" />
                        <x-requests.form-field name="phone" label="Phone" type="tel" :required="true" />
                    </div>
                    <div>
                        <label for="cv" class="block text-sm font-medium text-slate-700 mb-1">CV <span class="text-moaum-red">*</span></label>
                        <input id="cv" type="file" name="cv" required accept=".pdf,.doc,.docx"
                               class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-moaum-blue/10 file:px-4 file:py-2 file:font-semibold file:text-moaum-blue">
                        <p class="text-xs text-slate-500 mt-1">PDF or Word, up to 5 MB.</p>
                        @error('cv')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <x-requests.form-field name="qualifications" label="Relevant qualifications" type="textarea" />
                    <x-requests.form-field name="cover_letter" label="Cover letter" type="textarea" />
                    <label class="flex items-start gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="consent" value="1" @checked(old('consent')) required class="mt-1 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                        <span>I agree to MOAUM processing my application and CV for recruitment purposes, as described in the <a href="{{ route('legal.privacy') }}" class="text-moaum-blue hover:underline">privacy policy</a>.</span>
                    </label>
                    @error('consent') <p class="form-error">{{ $message }}</p> @enderror
                    <button type="submit" class="btn-primary">Submit application</button>
                </form>
            @endif
        </div>
    </section>
</x-layouts.public>
