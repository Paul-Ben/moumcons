{{-- PRD §9/§22 — CMS page (About section, legal and custom pages). --}}
@php
    $content = \App\Support\RichText::withoutPlaceholders($page->content);
    $summary = str_contains((string) $page->summary, 'CLIENT_TO_PROVIDE') ? null : $page->summary;
@endphp

<x-layouts.public :title="($page->seo_title ?: $page->title).' | '.config('moaum.company.short_name')"
                  :meta-description="$page->seo_description ?: ($summary ?: \Illuminate\Support\Str::limit(\App\Support\RichText::toPlainText($content), 155))">

    <section class="relative bg-moaum-charcoal py-16 lg:py-24 overflow-hidden">
        @if ($page->hero_image)
            <img src="{{ $page->hero_image }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-25">
        @endif
        <div class="absolute inset-0 dot-grid-red opacity-10" aria-hidden="true"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap text-sm text-slate-400 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                @if ($aboutNav)
                    <span class="mx-2">/</span>
                    <a href="{{ route('about.profile') }}" class="hover:text-white">About</a>
                @endif
                <span class="mx-2">/</span>
                <span class="text-white" aria-current="page">{{ $page->title }}</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-white mb-4 max-w-3xl">{{ $page->title }}</h1>
            @if ($summary)
                <p class="text-xl text-slate-300 max-w-2xl">{{ $summary }}</p>
            @endif
        </div>
    </section>

    @if ($aboutNav)
        <nav aria-label="About MOAUM" class="border-b border-slate-200 bg-white sticky top-20 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-6 overflow-x-auto">
                @foreach ($aboutNav as $item)
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                       @class(['py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition',
                               'border-moaum-red text-moaum-charcoal' => $item['active'],
                               'border-transparent text-slate-500 hover:text-moaum-charcoal' => ! $item['active']])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>
    @endif

    <section class="max-w-3xl mx-auto px-4 sm:px-6 py-14 lg:py-20">
        @if (filled(trim(strip_tags((string) $content))))
            <x-rich-content :html="$content" class="text-lg" />
        @endif
    </section>

    @if ($page->key === 'leadership')
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            @if ($leaders->isEmpty())
                <p class="text-center text-slate-500 bg-slate-50 border border-dashed border-slate-300 rounded-2xl p-10">
                    Details of our leadership team will be published here shortly.
                </p>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($leaders as $leader)
                        <article class="card text-center" x-data="{ open: false }">
                            <div class="w-32 h-32 mx-auto rounded-full overflow-hidden bg-slate-100 flex items-center justify-center mb-5 ring-4 ring-slate-50">
                                @if ($leader->photo)
                                    <img src="{{ $leader->photo }}" alt="Portrait of {{ $leader->name }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <span class="font-display text-3xl font-bold text-slate-400" aria-hidden="true">{{ $leader->initials() }}</span>
                                @endif
                            </div>
                            <h2 class="font-display text-lg font-bold text-moaum-charcoal">{{ $leader->name }}</h2>
                            <p class="text-sm font-semibold text-moaum-red mb-3">{{ $leader->position }}</p>
                            @if ($leader->bio)
                                <p class="text-sm text-slate-600" :class="open ? '' : 'line-clamp-4'">{{ $leader->bio }}</p>
                                @if (mb_strlen($leader->bio) > 220)
                                    <button type="button" class="mt-2 text-sm font-semibold text-moaum-blue hover:underline" @click="open = !open"
                                            x-text="open ? 'Show less' : 'Read more'" :aria-expanded="open.toString()"></button>
                                @endif
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if ($aboutNav)
        <section class="bg-slate-50 border-t border-slate-200 py-16">
            <x-container class="text-center">
                <h2 class="font-display text-2xl lg:text-3xl font-bold text-moaum-charcoal mb-3">Work with MOAUM</h2>
                <p class="text-slate-600 mb-8 max-w-xl mx-auto">Explore our business divisions or tell us what you need.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('businesses.index') }}" class="btn-outline">Our Businesses</a>
                    <a href="{{ route('requests.service.create') }}" class="btn-primary">Request a Service</a>
                </div>
            </x-container>
        </section>
    @endif
</x-layouts.public>
