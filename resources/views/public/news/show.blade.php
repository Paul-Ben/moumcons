{{-- PRD §16 — news article. --}}
<x-layouts.public :title="($article->seo_title ?: $article->title).' | '.config('moaum.company.short_name')"
                  :meta-description="$article->seo_description ?: $article->summary(155)">

    <article>
        <header class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-14 lg:py-20">
                <nav aria-label="Breadcrumb" class="flex flex-wrap text-sm text-slate-500 mb-6">
                    <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('news.index') }}" class="hover:text-moaum-red">News</a>
                    @if ($article->category)
                        <span class="mx-2">/</span>
                        <a href="{{ route('news.index', ['category' => $article->category->slug]) }}" class="hover:text-moaum-red">{{ $article->category->name }}</a>
                    @endif
                </nav>
                <h1 class="font-display text-3xl lg:text-5xl font-bold text-moaum-charcoal leading-tight mb-6">{{ $article->title }}</h1>
                <p class="text-sm text-slate-500 flex flex-wrap gap-x-3 gap-y-1">
                    <time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->format('j F Y') }}</time>
                    @if ($article->author)
                        <span aria-hidden="true">&middot;</span><span>By {{ $article->author->name }}</span>
                    @endif
                    <span aria-hidden="true">&middot;</span><span>{{ $article->readingMinutes() }} min read</span>
                </p>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
            @if ($article->featured_image)
                <img src="{{ $article->featured_image }}" alt="" class="w-full rounded-2xl mb-10 shadow-card">
            @endif

            <x-rich-content :html="$article->content" class="text-lg" />

            @if (! empty($article->tags))
                <div class="flex flex-wrap gap-2 mt-10 pt-8 border-t border-slate-200">
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('news.index', ['tag' => $tag]) }}" class="badge bg-slate-100 text-slate-700 hover:bg-slate-200">{{ $tag }}</a>
                    @endforeach
                </div>
            @endif

            <div class="mt-10">
                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-moaum-blue hover:underline">
                    <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> Back to all news
                </a>
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="bg-slate-50 border-t border-slate-200 py-16">
            <x-container>
                <h2 class="font-display text-2xl font-bold text-moaum-charcoal mb-8">More News</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($related as $other)
                        <x-news-card :article="$other" />
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif
</x-layouts.public>
