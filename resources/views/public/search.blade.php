{{-- PRD §37 — site search. --}}
<x-layouts.public :title="($term ? 'Search: '.$term : 'Search').' | '.config('moaum.company.short_name')"
                  meta-description="Search MOAUM divisions, services, projects, news, training and more." :noindex="filled($term)">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-14 lg:py-16">
            <h1 class="font-display text-3xl lg:text-4xl font-bold text-moaum-charcoal mb-6">Search</h1>
            <form method="GET" action="{{ route('search') }}" role="search" class="flex gap-3 max-w-2xl">
                <label for="search-q" class="sr-only">Search the site</label>
                <div class="relative flex-1">
                    <x-icon name="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" />
                    <input id="search-q" type="search" name="q" value="{{ $term }}" placeholder="Services, divisions, projects, news…" autofocus maxlength="100"
                           class="w-full pl-12 pr-4 py-3 rounded-lg border border-slate-300 focus:border-moaum-blue focus:ring-2 focus:ring-moaum-blue/20 outline-none">
                </div>
                <button type="submit" class="btn-primary">Search</button>
            </form>
        </x-container>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if (mb_strlen($term) < 2)
            <p class="text-slate-600">Type at least two characters to search across the whole site.</p>
        @elseif ($total === 0)
            <div class="text-center py-16 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <p class="text-slate-700 font-semibold mb-2">No results for “{{ $term }}”.</p>
                <p class="text-slate-600">Try a different word, browse <a href="{{ route('services.index') }}" class="text-moaum-blue hover:underline">our services</a>, or <a href="{{ route('contact.index') }}" class="text-moaum-blue hover:underline">ask us directly</a>.</p>
            </div>
        @else
            <p class="text-sm text-slate-500 mb-8" role="status">{{ $total }} {{ Str::plural('result', $total) }} for “{{ $term }}”</p>
            @foreach ($groups as $group)
                <div class="mb-10">
                    <h2 class="font-display text-xl font-bold text-moaum-charcoal mb-4">{{ $group['label'] }}</h2>
                    <ul class="space-y-3">
                        @foreach ($group['results'] as $result)
                            <li>
                                <a href="{{ $result['url'] }}" class="block p-5 bg-white border border-slate-200 rounded-xl hover:border-moaum-blue hover:shadow-sm transition">
                                    <span class="flex flex-wrap items-baseline justify-between gap-2">
                                        <span class="font-semibold text-moaum-charcoal">{{ $result['title'] }}</span>
                                        @if ($result['meta'])
                                            <span class="text-xs text-slate-400">{{ $result['meta'] }}</span>
                                        @endif
                                    </span>
                                    @if ($result['excerpt'])
                                        <span class="block text-sm text-slate-600 mt-1">{{ $result['excerpt'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @endif
    </section>
</x-layouts.public>
