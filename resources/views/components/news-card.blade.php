@props(['article'])

{{-- News card (PRD §16) for /news, the home page and related articles. --}}
<article {{ $attributes->merge(['class' => 'group bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-card hover:shadow-lg transition flex flex-col']) }}>
    <a href="{{ route('news.show', $article) }}" class="block aspect-[16/9] bg-slate-100 overflow-hidden" tabindex="-1" aria-hidden="true">
        @if ($article->featured_image)
            <img src="{{ $article->featured_image }}" alt="" loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500 motion-reduce:transform-none">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                <x-icon name="newspaper" class="w-10 h-10 text-slate-300" />
            </div>
        @endif
    </a>
    <div class="p-6 flex-1 flex flex-col">
        <p class="text-xs text-slate-500 mb-2 flex flex-wrap gap-x-2">
            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j M Y') }}</time>
            @if ($article->category)
                <span aria-hidden="true">&middot;</span>
                <span class="text-moaum-red font-semibold">{{ $article->category->name }}</span>
            @endif
        </p>
        <h3 class="font-display text-lg font-bold text-moaum-charcoal mb-2">
            <a href="{{ route('news.show', $article) }}" class="hover:text-moaum-blue transition">{{ $article->title }}</a>
        </h3>
        <p class="text-sm text-slate-600 line-clamp-3">{{ $article->summary() }}</p>
    </div>
</article>
