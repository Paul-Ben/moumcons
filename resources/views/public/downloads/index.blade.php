{{-- PRD §18 — document library. --}}
<x-layouts.public title="Downloads | {{ config('moaum.company.short_name') }}"
                  meta-description="Company profile, brochures, service catalogues, forms and other documents from MOAUM Consultancy Services.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">Downloads</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Downloads</h1>
            <p class="text-lg text-slate-600 max-w-2xl">Company documents, brochures, forms and publications.</p>
        </x-container>
    </section>

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if ($categories->count() > 1)
            <nav aria-label="Document categories" class="flex flex-wrap gap-2 mb-10">
                <a href="{{ route('downloads.index') }}" @class(['px-4 py-2 rounded-full text-sm font-medium border transition',
                    'bg-moaum-charcoal text-white border-moaum-charcoal' => ! $activeCategory,
                    'bg-white text-slate-600 border-slate-200 hover:border-slate-400' => $activeCategory])>All</a>
                @foreach ($categories as $value => $label)
                    <a href="{{ route('downloads.index', ['category' => $value]) }}" @class(['px-4 py-2 rounded-full text-sm font-medium border transition',
                        'bg-moaum-charcoal text-white border-moaum-charcoal' => $activeCategory === $value,
                        'bg-white text-slate-600 border-slate-200 hover:border-slate-400' => $activeCategory !== $value])>{{ $label }}</a>
                @endforeach
            </nav>
        @endif

        @forelse ($grouped as $category => $documents)
            <div class="mb-12">
                <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-5">{{ $category }}</h2>
                <ul class="space-y-3">
                    @foreach ($documents as $document)
                        <li class="flex flex-wrap sm:flex-nowrap items-center gap-4 p-5 bg-white border border-slate-200 rounded-xl hover:border-moaum-blue transition">
                            <span class="w-12 h-12 rounded-lg bg-moaum-red/10 text-moaum-red flex items-center justify-center text-xs font-bold shrink-0" aria-hidden="true">{{ $document->fileType() }}</span>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-moaum-charcoal">{{ $document->title }}</h3>
                                @if ($document->description)
                                    <p class="text-sm text-slate-600 mt-0.5">{{ $document->description }}</p>
                                @endif
                                <p class="text-xs text-slate-400 mt-1">{{ $document->fileType() }} · {{ $document->humanSize() }}</p>
                            </div>
                            @if ($document->canBeDownloadedBy(auth()->user()))
                                <a href="{{ route('downloads.show', $document) }}" class="btn-secondary text-sm py-2 px-4 shrink-0">
                                    <x-icon name="download" class="w-4 h-4" /> Download<span class="sr-only"> {{ $document->title }}</span>
                                </a>
                            @else
                                <a href="{{ route('downloads.show', $document) }}" class="btn-ghost text-sm border border-slate-200 shrink-0">Sign in to download</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <x-icon name="download" class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-600">No documents are available yet.</p>
            </div>
        @endforelse
    </section>
</x-layouts.public>
