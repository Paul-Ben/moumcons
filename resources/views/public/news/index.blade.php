{{-- PRD §16 — News & updates. --}}
<x-layouts.public title="News & Updates | {{ config('moaum.company.short_name') }}"
                  meta-description="News, announcements, events and publications from MOAUM Consultancy Services Limited.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">News &amp; Updates</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">News &amp; Updates</h1>
            <p class="text-lg text-slate-600 max-w-2xl">Announcements, events and publications from across MOAUM.</p>
        </x-container>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if ($categories->isNotEmpty())
            <nav aria-label="News categories" class="flex flex-wrap gap-2 mb-10">
                <a href="{{ route('news.index') }}" @class(['px-4 py-2 rounded-full text-sm font-medium border transition',
                    'bg-moaum-charcoal text-white border-moaum-charcoal' => ! $activeCategory,
                    'bg-white text-slate-600 border-slate-200 hover:border-slate-400' => $activeCategory])
                   @if (! $activeCategory) aria-current="page" @endif>All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('news.index', ['category' => $category->slug]) }}" @class(['px-4 py-2 rounded-full text-sm font-medium border transition',
                        'bg-moaum-charcoal text-white border-moaum-charcoal' => $activeCategory?->is($category),
                        'bg-white text-slate-600 border-slate-200 hover:border-slate-400' => ! $activeCategory?->is($category)])
                       @if ($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                @endforeach
            </nav>
        @endif

        @if ($activeTag)
            <p class="mb-8 text-sm text-slate-600">
                Tagged <span class="badge bg-slate-100 text-slate-700">{{ $activeTag }}</span>
                <a href="{{ route('news.index') }}" class="ml-2 text-moaum-blue hover:underline">Clear</a>
            </p>
        @endif

        @if ($lead)
            <article class="grid lg:grid-cols-2 gap-8 items-center mb-14 bg-slate-50 rounded-2xl overflow-hidden border border-slate-200">
                <a href="{{ route('news.show', $lead) }}" class="block aspect-[16/10] lg:aspect-auto lg:h-full bg-slate-200" tabindex="-1" aria-hidden="true">
                    @if ($lead->featured_image)
                        <img src="{{ $lead->featured_image }}" alt="" class="w-full h-full object-cover">
                    @endif
                </a>
                <div class="p-8 lg:pr-12">
                    <p class="eyebrow mb-3">Featured{{ $lead->category ? ' · '.$lead->category->name : '' }}</p>
                    <h2 class="font-display text-3xl font-bold text-moaum-charcoal mb-4">
                        <a href="{{ route('news.show', $lead) }}" class="hover:text-moaum-blue transition">{{ $lead->title }}</a>
                    </h2>
                    <p class="text-slate-600 mb-6">{{ $lead->summary(260) }}</p>
                    <p class="text-sm text-slate-500"><time datetime="{{ $lead->published_at->toDateString() }}">{{ $lead->published_at->format('j F Y') }}</time></p>
                </div>
            </article>
        @endif

        @if ($articles->isEmpty() && ! $lead)
            <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <x-icon name="newspaper" class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-600">No news has been published here yet.</p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($articles as $article)
                    <x-news-card :article="$article" />
                @endforeach
            </div>
            @if ($articles->hasPages())
                <div class="mt-12">{{ $articles->links() }}</div>
            @endif
        @endif
    </section>
</x-layouts.public>
