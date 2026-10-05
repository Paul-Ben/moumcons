{{-- PRD §14 — project detail: hero, description, scope, gallery, key facts. --}}
<x-layouts.public :title="($project->seo_title ?: $project->title).' | '.config('moaum.company.short_name')"
                  :meta-description="$project->seo_description ?: ($project->summary ?: \Illuminate\Support\Str::limit(\App\Support\RichText::toPlainText($project->description), 155))">

    <section class="relative bg-moaum-charcoal py-20 lg:py-28">
        <div class="absolute inset-0">
            @if ($project->featured_image)
                <img src="{{ $project->featured_image }}" alt="" class="w-full h-full object-cover opacity-30">
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-moaum-charcoal via-moaum-charcoal/80 to-transparent"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap text-sm text-slate-400 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('projects.index') }}" class="hover:text-white">Projects</a>
                <span class="mx-2">/</span>
                <span class="text-white" aria-current="page">{{ $project->title }}</span>
            </nav>
            <span class="inline-block bg-moaum-blue text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-4">{{ $project->status->label() }}</span>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-white mb-4 max-w-3xl">{{ $project->title }}</h1>
            @if ($project->summary)
                <p class="text-xl text-slate-300 max-w-2xl">{{ $project->summary }}</p>
            @endif
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid lg:grid-cols-3 gap-12">
            <div class="lg:col-span-2 space-y-12">
                @if ($project->description)
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">Overview</h2>
                        <x-rich-content :html="$project->description" />
                    </div>
                @endif

                @if ($project->scope)
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-4">Scope of Work</h2>
                        <x-rich-content :html="$project->scope" />
                    </div>
                @endif

                @if ($project->images->isNotEmpty())
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-6">Gallery</h2>
                        <x-lightbox-gallery :images="$project->images" :alt="$project->title" />
                    </div>
                @endif

                @if ($related->isNotEmpty())
                    <div>
                        <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-6">Related Projects</h2>
                        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach ($related as $other)
                                <x-project-card :project="$other" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                        <h2 class="font-display font-bold text-moaum-charcoal mb-4">Project Facts</h2>
                        <dl class="space-y-4 text-sm">
                            @if ($project->client)
                                <div><dt class="text-slate-500">Client</dt><dd class="font-medium text-moaum-charcoal">{{ $project->client }}</dd></div>
                            @endif
                            @if ($project->division)
                                <div><dt class="text-slate-500">Division</dt>
                                    <dd><a href="{{ route('businesses.show', $project->division) }}" class="font-medium text-moaum-blue hover:underline">{{ $project->division->name }}</a></dd></div>
                            @endif
                            @if ($project->location)
                                <div><dt class="text-slate-500">Location</dt><dd class="font-medium text-moaum-charcoal">{{ $project->location }}</dd></div>
                            @endif
                            @if ($project->start_date)
                                <div><dt class="text-slate-500">Started</dt><dd class="font-medium text-moaum-charcoal">{{ $project->start_date->format('F Y') }}</dd></div>
                            @endif
                            @if ($project->completion_date)
                                <div><dt class="text-slate-500">Completed</dt><dd class="font-medium text-moaum-charcoal">{{ $project->completion_date->format('F Y') }}</dd></div>
                            @endif
                            <div><dt class="text-slate-500">Status</dt><dd class="font-medium text-moaum-charcoal">{{ $project->status->label() }}</dd></div>
                        </dl>
                        @if (! empty($project->tags))
                            <div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-slate-200">
                                @foreach ($project->tags as $tag)
                                    <span class="badge bg-white border border-slate-200 text-slate-600">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="bg-moaum-charcoal rounded-xl p-6 text-white">
                        <h2 class="font-display font-bold mb-2">Planning something similar?</h2>
                        <p class="text-sm text-slate-300 mb-5">Tell us about your project and we'll prepare a quotation.</p>
                        <a href="{{ route('requests.quote.create', array_filter(['division' => $project->division?->slug])) }}" class="btn-primary w-full">Request a Quote</a>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.public>
